/**
 * Gladi bersih metode bayar submitin.id — TIDAK PERNAH MENEKAN TOMBOL BAYAR.
 *
 * Memakai pembaca yang sama persis dengan bot (`baca-bayar.mjs`), jadi hasilnya
 * benar-benar mewakili keputusan bot: paket ("Max") dulu, kalau habis baru
 * "Gunakan Saldo", QRIS tidak pernah. Yang diperiksa: baris metode bayar yang
 * terbaca, pilihan bot, lalu — sesudah kartunya ditekan — `#payMethodType`,
 * `#payPackageId`, dan bunyi tombol `#payBtn`.
 *
 * Jalankan di VPS:
 *   sudo -u bot node /opt/botturnitin/uji-bayar.mjs            # halaman sungguhan
 *   sudo -u bot node /opt/botturnitin/uji-bayar.mjs --contoh   # contoh keadaan, tanpa jaringan
 *
 * `--contoh` memasang beberapa bentuk halaman buatan (paket habis, paket
 * kedaluwarsa, tanpa paket, saldo cukup/kurang) lalu memastikan pilihannya
 * sesuai aturan. Keadaan "paket habis lalu jatuh ke saldo" tidak bisa ditunggu
 * di halaman sungguhan, jadi cabang itu diuji di sini.
 *
 * Bot biasanya sedang memegang profil Chromium-nya. Supaya tidak bentrok,
 * jalankan di atas salinan profil:
 *   sudo -u bot cp -a /opt/botturnitin/profil /tmp/uji-profil
 *   sudo -u bot rm -f /tmp/uji-profil/Singleton*
 *   sudo -u bot env PROFIL=/tmp/uji-profil node /opt/botturnitin/uji-bayar.mjs
 *
 * Tidak ada berkas yang diunggah dan Phoenix tidak dihubungi, jadi kuota paket
 * maupun saldo tidak berkurang sama sekali.
 */

import { chromium } from 'playwright';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { bacaBayarDiHalaman } from './baca-bayar.mjs';

const AKAR = path.dirname(new URL(import.meta.url).pathname);
const CONTOH_SAJA = process.argv.includes('--contoh');
// Contoh keadaan tidak membuka submitin sama sekali, jadi ia TIDAK memakai
// profil bot — supaya tidak berebut dengan Chromium milik service yang hidup.
const PROFIL = CONTOH_SAJA
    ? fs.mkdtempSync(path.join(os.tmpdir(), 'uji-bayar-'))
    : (process.env.PROFIL || path.join(AKAR, 'profil'));
const POTRET = process.env.POTRET || path.join(AKAR, 'potret', `uji-bayar-${Date.now()}.png`);

const ctx = await chromium.launchPersistentContext(PROFIL, {
    headless: true,
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu'],
});

const hal = ctx.pages()[0] ?? await ctx.newPage();
let keluar = 0;

/**
 * Contoh keadaan halaman + pilihan yang seharusnya diambil bot.
 *
 * Bentuk HTML-nya disalin dari halaman submitin sungguhan (23 Nov 2026), hanya
 * angkanya yang diubah.
 */
