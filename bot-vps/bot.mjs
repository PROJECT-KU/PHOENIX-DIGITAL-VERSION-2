/**
 * Bot cek plagiasi Phoenix — versi VPS.
 *
 * Pemindahan dari skrip Tampermonkey (public/bot/phoenix-turnitin.user.js) ke
 * Playwright, supaya pekerjaannya tidak lagi bergantung pada browser admin yang
 * harus terus menyala. Alur, selektor, dan SELURUH pengamannya sengaja dibuat
 * sama; yang berubah hanya siapa yang menjalankan browsernya.
 *
 * Alur satu tugas:
 *   1. tanya antrean ke Phoenix,
 *   2. unduh berkas customer dari Phoenix (nama berkas sudah diganti penanda
 *      PD-xxxxxxxx-INV-…; nama asli customer tidak pernah sampai ke submitin),
 *   3. isi form submitin: Turnitin V1 + filter pilihan customer,
 *   4. PERIKSA metode bayar = paket Standard. Bukan paket → BERHENTI.
 *      Bot tidak pernah memilih QRIS maupun Saldo.
 *   5. klik "Gunakan Paket", catat kode SC-… ke Phoenix,
 *   6. tunggu di halaman status sampai "Unduh Plagiasi" muncul,
 *   7. cocokkan penanda, unduh PDF, kirim ke Phoenix.
 *
 * Semua kegagalan dilaporkan ke Phoenix (/gagal) supaya muncul di kartu dasbor
 * admin — persis seperti versi lama.
 */

import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import { execFileSync } from 'node:child_process';

const AKAR = path.dirname(new URL(import.meta.url).pathname);

/* ================================================================
 | Setelan
 * ================================================================ */

function bacaEnv(berkas) {
    const isi = fs.readFileSync(berkas, 'utf8');
    const hasil = {};

    for (const baris of isi.split('\n')) {
        const bersih = baris.trim();
        if (!bersih || bersih.startsWith('#')) continue;
        const pisah = bersih.indexOf('=');
        if (pisah < 1) continue;
        hasil[bersih.slice(0, pisah).trim()] = bersih.slice(pisah + 1).trim();
    }

    return hasil;
}

const env = bacaEnv(path.join(AKAR, '.env'));

const CFG = {
    phoenix: String(env.PHOENIX_URL || '').replace(/\/+$/, ''),
    token: env.PHOENIX_TOKEN || '',
    email: env.SUBMITIN_EMAIL || '',
    sandi: env.SUBMITIN_PASSWORD || '',
    // 'aman' = berhenti tepat sebelum menekan "Gunakan Paket" lalu menyimpan
    // tangkapan layar. Dipakai untuk uji pertama supaya kuota tidak terpakai.
    mode: (env.MODE || 'aman').toLowerCase(),
    jeda: Math.max(15, parseInt(env.JEDA_DETIK || '60', 10)) * 1000,
    // Batas submitin 50 MB per berkas; diberi jarak aman sedikit.
    batasMb: Math.max(1, parseInt(env.BATAS_MB || '48', 10)),
    profil: path.join(AKAR, 'profil'),
    potret: path.join(AKAR, 'potret'),
    sekali: process.argv.includes('--sekali'),
};

const BATAS_HASIL_MS = 60 * 60 * 1000;   // laporan harus keluar dalam 60 menit
const JEDA_DETAK_MS = 3 * 60 * 1000;     // kabari Phoenix selama menunggu
const JEDA_MUAT_ULANG_MS = 30 * 1000;    // muat ulang halaman status

/** Keadaan berjalan yang dilaporkan ke Phoenix tiap detak. */
const KEADAAN = {
    kuota: null,
    kuotaTeks: null,
    pekerjaan: '',
    selesaiHariIni: 0,
    gagalHariIni: 0,
    galatTerakhir: '',
    hari: new Date().toDateString(),
    dijedaLokal: false,
};

const FORM_URL = 'https://submitin.id/services/plagiarism';
const LOGIN_URL = 'https://submitin.id/user/login';

/* ================================================================
 | Catatan
 * ================================================================ */

function catat(...pesan) {
    const waktu = new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
    console.log(`[${waktu}]`, ...pesan);
}

const tidur = (ms) => new Promise((r) => setTimeout(r, ms));

/* ================================================================
 | API Phoenix
 * ================================================================ */

async function api(metode, jalur, isi, jenis = 'json') {
    const r = await fetch(CFG.phoenix + '/api/bot-turnitin' + jalur, {
        method: metode,
        headers: {
            Authorization: 'Bearer ' + CFG.token,
            Accept: jenis === 'blob' ? '*/*' : 'application/json',
        },
        body: isi,
        signal: AbortSignal.timeout(5 * 60 * 1000),
    });

    if (jenis === 'blob') {
        if (!r.ok) throw Object.assign(new Error('HTTP ' + r.status + ' saat mengunduh berkas'), { status: r.status });

        return Buffer.from(await r.arrayBuffer());
    }

    let badan = null;
    try {
        badan = await r.json();
    } catch {
        badan = null;
    }

    if (!r.ok) {
        throw Object.assign(new Error((badan && (badan.pesan || badan.message)) || 'HTTP ' + r.status), { status: r.status });
    }

    return badan || {};
}

