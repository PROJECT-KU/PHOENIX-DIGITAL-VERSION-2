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

    if (/auth=required/.test(hal.url()) || !/cekplagiat\.id\/d/.test(hal.url())) {
        throw Object.assign(
            new Error('Sesi cekplagiat.id sudah habis — perlu login Google ulang lalu cookienya dititipkan lagi ke VPS.'),
            { sesiHabis: true },
        );
    }
}

/** Sisa koin dari tautan "N Koin" di kepala halaman. */
export async function bacaKoin(hal) {
    const teks = await hal.locator('a[href="/u"]').first().innerText().catch(() => '');
    const angka = parseInt(String(teks).replace(/[^\d]/g, ''), 10);

    return Number.isNaN(angka) ? null : angka;
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
 * Yang memakai ANGKA tidak ada padanannya di sini. Mengirimkannya begitu saja
 * berarti pelanggan menerima setelan yang bukan dibayarnya, jadi tugas seperti
 * itu ditolak supaya dikerjakan admin.
 */
export function periksaSaringan(filter) {
    const f = filter || {};
    const angka = [];

    if (f.excl_source) angka.push('Exclude Sources');
    if (f.excl_match) angka.push('Exclude Matches');

    return angka.length
        ? `Saringan ${angka.join(' & ')} memakai batas angka yang tidak ada di cekplagiat.id, `
            + 'jadi pengecekan tidak bisa dipindahkan ke sana. Kerjakan manual.'
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
    await setSakelar(hal, SAKELAR.miripKecil, false);

    return { biaya: await bacaBiaya(hal) };
}

/* ================================================================
 | Kirim & tunggu hasil
 * ================================================================ */

/** Tekan "Proses dan Unggah"; mengembalikan kode dokumen dari alamat /d/<kode>. */
export async function kirim(hal) {
    const pindah = hal.waitForURL(/\/d\/[A-Za-z0-9_-]{6,}/, { timeout: 5 * 60 * 1000 }).catch(() => null);
    const tombol = tombolKirim(hal);

    try {
        await tombol.click({ timeout: 20000 });
    } catch {
        await tombol.evaluate((el) => el.click());
    }

    await pindah;

    const cocok = hal.url().match(/\/d\/([A-Za-z0-9_-]{6,})/);
    if (!cocok) throw new Error('Sesudah "Proses dan Unggah", cekplagiat.id tidak membuka halaman dokumen.');

    return cocok[1];
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
