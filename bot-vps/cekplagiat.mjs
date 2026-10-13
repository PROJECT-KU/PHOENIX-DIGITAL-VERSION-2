/**
 * Penyedia cadangan: cekplagiat.id
 * =================================
 *
 * Dipakai HANYA bila submitin.id tidak bisa dipakai (halaman layanannya 503 /
 * form tidak muncul). Submitin tetap yang utama karena paketnya sudah dibeli
 * dan jauh lebih murah.
 *
 * Perbedaan yang WAJIB diingat (ditemukan 13 Okt 2026 saat memetakan situs):
 *
 * 1. Login HANYA lewat Google (tidak ada email+sandi, tidak ada API). Jadi bot
 *    tidak bisa login sendiri: sesinya dititipkan sekali lewat berkas cookie
 *    (lihat muatCookie di bot.mjs). Kalau sesinya habis, bot berhenti sopan dan
 *    minta admin login ulang — bukan mencoba menebak sandi.
 * 2. Batas berkas 10 MB (submitin 50 MB), sedangkan Phoenix menerima 20 MB dari
 *    pelanggan. Karena itu pengecilan PDF (Ghostscript) memang terpakai di sini.
 * 3. Biayanya KOIN, bukan paket: sekali cek plagiarisme 18–22 koin (±Rp1.000
 *    per koin). Bot menolak jalan kalau koinnya kurang — lebih baik tertunda
 *    daripada setengah jalan.
 * 4. Hanya "Basic Report" yang tersedia (Advanced sedang "Dalam Perbaikan"):
 *    nilai similarity + laporan PDF, tanpa rincian sumber.
 * 5. Saringannya cuma tiga sakelar tanpa angka. Tugas yang memakai batas angka
 *    (exclude sources X% / exclude matches N kata) TIDAK bisa ditiru persis,
 *    jadi tugas seperti itu dikembalikan untuk dikerjakan admin — bukan dikirim
 *    dengan setelan yang berbeda dari yang dibayar pelanggan.
 */

import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

export const NAMA = 'cekplagiat.id';
export const DASBOR = 'https://www.cekplagiat.id/d';
export const BATAS_MB = 10;

/** Kunci tombol React: id-nya berakhiran acak (`quotes-_r_8_`), jadi dicocokkan dari depan. */
const SAKELAR = {
    plagiarisme: 'plagiarismCheck-',
    ai: 'aiCheck-',
    drillbit: 'drillbitCheck-',
    kutipan: 'quotes-',
    pustaka: 'bibliography-',
    miripKecil: 'source-',
    basic: 'reportTier-basic-',
};

const tidur = (ms) => new Promise((r) => setTimeout(r, ms));

/* ================================================================
 | Dasar
 * ================================================================ */

/** Buka dasbor; melempar bila sesinya sudah habis. */
export async function keDasbor(hal) {
    if (!hal.url().startsWith(DASBOR)) {
        await hal.goto(DASBOR, { waitUntil: 'domcontentloaded', timeout: 60000 });
    }
    await tidur(1500);

    // Halamannya React: isinya baru muncul sesudah skripnya jalan. Di VPS
    // 1 vCPU itu bisa beberapa detik, jadi ditunggu — bukan ditebak.
    await hal.waitForSelector('a[href="/u"]', { state: 'attached', timeout: 30000 }).catch(() => {});

    if (/auth=required/.test(hal.url()) || !/cekplagiat\.id\/d/.test(hal.url())) {
        throw Object.assign(
            new Error('Sesi cekplagiat.id sudah habis — perlu login Google ulang lalu cookienya dititipkan lagi ke VPS.'),
            { sesiHabis: true },
        );
    }
}

/**
 * Sisa koin dari tautan "N Koin" di kepala halaman.
 *
 * Ditunggu sampai angkanya benar-benar muncul: pernah terbaca kosong sekali
 * (13 Okt 2026 19.00) hanya karena halamannya belum selesai dirender, dan bot
 * langsung berhenti mengambil antrean selama 30 menit.
 */