function formulir(objek) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(objek)) fd.append(k, v);

    return fd;
}

async function laporGagal(tugasId, pesan, kuotaHabis = false) {
    catat('GAGAL:', pesan);

    KEADAAN.gagalHariIni += 1;
    KEADAAN.galatTerakhir = String(pesan);
    KEADAAN.pekerjaan = '';

    try {
        await api('POST', `/tugas/${tugasId}/gagal`, formulir({
            pesan: String(pesan).slice(0, 480),
            kuota_habis: kuotaHabis ? '1' : '0',
        }));
    } catch (e) {
        catat('Laporan gagal tidak terkirim:', e.message);
    }
}

/* ================================================================
 | Laporan ke Phoenix (dibaca agen Telegram)
 * ================================================================ */

function angkaPerintah(keluaran) {
    return parseInt(String(keluaran).replace(/[^\d]/g, ''), 10) || 0;
}

/**
 * Kirim keadaan mesin ke Phoenix, lalu jemput perintah yang menunggu.
 *
 * VPS ini TIDAK membuka port apa pun — Phoenix tidak pernah menghubunginya.
 * Semua pemantauan dan kendali lewat panggilan keluar ini, jadi tidak ada
 * pintu masuk baru yang harus dijaga.
 */
async function lapor() {
    // Hitungan harian dinolkan sendiri saat ganti hari.
    const hariIni = new Date().toDateString();
    if (KEADAAN.hari !== hariIni) {
        KEADAAN.hari = hariIni;
        KEADAAN.selesaiHariIni = 0;
        KEADAAN.gagalHariIni = 0;
    }

    let memori = {};
    let disk = null;
    let beban = null;

    try {
        const bebas = execFileSync('free', ['-m']).toString().split('\n')[1].split(/\s+/);
        memori = { memori_mb: parseInt(bebas[2], 10), memori_total_mb: parseInt(bebas[1], 10) };
        disk = angkaPerintah(execFileSync('df', ['-h', '/']).toString().split('\n')[1].split(/\s+/)[4]);
        beban = parseFloat(fs.readFileSync('/proc/loadavg', 'utf8').split(' ')[0]);
    } catch { /* biarkan kosong; laporan tetap dikirim */ }

    const isi = {
        mode: KEADAAN.dijedaLokal ? 'jeda' : CFG.mode,
        kuota: KEADAAN.kuota,
        kuota_teks: KEADAAN.kuotaTeks,
        pekerjaan: KEADAAN.pekerjaan,
        selesai_hari_ini: KEADAAN.selesaiHariIni,
        gagal_hari_ini: KEADAAN.gagalHariIni,
        galat_terakhir: KEADAAN.galatTerakhir.slice(0, 300),
        hidup_detik: Math.round(process.uptime()),
        ...memori,
        ...(disk === null ? {} : { disk_persen: disk }),
        ...(beban === null ? {} : { beban }),
    };

    const bersih = Object.fromEntries(Object.entries(isi).filter(([, v]) => v !== null && v !== undefined && v !== ''));

    try {
        const r = await api('POST', '/lapor', formulir(bersih));

        if (r.perintah) await jalankanPerintah(r.perintah);
    } catch (e) {
        catat('Laporan ke Phoenix gagal:', e.message);
    }
}

/** Perintah dari agen Telegram — daftar tertutup, tidak ada shell. */
async function jalankanPerintah(perintah) {
    catat('Perintah dari Telegram:', perintah);

    if (perintah === 'jeda') {
        KEADAAN.dijedaLokal = true;
        catat('Bot dijeda: antrean tidak diambil sampai ada perintah lanjut.');
    } else if (perintah === 'lanjut') {
        KEADAAN.dijedaLokal = false;
        catat('Bot dilanjutkan.');
    } else if (perintah === 'restart') {
        catat('Bot dimulai ulang atas perintah Telegram.');
        // Keluar dengan kode 0; systemd (Restart=always) yang menyalakan lagi.
        await tidur(500);
        process.exit(0);
    }
}

/* ================================================================
 | Mengecilkan berkas kebesaran
 * ================================================================ */

const megabyte = (byte) => (byte / 1048576).toFixed(2) + ' MB';

/** Banyaknya kata pada lapisan teks PDF — dipakai membuktikan isi tidak hilang. */
function kataPdf(berkas) {
    try {
        const teks = execFileSync('pdftotext', ['-q', berkas, '-'], { maxBuffer: 64 * 1024 * 1024 }).toString();

        return (teks.match(/\S+/g) || []).length;
    } catch {
        return null;
    }
}

/**
 * Kecilkan PDF yang melebihi batas submitin.
 *
 * Yang dikecilkan HANYA gambar di dalamnya (diturunkan resolusinya oleh
 * Ghostscript); lapisan teks — satu-satunya yang dibaca Turnitin — disalin apa
 * adanya. Sesudah dikecilkan, jumlah katanya dibandingkan dengan aslinya:
 * kalau berkurang, hasilnya DIBUANG dan berkas asli yang dipakai. Lebih baik
 * gagal terang-terangan daripada mengirim naskah yang isinya sudah berkurang.
 */