const CONTOH = [
    {
        nama: 'paket bersisa, saldo kurang → paket',
        harga: 12900,
        bayar: [
            '<label class="pay-method-label is-selected" data-pm="qris" data-pkg-id="0"><input type="radio" name="_pay_method_ui" value="qris" checked><span class="pm-text">Bayar via QRIS</span></label>',
            '<label class="pay-method-label pm-disabled" data-pm="wallet" data-pkg-id="0" data-balance="2100"><input type="radio" name="_pay_method_ui" value="wallet" disabled><span class="pm-text">Gunakan Saldo</span><span class="pm-badge">Rp 2.100 - Kurang</span></label>',
            '<label class="pay-method-label" data-pm="package" data-pkg-id="6933"><input type="radio" name="_pay_method_ui" value="package_6933"><span class="pm-text">Max</span><span class="pm-badge">25x tersisa - s/d 07/12/2026</span></label>',
        ],
        pilihan: 'package',
        kuota: 25,
    },
    {
        nama: 'paket 0x tersisa, saldo cukup → saldo',
        harga: 12900,
        bayar: [
            '<label class="pay-method-label" data-pm="wallet" data-pkg-id="0" data-balance="50000"><input type="radio" name="_pay_method_ui" value="wallet"><span class="pm-text">Gunakan Saldo</span><span class="pm-badge">Rp 50.000</span></label>',
            '<label class="pay-method-label" data-pm="package" data-pkg-id="6933"><input type="radio" name="_pay_method_ui" value="package_6933"><span class="pm-text">Max</span><span class="pm-badge">0x tersisa - s/d 07/12/2026</span></label>',
        ],
        pilihan: 'wallet',
        kuota: 3,
    },
    {
        nama: 'paket kedaluwarsa, saldo cukup → saldo',
        harga: 12900,
        bayar: [
            '<label class="pay-method-label" data-pm="wallet" data-pkg-id="0" data-balance="30000"><input type="radio" name="_pay_method_ui" value="wallet"><span class="pm-text">Gunakan Saldo</span><span class="pm-badge">Rp 30.000</span></label>',
            '<label class="pay-method-label" data-pm="package" data-pkg-id="6933"><input type="radio" name="_pay_method_ui" value="package_6933"><span class="pm-text">Max</span><span class="pm-badge">9x tersisa - s/d 01/01/2020</span></label>',
        ],
        pilihan: 'wallet',
        kuota: 2,
    },
    {
        nama: 'paket dimatikan submitin & saldo kurang → tidak ada (dialihkan)',
        harga: 12900,
        bayar: [
            '<label class="pay-method-label pm-disabled" data-pm="wallet" data-pkg-id="0" data-balance="2100"><input type="radio" name="_pay_method_ui" value="wallet" disabled><span class="pm-text">Gunakan Saldo</span><span class="pm-badge">Rp 2.100 - Kurang</span></label>',
            '<label class="pay-method-label pm-disabled" data-pm="package" data-pkg-id="6933"><input type="radio" name="_pay_method_ui" value="package_6933" disabled><span class="pm-text">Max</span><span class="pm-badge">4x tersisa - s/d 07/12/2026</span></label>',
        ],
        pilihan: null,
        kuota: 0,
    },
    {
        nama: 'tanpa baris paket, saldo cukup → saldo',
        harga: 12900,
        bayar: [
            '<label class="pay-method-label" data-pm="qris" data-pkg-id="0"><span class="pm-text">Bayar via QRIS</span></label>',
            '<label class="pay-method-label" data-pm="wallet" data-pkg-id="0" data-balance="25800"><input type="radio" name="_pay_method_ui" value="wallet"><span class="pm-text">Gunakan Saldo</span><span class="pm-badge">Rp 25.800</span></label>',
        ],
        pilihan: 'wallet',
        kuota: 2,
    },
    {
        nama: 'QRIS saja → tidak ada (bot tidak pernah memilih QRIS)',
        harga: 12900,
        bayar: ['<label class="pay-method-label is-selected" data-pm="qris" data-pkg-id="0"><span class="pm-text">Bayar via QRIS</span></label>'],
        pilihan: null,
        kuota: 0,
    },
];

if (CONTOH_SAJA) {
    for (const c of CONTOH) {
        await hal.setContent(`<form id="orderForm">
            <div id="payMethods">${c.bayar.join('')}</div>
            <div>Harga per dokumen Rp ${c.harga.toLocaleString('id-ID')}</div>
        </form>`);

        const hasil = await hal.evaluate(bacaBayarDiHalaman);
        const ok = hasil.pilihan === c.pilihan && hasil.kuota === c.kuota;
        if (!ok) keluar = 1;

        console.log(`${ok ? '✓' : '✗'} ${c.nama}`);
        console.log(`   pilihan ${hasil.pilihan ?? '-'} (harusnya ${c.pilihan ?? '-'}), `
            + `kuota ${hasil.kuota} (harusnya ${c.kuota}) — ${hasil.pesan}`);
    }

    await ctx.close().catch(() => {});
    fs.rmSync(PROFIL, { recursive: true, force: true });
    console.log(keluar ? '\nHASIL: ADA CONTOH YANG MELESET.' : '\nHASIL: semua contoh sesuai aturan.');
    process.exit(keluar);
}