export async function bacaKoin(hal, tungguMs = 20000) {
    const batas = Date.now() + tungguMs;

    for (;;) {
        const teks = await hal.locator('a[href="/u"]').first().innerText().catch(() => '');
        const angka = parseInt(String(teks).replace(/[^\d]/g, ''), 10);

        if (!Number.isNaN(angka)) return angka;
        if (Date.now() > batas) return null;

        await tidur(1500);
    }
}

/**
 * Siap dipakai atau tidak — dipanggil sebelum tugas diambil alih dari submitin.
 *
 * @returns {{siap: boolean, koin: number|null, pesan: string}}
 */
export async function periksaSiap(hal, minimalKoin) {
    try {
        await keDasbor(hal);
    } catch (e) {
        return { siap: false, koin: null, pesan: e.message };
    }

    const koin = await bacaKoin(hal);

    if (koin === null) {
        return { siap: false, koin: null, pesan: 'Sisa koin cekplagiat.id tidak terbaca di halaman.' };
    }

    if (koin < minimalKoin) {
        return {
            siap: false,
            koin,
            pesan: `Koin cekplagiat.id tinggal ${koin}, sedangkan sekali cek butuh ±${minimalKoin}. Isi koin dulu di https://www.cekplagiat.id/u`,
        };
    }

    return { siap: true, koin, pesan: `cekplagiat.id siap, koin ${koin}.` };
}

/* ================================================================
 | Mengisi form
 * ================================================================ */

/** Sakelar React: keadaannya dibaca dari aria-checked, bukan dari input tersembunyi. */
async function setSakelar(hal, awalanId, mau) {
    const tombol = hal.locator(`[role=dialog] button[id^="${awalanId}"]`).first();
    if (!(await tombol.count())) throw new Error(`Sakelar ${awalanId} tidak ditemukan di form cekplagiat.id`);

    const sekarang = (await tombol.getAttribute('aria-checked').catch(() => null)) === 'true';
    if (sekarang === mau) return;

    await tombol.evaluate((el) => el.click());
    await tidur(400);

    const sesudah = (await tombol.getAttribute('aria-checked').catch(() => null)) === 'true';
    if (sesudah !== mau) throw new Error(`Sakelar ${awalanId} tidak mau berubah jadi ${mau ? 'aktif' : 'mati'}`);
}

/**
 * Saringan pelanggan → sakelar cekplagiat.id.
 *
 * submitin menerima ANGKA (exclude sources < 5%, exclude matches < 10 kata);
 * cekplagiat hanya punya satu sakelar "Kecualikan kata yang mirip" tanpa kolom
 * angka. Keputusan 13 Okt 2026: sakelar itu dipakai sebagai padanan terdekat —
 * menahan pesanannya membuat ±12% antrean menumpuk selama submitin mati —
 * tetapi penyesuaiannya DICATAT supaya admin tahu ambangnya bukan yang dipesan.
 *
 * @returns {string|null} catatan untuk kartu dasbor, atau null bila tidak ada
 */
export function catatanSaringan(filter) {
    const f = filter || {};
    const diminta = [];

    if (f.excl_source) {
        diminta.push(`Exclude Sources < ${f.excl_source.nilai}${f.excl_source.tipe === 'words' ? ' kata' : '%'}`);
    }
    if (f.excl_match) {
        diminta.push(`Exclude Matches < ${f.excl_match.kata} kata`);
    }

    return diminta.length
        ? `Dikerjakan lewat cekplagiat.id. ${diminta.join(' & ')} diganti sakelar `
            + '"Kecualikan kata yang mirip" (cekplagiat.id tidak punya kolom angka), '
            + 'jadi ambangnya mengikuti bawaan mereka — bukan angka yang dipesan.'
        : null;
}

/**
 * Buka jendela "Periksa Dokumen Baru".
 *
 * Tombolnya ada tiga (menu atas, sampul, kartu daftar) dan yang di menu atas
 * tertutup nav lengket, jadi klik biasa Playwright tersendat sampai batas
 * waktu. Dipilih yang benar-benar terlihat, lalu ditekan di tingkat DOM.
 */