function kecilkanBerkas(berkas, namaBerkas) {
    const batas = CFG.batasMb * 1048576;
    const awal = fs.statSync(berkas).size;

    if (awal <= batas) return { berkas, diubah: false };

    if (!/\.pdf$/i.test(namaBerkas)) {
        throw new Error(`Berkas ${megabyte(awal)} melewati batas ${CFG.batasMb} MB submitin dan bukan PDF, `
            + 'jadi tidak bisa dikecilkan otomatis. Kerjakan manual atau minta customer mengirim PDF.');
    }

    const kataAsli = kataPdf(berkas);
    catat(`Berkas ${megabyte(awal)} melebihi batas ${CFG.batasMb} MB — dikecilkan dulu`
        + (kataAsli === null ? '' : ` (teks asli ${kataAsli} kata)`));

    // Dari yang paling menjaga mutu ke yang paling agresif; berhenti begitu muat.
    for (const mutu of ['/printer', '/ebook', '/screen']) {
        const wadah = fs.mkdtempSync(path.join(os.tmpdir(), 'kecil-'));
        const keluar = path.join(wadah, namaBerkas);

        try {
            execFileSync('gs', [
                '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.4', `-dPDFSETTINGS=${mutu}`,
                '-dNOPAUSE', '-dQUIET', '-dBATCH', '-dSAFER',
                // Teks TIDAK diubah jadi gambar: tanpa ini Turnitin bisa kehilangan
                // lapisan teksnya sama sekali.
                '-dNoOutputFonts=false', '-dSubsetFonts=true', '-dEmbedAllFonts=true',
                `-sOutputFile=${keluar}`, berkas,
            ], { timeout: 10 * 60 * 1000, stdio: 'pipe' });
        } catch (e) {
            fs.rmSync(wadah, { recursive: true, force: true });
            catat(`Ghostscript ${mutu} gagal: ${String(e.message).split('\n')[0].slice(0, 80)}`);
            continue;
        }

        const ukuran = fs.statSync(keluar).size;
        const kataBaru = kataPdf(keluar);

        // Isi wajib utuh. Selisih 0,5% ditoleransi untuk beda pemenggalan kata.
        const isiUtuh = kataAsli === null || kataBaru === null
            ? kataBaru !== null && kataBaru > 0
            : kataBaru >= Math.floor(kataAsli * 0.995);

        catat(`  ${mutu}: ${megabyte(ukuran)}${kataBaru === null ? '' : `, teks ${kataBaru} kata`}`
            + (isiUtuh ? '' : ' — ISI BERKURANG, dibuang'));

        if (!isiUtuh) {
            fs.rmSync(wadah, { recursive: true, force: true });
            continue;
        }

        if (ukuran <= batas) {
            catat(`Berhasil dikecilkan: ${megabyte(awal)} → ${megabyte(ukuran)} (mutu ${mutu}), isi utuh.`);

            return { berkas: keluar, diubah: true };
        }

        fs.rmSync(wadah, { recursive: true, force: true });
    }

    throw new Error(`Berkas ${megabyte(awal)} tetap di atas batas ${CFG.batasMb} MB sesudah dikecilkan `
        + 'tanpa mengurangi isinya. Kerjakan manual.');
}

/* ================================================================
 | Browser
 * ================================================================ */

async function bukaBrowser() {
    fs.mkdirSync(CFG.profil, { recursive: true });
    fs.mkdirSync(CFG.potret, { recursive: true });

    return chromium.launchPersistentContext(CFG.profil, {
        headless: true,
        // --no-sandbox: proses berjalan sebagai user biasa di VPS tanpa
        // namespace user; --disable-dev-shm-usage: /dev/shm di VPS kecil.
        args: ['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu'],
        viewport: { width: 1440, height: 900 },
        acceptDownloads: true,
        locale: 'id-ID',
        timezoneId: 'Asia/Jakarta',
    });
}

/**
 * Pastikan sesi submitin hidup; login sekali kalau perlu.
 *
 * Halaman /services/plagiarism BISA DIBUKA TANPA LOGIN, hanya saja tidak
 * memunculkan metode bayar. Jadi "tidak dialihkan ke /user/login" bukan bukti
 * sudah masuk — yang dipakai keberadaan tautan login di halaman itu sendiri.
 */