try {
    await hal.goto('https://submitin.id/services/plagiarism', { waitUntil: 'domcontentloaded', timeout: 60000 });
    await hal.waitForSelector('#orderForm', { timeout: 30000 });
    await hal.waitForSelector('#payMethods', { timeout: 20000 }).catch(() => {});

    // "Tidak dialihkan ke /user/login" BUKAN bukti sudah masuk — halaman ini
    // terbuka untuk tamu, metode bayarnya saja yang tidak dirender.
    const tamu = await hal.locator('a[href*="/user/login"]').count();
    console.log('login submitin  :', tamu ? 'BELUM MASUK' : 'sudah masuk');

    const bayar = await hal.evaluate(bacaBayarDiHalaman);

    console.log('harga/dokumen   :', bayar.harga === null ? '(tidak terbaca)' : 'Rp ' + bayar.harga.toLocaleString('id-ID'));
    for (const p of bayar.paket ?? []) {
        console.log(`paket           : ${p.nama} [id ${p.id}] — "${p.label}" → ${p.bisa ? 'BISA DIPAKAI' : 'tidak bisa dipakai'}`);
    }
    if (!(bayar.paket ?? []).length) console.log('paket           : (tidak ada baris paket di halaman)');
    console.log('saldo           :', bayar.saldo
        ? `${bayar.saldo.nilai === null ? '(tidak terbaca)' : 'Rp ' + bayar.saldo.nilai.toLocaleString('id-ID')}`
            + ` — ${bayar.saldo.cukup ? 'cukup' : 'tidak cukup'}${bayar.saldo.mati ? ', barisnya dimatikan' : ''}`
        : '(tidak ada)');
    console.log('pilihan bot     :', bayar.pilihan ?? 'TIDAK ADA — tugas dialihkan ke cadangan');
    console.log('kuota dilaporkan:', bayar.kuota, '| pesan:', bayar.pesan);

    if (bayar.pilihan) {
        const kartu = bayar.pilihan === 'package'
            ? hal.locator('#payMethods [data-pm="package"]').nth(bayar.paketBisa.indeks)
            : hal.locator('#payMethods [data-pm="wallet"]').first();

        await kartu.evaluate((el) => el.click());
        await hal.waitForTimeout(1000);

        const metode = await hal.locator('#payMethodType').inputValue().catch(() => '');
        const idPaket = await hal.locator('#payPackageId').inputValue().catch(() => '');
        const tombol = ((await hal.locator('#payBtn').textContent().catch(() => '')) || '').trim();

        console.log('payMethodType   :', metode, metode === bayar.pilihan ? '✓' : '✗ TIDAK SESUAI');
        console.log('payPackageId    :', idPaket || '(kosong)', bayar.pilihan === 'package'
            ? (idPaket && idPaket === bayar.paketBisa.id ? '✓' : '✗ TIDAK SESUAI')
            : '(tidak dipakai)');
        console.log('tombol #payBtn  :', `"${tombol}"`,
            (bayar.pilihan === 'package' ? /paket/i : /saldo/i).test(tombol) && !/qris/i.test(tombol) ? '✓' : '✗ TIDAK SESUAI');

        const cocok = metode === bayar.pilihan
            && (bayar.pilihan !== 'package' || idPaket === bayar.paketBisa.id)
            && (bayar.pilihan === 'package' ? /paket/i : /saldo/i).test(tombol)
            && !/qris/i.test(tombol);

        if (!cocok) keluar = 1;
    }

    await hal.locator('#orderForm').screenshot({ path: POTRET }).catch(() => hal.screenshot({ path: POTRET, fullPage: true }));
    console.log('tangkapan layar :', POTRET);
    console.log(keluar ? '\nHASIL: ADA YANG TIDAK SESUAI — jangan dipakai dulu.' : '\nHASIL: sesuai. Tombol bayar TIDAK ditekan, kuota utuh.');
} catch (e) {
    keluar = 1;
    console.log('GAGAL:', String(e.message || e).split('\n')[0]);
} finally {
    await ctx.close().catch(() => {});
}

process.exit(keluar);