async function bukaForm(hal) {
    const kena = await hal.evaluate(() => {
        const calon = [...document.querySelectorAll('button')]
            .filter((b) => b.innerText.trim() === 'Periksa Dokumen Baru');
        const pilih = calon.find((b) => b.offsetParent !== null) || calon[0];
        if (!pilih) return false;
        pilih.click();

        return true;
    });

    if (!kena) throw new Error('Tombol "Periksa Dokumen Baru" tidak ada di dasbor cekplagiat.id');

    // Kolom berkasnya sengaja disembunyikan di balik area seret-lepas, jadi
    // yang ditunggu keberadaannya (attached), bukan terlihat.
    await hal.waitForSelector('[role=dialog] input[type=file]', { state: 'attached', timeout: 30000 });
    await tidur(800);
}

/** Biaya tugas ini, dibaca dari tombol "Proses dan Unggah (22 Koin)". */
async function bacaBiaya(hal) {
    const teks = await tombolKirim(hal).innerText().catch(() => '');
    const cocok = String(teks).match(/\((\d+)\s*Koin\)/i);

    return cocok ? parseInt(cocok[1], 10) : null;
}

function tombolKirim(hal) {
    return hal.locator('[role=dialog] button').filter({ hasText: /Proses dan Unggah/i }).first();
}

/**
 * Isi form sampai siap dikirim (tombolnya TIDAK ditekan di sini).
 *
 * @returns {{biaya: number|null}}
 */
export async function isiForm(hal, tugas, berkasPath, namaBerkas) {
    await bukaForm(hal);

    await hal.setInputFiles('[role=dialog] input[type=file]', berkasPath);

    /*
     * Sesudah berkas dipilih, jendelanya berganti jadi pratinjau: kolom
     * berkasnya hilang dan NAMANYA DIPOTONG DARI DEPAN ("… -0003.pdf"), jadi
     * mencocokkan nama utuh selalu gagal. Yang ditunggu: baris ukuran berkas
     * muncul; sesudah itu ekor namanya dicocokkan supaya yakin berkas yang
     * terbaca memang berkas tugas ini.
     */
    const terbaca = await hal.waitForFunction(
        () => /\d+(?:[.,]\d+)?\s*(?:KB|MB)/i.test(document.querySelector('[role=dialog]')?.innerText || ''),
        null,
        { timeout: 20000 },
    ).catch(() => null);

    if (!terbaca) throw new Error('Berkas tidak terbaca oleh form cekplagiat.id');

    // Delapan huruf terakhir: pemotongannya memakan sebagian nama, jadi ekor
    // yang lebih panjang justru tidak pernah cocok.
    const ekor = namaBerkas.slice(-8);
    const isiJendela = await hal.locator('[role=dialog]').innerText().catch(() => '');
    if (!isiJendela.includes(ekor)) {
        throw new Error(`Berkas yang terbaca cekplagiat.id bukan ${namaBerkas} — dihentikan agar hasil tidak tertukar.`);
    }

    // Layanan: cek plagiarisme saja. Cek AI di sini memakai GPTZero — mesin yang
    // berbeda dari Turnitin, jadi tidak pernah dinyalakan oleh bot.
    await setSakelar(hal, SAKELAR.plagiarisme, true);
    await setSakelar(hal, SAKELAR.ai, false);
    await setSakelar(hal, SAKELAR.drillbit, false);

    // Laporan: Basic (Advanced sedang dimatikan oleh cekplagiat.id).
    await setSakelar(hal, SAKELAR.basic, true);

    const f = tugas.filter || {};
    await setSakelar(hal, SAKELAR.kutipan, !!f.excl_quotes);
    await setSakelar(hal, SAKELAR.pustaka, !!f.excl_biblio);
    // Padanan terdekat untuk exclude sources / exclude matches yang berangka.
    await setSakelar(hal, SAKELAR.miripKecil, !!(f.excl_source || f.excl_match));

    return { biaya: await bacaBiaya(hal) };
}

/* ================================================================
 | Kirim & tunggu hasil
 * ================================================================ */

/**
 * Cari dokumen di daftar /d berdasarkan ekor nama berkasnya.
 *
 * Dipakai dua kali: sesudah menekan "Proses dan Unggah" (situsnya TIDAK selalu
 * pindah halaman — dokumennya cuma muncul di daftar), dan SEBELUM mengunggah,
 * supaya berkas yang sudah pernah dibayar tidak diunggah lagi.
 *
 * @returns {Promise<string|null>} kode dokumen, atau null bila belum ada
 */