async function pastikanLogin(hal) {
    const adaTautanLogin = await hal.locator('a[href*="/user/login"]').count().catch(() => 0);

    if (!hal.url().includes('/user/login') && !adaTautanLogin) return;

    if (!hal.url().includes('/user/login')) {
        catat('Belum masuk submitin — membuka halaman login…');
        await hal.goto(LOGIN_URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
    }

    catat('Sesi submitin habis — masuk ulang…');
    await hal.fill('input[name=email]', CFG.email);
    await hal.fill('input[name=password]', CFG.sandi);
    await hal.check('input[type=checkbox][name=remember_me]').catch(() => {});
    await hal.click('button:has-text("Masuk sekarang")');
    await hal.waitForLoadState('networkidle', { timeout: 45000 }).catch(() => {});

    if (hal.url().includes('/user/login')) {
        throw new Error('Login submitin.id ditolak — periksa email/password di .env.');
    }

    catat('Login submitin berhasil.');
}

/**
 * Tutup pop-up promo submitin.
 *
 * Modal promo menutupi form dan bisa menelan klik "Gunakan Paket" — satu-satunya
 * klik yang benar-benar memakai kuota. Kotak "Jangan tampilkan lagi" ikut
 * dicentang supaya tidak muncul lagi di profil browser ini.
 */
async function tutupPromo(hal) {
    const modal = hal.locator('.modal, [class*="promo"], [id*="promo"]')
        .filter({ hasText: /jangan tampilkan lagi|promo/i }).first();

    if (!(await modal.count()) || !(await modal.isVisible().catch(() => false))) return;

    await hal.locator('input[type=checkbox]').filter({ hasNot: hal.locator('#orderForm input') }).first()
        .evaluate((el) => { if (!el.checked) el.click(); }).catch(() => {});

    for (const sel of ['button:has-text("Tutup")', '[aria-label="Close"]', '.modal .btn-close', 'button:has-text("×")']) {
        const tombol = hal.locator(sel).first();
        if (await tombol.count()) {
            await tombol.evaluate((el) => el.click()).catch(() => {});
            await tidur(400);
            break;
        }
    }

    if (await modal.isVisible().catch(() => false)) {
        // Masih menutupi: singkirkan dari DOM supaya tidak menelan klik.
        await modal.evaluate((el) => el.remove()).catch(() => {});
        catat('Pop-up promo submitin disingkirkan dari halaman.');
    } else {
        catat('Pop-up promo submitin ditutup.');
    }
}

async function keForm(hal) {
    await hal.goto(FORM_URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await pastikanLogin(hal);

    if (!hal.url().includes('/services/plagiarism')) {
        await hal.goto(FORM_URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
    }

    await hal.waitForSelector('#orderForm', { timeout: 30000 });

    // Metode bayar baru dirender untuk pengguna yang sudah masuk.
    await hal.waitForSelector('#payMethods', { timeout: 20000 }).catch(() => {});

    await tutupPromo(hal);
}

/* ================================================================
 | Paket Standard
 * ================================================================ */

/**
 * Baca label paket Standard beserta sisa kuotanya.
 *
 * Angka "Total Bayar" SENGAJA tidak dipakai sebagai pengaman: submitin tetap
 * menampilkan harga per dokumen (Rp 5.000) walau dibayar dengan paket.
 */
async function bacaPaket(hal) {
    return hal.evaluate(() => {
        const label = Array.from(document.querySelectorAll('#payMethods .pay-method-label[data-pm="package"]'))
            .find((l) => /standard/i.test(l.textContent));

        if (!label) return { ada: false, pesan: 'Paket Standard tidak ada di akun submitin.id.' };

        const teks = label.textContent.replace(/\s+/g, ' ');
        const sisaM = teks.match(/(\d+)\s*x\s*tersisa/i);
        const tglM = teks.match(/s\/d\s*(\d{1,2})\/(\d{1,2})\/(\d{4})/i);
        const sisa = sisaM ? parseInt(sisaM[1], 10) : null;

        let kedaluwarsa = false;
        if (tglM) {
            kedaluwarsa = new Date(+tglM[3], +tglM[2] - 1, +tglM[1], 23, 59, 59) < new Date();
        }

        const mati = label.classList.contains('pm-disabled');
        const habis = sisa === 0 || kedaluwarsa || (mati && sisa !== null && sisa < 1);

        return {
            ada: true,
            sisa,
            kedaluwarsa,
            mati,
            habis,
            id: label.getAttribute('data-pkg-id') || '',
            pesan: habis
                ? 'Paket Standard ' + (kedaluwarsa ? 'sudah kedaluwarsa' : 'habis')
                    + (tglM ? ` (s/d ${tglM[1]}/${tglM[2]}/${tglM[3]})` : '') + '.'
                : 'Paket Standard: ' + (sisa === null ? '?' : sisa) + 'x tersisa',
        };
    });
}

/** Syarat sebelum mengambil tugas: form muncul, nomor WA terisi, paket bersisa. */
async function periksaSiap(hal) {
    const adaForm = await hal.locator('#orderForm').count();
    const adaBayar = await hal.locator('#payMethods').count();

    if (!adaForm || !adaBayar) {
        return { siap: false, masalah: 'Form submitin tidak lengkap (mungkin sesi login putus).' };
    }

    const wa = await hal.locator('#waInput').inputValue().catch(() => '');
    if (String(wa).replace(/\D/g, '').length < 9) {
        return {
            siap: false,
            masalah: 'Nomor WhatsApp akun submitin.id kosong. Isi di profil submitin '
                + '(bot tidak pernah memakai nomor customer).',
        };
    }

    const paket = await bacaPaket(hal);

    // Disimpan untuk laporan ke Telegram, apa pun hasilnya.
    KEADAAN.kuota = paket.ada ? paket.sisa : 0;
    KEADAAN.kuotaTeks = paket.pesan;

    if (!paket.ada || paket.habis) return { siap: false, masalah: paket.pesan, kuotaHabis: true };

    return { siap: true, paket };
}

/* ================================================================
 | Mengisi form
 * ================================================================ */

async function isiForm(hal, tugas, berkasPath, namaBerkas) {
    await hal.waitForSelector('#orderForm', { timeout: 30000 });

    // Layanan: Cek Plagiasi
    const tab = hal.locator('button.svc-btn[data-svc="plagiarism"]');
    if (await tab.count() && !(await tab.first().evaluate((e) => e.classList.contains('is-active')))) {
        await tab.first().click();
        await tidur(600);
    }

    // Turnitin V1 — biasanya SUDAH terpilih sejak halaman dibuka, jadi kartunya
    // hanya ditekan bila memang belum. Menekan yang sudah aktif pernah membuat
    // klik menggantung sampai batas waktu.
    if ((await hal.locator('#plagiarismEngine').inputValue().catch(() => '')) !== 'v1') {
        await domKlik(hal, '.plat-card[data-engine="v1"], input[name="_plat_ui"][value="v1"]').catch(() => {});
        await tidur(500);
    }

    if ((await hal.locator('#plagiarismEngine').inputValue().catch(() => '')) !== 'v1') {
        throw new Error('Gagal memilih Turnitin V1');
    }

    // Buang berkas sisa percobaan sebelumnya, lalu masukkan berkas tugas.
    const hapus = hal.locator('#fpRemove');
    if (await hapus.count() && await hapus.first().isVisible().catch(() => false)) {
        await hapus.first().evaluate((el) => el.click());
        await tidur(400);
    }

    await hal.setInputFiles('#fileInput', berkasPath);

    const terlihat = await hal.waitForFunction(
        (nama) => document.body.innerText.includes(nama),
        namaBerkas,
        { timeout: 20000 },
    ).catch(() => null);

    if (!terlihat) throw new Error('Berkas tidak terbaca oleh form submitin');

    // Judul = penanda + nomor order, BUKAN nama customer.
    if (await hal.locator('#judulInput').count()) {
        await setNilai(hal, '#judulInput', tugas.judul);
    }

    // Promo dikosongkan, bundel layanan lain dimatikan.
    const promo = hal.locator('#promoInput');
    if (await promo.count() && await promo.inputValue().catch(() => '')) await setNilai(hal, '#promoInput', '');

    if (await hal.locator('#bundleOtherService').count()) {
        await setCentang(hal, '#bundleOtherService', false);
        await tidur(300);
    }

    // Filter sesuai pilihan customer.
    const f = tugas.filter || {};
    await setCentang(hal, 'input[name="excl_biblio"]', !!f.excl_biblio);
    await setCentang(hal, 'input[name="excl_quotes"]', !!f.excl_quotes);

    await setCentang(hal, '#exclSourceChk', !!f.excl_source);
    if (f.excl_source) {
        await tidur(300);
        const kata = f.excl_source.tipe === 'words';
        const radio = kata ? '#sourceTypeWords' : '#sourceTypePercent';
        if (!(await hal.locator(radio).count())) throw new Error('Pilihan satuan Exclude Sources tidak ditemukan');

        await hal.locator(radio).first().evaluate((el) => { if (!el.checked) el.click(); });
        await setNilai(hal, kata ? '#exclSourceWords' : '#exclSourcePercent', f.excl_source.nilai);
    }

    await setCentang(hal, '#exclMatchChk', !!f.excl_match);
    if (f.excl_match) {
        await tidur(300);
        await setNilai(hal, '#exclMatchWords', f.excl_match.kata);
    }

    // Paket Standard — satu-satunya metode bayar yang boleh dipakai bot.
    const paket = await bacaPaket(hal);
    if (!paket.ada || paket.habis) {
        throw Object.assign(new Error(paket.pesan), { kuotaHabis: true });
    }
    if (paket.mati) throw new Error('Paket Standard tidak bisa dipilih untuk pesanan ini');

    await hal.locator('#payMethods .pay-method-label[data-pm="package"]')
        .filter({ hasText: /standard/i }).first().evaluate((el) => el.click());
    await tidur(800);

    const metode = await hal.locator('#payMethodType').inputValue().catch(() => '');
    const idPaket = await hal.locator('#payPackageId').inputValue().catch(() => '0');

    if (metode !== 'package' || idPaket === '0') throw new Error('Gagal memilih paket Standard');

    return paket;
}

/*
 * Kontrol form submitin banyak yang display:none atau berukuran 0x0 (checkbox
 * & radio bergaya kustom). Playwright menolak mengkliknya karena dianggap tidak
 * bisa disentuh pengguna — padahal halamannya memang dirancang begitu. Karena
 * itu penekanan dilakukan di tingkat DOM, sama seperti skrip Tampermonkey.
 */
async function domKlik(hal, sel) {
    const ada = await hal.locator(sel).count();
    if (!ada) throw new Error('Elemen tidak ditemukan di form submitin: ' + sel);

    await hal.locator(sel).first().evaluate((el) => el.click());
}

async function setCentang(hal, sel, mau) {
    const hasil = await hal.locator(sel).first().evaluate((el, mauCentang) => {
        if (el.disabled) return 'nonaktif';
        if (el.checked !== mauCentang) el.click();

        return el.checked === mauCentang ? 'ok' : 'gagal';
    }, !!mau).catch(() => 'hilang');

    if (hasil === 'hilang') throw new Error('Kotak centang tidak ditemukan di form submitin: ' + sel);
    if (hasil === 'nonaktif') throw new Error('Kotak centang ' + sel + ' dinonaktifkan oleh submitin');
    if (hasil !== 'ok') throw new Error('Gagal mengubah centang ' + sel);
}

/** Isi nilai lewat setter asli + event input/change (kolomnya bisa 0x0). */
async function setNilai(hal, sel, nilai) {
    const ok = await hal.locator(sel).first().evaluate((el, v) => {
        el.disabled = false;
        const setter = Object.getOwnPropertyDescriptor(Object.getPrototypeOf(el), 'value');
        setter && setter.set ? setter.set.call(el, String(v)) : (el.value = String(v));
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));

        return el.value === String(v);
    }, nilai).catch(() => false);

    if (!ok) throw new Error('Gagal mengisi ' + sel);
}

/**
 * Pemeriksaan terakhir sebelum menekan tombol.
 *
 * Pengaman uang: HANYA paket. Kalau ada satu saja yang janggal, tidak ada yang
 * dikirim ke submitin — lebih baik tugasnya tertunda daripada salah bayar.
 */
async function periksaSebelumKirim(hal, namaBerkas) {
    const salah = [];

    if ((await hal.locator('#plagiarismEngine').inputValue().catch(() => '')) !== 'v1') salah.push('bukan Turnitin V1');
    if ((await hal.locator('#payMethodType').inputValue().catch(() => '')) !== 'package') salah.push('metode bayar bukan paket');

    const idPaket = await hal.locator('#payPackageId').inputValue().catch(() => '0');
    if (idPaket === '0') salah.push('paket belum terpilih');

    const paket = await bacaPaket(hal);
    const terpilihStandard = await hal.locator('#payMethods .pay-method-label[data-pm="package"]')
        .filter({ hasText: /standard/i }).first()
        .evaluate((el) => el.classList.contains('is-selected')).catch(() => false);

    if (!paket.ada || !terpilihStandard || idPaket !== paket.id) salah.push('yang terpilih bukan paket Standard');

    const tombol = hal.locator('#payBtn');
    const teksTombol = (await tombol.textContent().catch(() => '')) || '';
    if (!/gunakan\s+paket/i.test(teksTombol)) {
        salah.push('tombol bukan "Gunakan Paket" (bot tidak membayar dengan QRIS/Saldo)');
    }

    const adaBerkas = await hal.evaluate((n) => document.body.innerText.includes(n), namaBerkas);
    if (!adaBerkas) salah.push('berkas tugas tidak terlihat di form');

    if (salah.length) {
        throw new Error('Dibatalkan sebelum kirim: ' + salah.join('; ') + '. Tidak ada yang dikirim ke submitin.');
    }

    return tombol;
}

/* ================================================================
 | Halaman status
 * ================================================================ */

function kodeDariUrl(url) {
    try {
        const k = new URL(url).searchParams.get('order');

        return k ? k.trim().toUpperCase() : null;
    } catch {
        return null;
    }
}

/** Ambil PDF laporan lewat sesi browser yang sama (cookie ikut terbawa). */
async function unduhLaporan(hal, ctx) {
    const tombol = hal.locator('a, button').filter({ hasText: /unduh\s+plagiasi/i }).first();
    if (!(await tombol.count())) return null;

    const href = await tombol.getAttribute('href').catch(() => null);
    let url = href && !/^javascript:/i.test(href) && href !== '#' ? new URL(href, hal.url()).href : null;

    if (!url) {
        const data = (await tombol.getAttribute('data-url').catch(() => null))
            || (await tombol.getAttribute('data-href').catch(() => null));
        if (data) url = new URL(data, hal.url()).href;
    }

    if (!url) {
        // Tombol ber-skrip: tangkap unduhannya lewat Playwright.
        const menunggu = hal.waitForEvent('download', { timeout: 60000 }).catch(() => null);
        await tombol.click();
        const unduhan = await menunggu;
        if (!unduhan) return null;

        const tujuan = path.join(os.tmpdir(), 'laporan-' + Date.now() + '.pdf');
        await unduhan.saveAs(tujuan);

        return fs.readFileSync(tujuan);
    }

    const r = await ctx.request.get(url, { timeout: 5 * 60 * 1000 });
    if (!r.ok()) throw new Error('HTTP ' + r.status() + ' saat mengunduh laporan');

    return Buffer.from(await r.body());
}

/* ================================================================
 | Satu tugas, dari awal sampai selesai
 * ================================================================ */

async function kerjakan(ctx, hal, tugas) {
    catat(`Mengerjakan ${tugas.order_number} (${tugas.penanda})`);
    KEADAAN.pekerjaan = tugas.order_number;

    // 1. Unduh berkas customer dari Phoenix.
    let isi;
    try {
        isi = await api('GET', `/tugas/${tugas.id}/berkas`, null, 'blob');
    } catch (e) {
        return laporGagal(tugas.id, 'Tidak bisa mengunduh berkas customer dari Phoenix: ' + e.message);
    }

    const berkasPath = path.join(os.tmpdir(), tugas.nama_berkas);
    fs.writeFileSync(berkasPath, isi);

    let dipakai = berkasPath;
    let berkasKecil = null;

    try {
        // Batas submitin 50 MB. Berkas yang lebih besar dikecilkan dulu —
        // gambarnya saja, teksnya dijaga utuh (lihat kecilkanBerkas).
        const hasil = kecilkanBerkas(berkasPath, tugas.nama_berkas);
        dipakai = hasil.berkas;
        if (hasil.diubah) berkasKecil = hasil.berkas;
    } catch (e) {
        fs.rmSync(berkasPath, { force: true });

        return laporGagal(tugas.id, e.message);
    }

    try {
        // 2. Isi form.
        await keForm(hal);
        const siap = await periksaSiap(hal);
        if (!siap.siap) throw Object.assign(new Error(siap.masalah), { kuotaHabis: !!siap.kuotaHabis });

        await isiForm(hal, tugas, dipakai, tugas.nama_berkas);
        await tidur(900);

        const tombol = await periksaSebelumKirim(hal, tugas.nama_berkas);

        // 3. Mode aman: berhenti di sini. Form terisi lengkap, tapi tidak ada
        //    yang dikirim dan kuota tidak terpakai.
        if (CFG.mode !== 'penuh') {
            // Yang dipotret FORMNYA saja: tangkapan halaman penuh membuat bukti
            // yang penting tenggelam di antara materi promosi submitin.
            const potret = path.join(CFG.potret, `aman-${tugas.order_number}-${Date.now()}.png`);
            const formEl = hal.locator('#orderForm');
            await (await formEl.count()
                ? formEl.screenshot({ path: potret })
                : hal.screenshot({ path: potret, fullPage: true }));
            catat('MODE AMAN — form terisi tapi TIDAK dikirim. Tangkapan layar:', potret);

            return laporGagal(
                tugas.id,
                'Mode aman: form submitin sudah terisi lengkap dan lolos semua pemeriksaan, '
                    + 'tetapi bot sengaja berhenti sebelum menekan "Gunakan Paket". '
                    + 'Ubah MODE=penuh di server untuk menjalankannya sungguhan.',
            );
        }

        // 4. Kirim. Dicatat sebelum klik supaya tidak pernah terkirim dua kali.
        catat('Klik "Gunakan Paket" untuk', tugas.order_number);
        await tutupPromo(hal);

        const pindah = hal.waitForURL(/\/status\?order=/i, { timeout: 10 * 60 * 1000 }).catch(() => null);

        // Klik biasa dulu; kalau ada yang menghalangi, ulangi di tingkat DOM.
        try {
            await tombol.click({ timeout: 15000 });
        } catch (e) {
            catat('Klik biasa terhalang (' + e.message.split('\n')[0].slice(0, 60) + ') — diulang lewat DOM.');
            await tombol.evaluate((el) => el.click());
        }

        await pindah;

        const galat = await hal.locator('#alertError.show').textContent().catch(() => null);
        if (galat && hal.url().includes('/services/plagiarism')) {
            return laporGagal(tugas.id, 'submitin.id menolak: ' + galat.trim());
        }

        const kode = kodeDariUrl(hal.url());
        if (!kode) {
            return laporGagal(
                tugas.id,
                'Setelah "Gunakan Paket", submitin.id membuka ' + hal.url().split('?')[0]
                    + ' (bukan halaman status). Cek riwayat pesanan submitin untuk ' + tugas.penanda + '.',
            );
        }

        // 5. Cocokkan penanda sebelum kode dicatat — jangan sampai hasil tertukar.
        const cocok = await hal.waitForFunction(
            (p) => document.body.innerText.includes(p),
            tugas.penanda,
            { timeout: 30000 },
        ).catch(() => null);

        if (!cocok) {
            return laporGagal(tugas.id, `Halaman status ${kode} tidak memuat penanda ${tugas.penanda}. Hasil tidak diambil agar tidak tertukar.`);
        }

        await api('POST', `/tugas/${tugas.id}/terkirim`, formulir({ kode }));
        catat(`Terkirim: ${tugas.order_number} → ${kode}`);

        // 6. Tunggu laporannya keluar.
        await tungguHasil(ctx, hal, tugas, kode);
    } catch (e) {
        return laporGagal(tugas.id, e.message || String(e), !!e.kuotaHabis);
    } finally {
        fs.rmSync(berkasPath, { force: true });
        if (berkasKecil) fs.rmSync(path.dirname(berkasKecil), { recursive: true, force: true });
    }
}

async function tungguHasil(ctx, hal, tugas, kode) {
    const mulai = Date.now();
    let detak = Date.now();

    for (;;) {
        if (Date.now() - mulai > BATAS_HASIL_MS) {
            return laporGagal(tugas.id, `Laporan ${kode} belum keluar setelah 60 menit.`);
        }

        const teks = await hal.locator('body').innerText().catch(() => '');

        if (!teks.includes(tugas.penanda)) {
            return laporGagal(tugas.id, `Halaman status ${kode} tidak memuat penanda ${tugas.penanda}. Hasil tidak diambil.`);
        }

        if (await hal.locator('.badge-failed, .badge-refund').count()) {
            return laporGagal(tugas.id, `submitin.id menandai pesanan ${kode} gagal/refund.`);
        }

        const siap = await hal.locator('a, button').filter({ hasText: /unduh\s+plagiasi/i }).count();

        if (siap) {
            const isi = await unduhLaporan(hal, ctx);
            if (!isi) throw new Error('Tombol "Unduh Plagiasi" ada, tapi alamat berkasnya tidak dikenali.');
            if (isi.subarray(0, 5).toString() !== '%PDF-') throw new Error('Yang terunduh bukan PDF.');

            const persenTeks = await hal.locator('.sim-value').first().textContent().catch(() => '');
            const persen = parseInt(String(persenTeks).replace(/[^\d]/g, ''), 10);

            const fd = new FormData();
            fd.append('kode', kode);
            if (!Number.isNaN(persen)) fd.append('persen', String(persen));
            fd.append('berkas', new Blob([isi], { type: 'application/pdf' }), `Turnitin-${tugas.order_number}.pdf`);

            const r = await api('POST', `/tugas/${tugas.id}/hasil`, fd);
            catat(`SELESAI ${tugas.order_number} (${Number.isNaN(persen) ? '?' : persen}%) — `
                + (r.status === 'selesai' ? 'terkirim ke customer' : 'perlu dilengkapi admin'));

            KEADAAN.selesaiHariIni += 1;
            KEADAAN.pekerjaan = '';
            if (KEADAAN.kuota !== null && KEADAAN.kuota > 0) KEADAAN.kuota -= 1;

            return;
        }

        if (Date.now() - detak > JEDA_DETAK_MS) {
            api('POST', '/detak', formulir({ tugas: String(tugas.id) })).catch(() => {});
            detak = Date.now();
        }

        await tidur(JEDA_MUAT_ULANG_MS);
        await hal.reload({ waitUntil: 'domcontentloaded', timeout: 60000 }).catch(() => {});
        await pastikanLogin(hal);
    }
}

/* ================================================================
 | Putaran utama
 * ================================================================ */

async function putaran(ctx, hal) {
    let detak = 0;

    for (;;) {
        try {
            // Laporan tiap putaran: itu juga jalur perintah dari Telegram.
            await lapor();

            if (Date.now() - detak > JEDA_DETAK_MS) {
                await api('POST', '/detak', formulir({ versi: 'vps-1.0' })).catch(() => {});
                detak = Date.now();
            }

            if (KEADAAN.dijedaLokal) {
                await tidur(CFG.jeda);
                continue;
            }

            /*
             * Mode aman TIDAK menyentuh antrean sama sekali.
             *
             * Memanggil /tugas membuat Phoenix menandai unggahan itu "dipegang
             * bot"; kalau lalu dilepas sebagai gagal (seperti yang dilakukan
             * mode aman), pesanan pelanggan sungguhan ikut jadi korban uji
             * coba. Selama belum mode penuh, bot hanya berdetak — pengujian
             * dilakukan manual dengan `node bot.mjs --sekali`.
             */
            if (CFG.mode !== 'penuh' && !CFG.sekali) {
                await tidur(CFG.jeda);
                continue;
            }

            const r = await api('GET', '/tugas');

            if (r.dijeda) {
                catat('Bot dijeda dari dasbor Phoenix.');
            } else if (r.tugas) {
                await kerjakan(ctx, hal, r.tugas);
            }
        } catch (e) {
            catat('Putaran gagal:', e.message);
        }

        if (CFG.sekali) return;

        await tidur(CFG.jeda);
    }
}

async function jalan() {
    for (const [kunci, nilai] of Object.entries({
        PHOENIX_URL: CFG.phoenix, PHOENIX_TOKEN: CFG.token, SUBMITIN_EMAIL: CFG.email, SUBMITIN_PASSWORD: CFG.sandi,
    })) {
        if (!nilai) throw new Error(`${kunci} belum diisi di .env`);
    }

    catat(`Bot mulai — mode ${CFG.mode.toUpperCase()}, tanya antrean tiap ${CFG.jeda / 1000} detik.`);
    if (CFG.mode !== 'penuh') {
        catat('MODE AMAN: antrean TIDAK diambil sama sekali, jadi pesanan pelanggan aman.');
        catat('Uji satu tugas secara manual dengan: node bot.mjs --sekali');
    }

    const ctx = await bukaBrowser();
    const hal = ctx.pages()[0] ?? await ctx.newPage();

    const tutup = async () => {
        catat('Berhenti…');
        await ctx.close().catch(() => {});
        process.exit(0);
    };
    process.on('SIGTERM', tutup);
    process.on('SIGINT', tutup);

    try {
        await keForm(hal);
        const siap = await periksaSiap(hal);
        catat('Keadaan awal:', siap.siap ? siap.paket.pesan : siap.masalah);

        await putaran(ctx, hal);
    } finally {
        await ctx.close().catch(() => {});
    }
}

jalan().catch((e) => {
    catat('BERHENTI:', e.message);
    process.exit(1);
});