export async function cariDokumen(hal, namaBerkas) {
    const ekor = namaBerkas.slice(-8);

    if (!/cekplagiat\.id\/d(\/|$|\?)/.test(hal.url())) {
        await hal.goto(DASBOR, { waitUntil: 'domcontentloaded', timeout: 60000 });
    } else {
        await hal.reload({ waitUntil: 'domcontentloaded', timeout: 60000 }).catch(() => {});
    }
    await tidur(2500);

    return hal.evaluate((e) => {
        const tautan = [...document.querySelectorAll('a[href^="/d/"]')]
            .filter((a) => a.innerText.includes(e));
        if (!tautan.length) return null;
        const cocok = tautan[0].getAttribute('href').match(/^\/d\/(.+)$/);

        return cocok ? cocok[1] : null;
    }, ekor);
}

/**
 * Tekan "Proses dan Unggah" lalu kembalikan kode dokumennya.
 *
 * Sesudah tombolnya ditekan KOIN SUDAH TERPOTONG, jadi kegagalan di sini tidak
 * boleh berujung "unggah lagi": kodenya dikejar sampai ketemu, lewat alamat
 * halaman maupun lewat daftar dokumen (13 Okt 2026 situsnya tetap di /d dan
 * 22 koin nyaris terbakar dua kali).
 */
export async function kirim(hal, namaBerkas) {
    const pindah = hal.waitForURL(/\/d\/[A-Za-z0-9_-]{6,}/, { timeout: 90 * 1000 }).catch(() => null);
    const tombol = tombolKirim(hal);

    try {
        await tombol.click({ timeout: 20000 });
    } catch {
        await tombol.evaluate((el) => el.click());
    }

    await pindah;

    const cocok = hal.url().match(/\/d\/([A-Za-z0-9_-]{6,})/);
    if (cocok) return cocok[1];

    // Tidak pindah halaman: cari dokumennya di daftar, beri waktu sampai muncul.
    for (let i = 0; i < 10; i++) {
        const kode = await cariDokumen(hal, namaBerkas);
        if (kode) return kode;
        await tidur(15000);
    }

    throw Object.assign(
        new Error('Berkas sudah dikirim ke cekplagiat.id (koin terpotong) tetapi kode dokumennya tidak ketemu. '
            + 'Buka https://www.cekplagiat.id/d dan ambil hasilnya manual.'),
        { sudahTerkirim: true },
    );
}

/** Keadaan satu dokumen: selesai/belum, persen, dan apakah berkasnya siap. */
export async function bacaKeadaan(hal, kode) {
    const alamat = `${DASBOR}/${kode}`;
    if (!hal.url().startsWith(alamat)) {
        await hal.goto(alamat, { waitUntil: 'domcontentloaded', timeout: 60000 });
    } else {
        await hal.reload({ waitUntil: 'domcontentloaded', timeout: 60000 }).catch(() => {});
    }
    await tidur(2500);

    const teks = await hal.locator('body').innerText().catch(() => '');
    const persenCocok = teks.match(/(\d{1,3})\s*%\s*\n?\s*Similarity/i);
    const adaUnduh = await hal.locator('button').filter({ hasText: /^Unduh Hasil/ }).count();

    return {
        gagal: /GAGAL|DIBATALKAN|REFUND/i.test(teks),
        selesai: /Status\s*\n?\s*SELESAI/i.test(teks) && !!adaUnduh,
        persen: persenCocok ? parseInt(persenCocok[1], 10) : null,
        teks,
    };
}

/** Unduh laporan PDF lewat sesi browser yang sama. */
export async function unduhLaporan(hal) {
    const tombol = hal.locator('button').filter({ hasText: /^Unduh Hasil/ }).first();
    if (!(await tombol.count())) return null;

    const menunggu = hal.waitForEvent('download', { timeout: 120000 }).catch(() => null);
    await tombol.click();
    const unduhan = await menunggu;
    if (!unduhan) return null;

    const tujuan = path.join(os.tmpdir(), 'cekplagiat-' + Date.now() + '.pdf');
    await unduhan.saveAs(tujuan);
    const isi = fs.readFileSync(tujuan);
    fs.rmSync(tujuan, { force: true });

    return isi;
}
