/**
 * Gladi bersih penyedia cadangan — TIDAK mengirim apa pun.
 *
 * Memakai profil & cookie yang sama dengan bot, mengisi form cekplagiat.id
 * dengan dokumen contoh, lalu berhenti tepat sebelum "Proses dan Unggah".
 * Koin tidak terpakai dan antrean Phoenix tidak disentuh sama sekali.
 *
 *   sudo -u bot node /opt/botturnitin/uji-cekplagiat.mjs
 */

import { chromium } from 'playwright';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import * as cekplagiat from './cekplagiat.mjs';

const AKAR = path.dirname(new URL(import.meta.url).pathname);
const PROFIL = path.join(AKAR, 'profil');
const COOKIE = path.join(AKAR, 'cekplagiat-cookie.json');

/** PDF contoh satu halaman, dibuat di tempat supaya tidak perlu berkas asli. */
function pdfContoh() {
    const isi = 'Dokumen uji bot Phoenix. Naskah ini sengaja pendek dan tidak dikirim ke mana pun.';
    const halaman = `BT /F1 12 Tf 60 720 Td (${isi}) Tj ET`;
    const obj = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
        `<< /Length ${halaman.length} >>\nstream\n${halaman}\nendstream`,
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
    ];

    let keluar = '%PDF-1.4\n';
    const offset = [];
    obj.forEach((o, i) => { offset.push(keluar.length); keluar += `${i + 1} 0 obj\n${o}\nendobj\n`; });
    const xref = keluar.length;
    keluar += `xref\n0 ${obj.length + 1}\n0000000000 65535 f \n`
        + offset.map((o) => String(o).padStart(10, '0') + ' 00000 n \n').join('')
        + `trailer\n<< /Size ${obj.length + 1} /Root 1 0 R >>\nstartxref\n${xref}\n%%EOF\n`;

    const berkas = path.join(os.tmpdir(), 'PD-UJI-CONTOH.pdf');
    fs.writeFileSync(berkas, keluar, 'latin1');

    return berkas;
}

const ctx = await chromium.launchPersistentContext(PROFIL, {
    headless: true,
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu'],
    viewport: { width: 1440, height: 900 },
    acceptDownloads: true,
    locale: 'id-ID',
    timezoneId: 'Asia/Jakarta',
});

if (fs.existsSync(COOKIE)) {
    await ctx.addCookies(JSON.parse(fs.readFileSync(COOKIE, 'utf8')));
    console.log('cookie sesi dipasang');
}

const hal = await ctx.newPage();
const berkas = pdfContoh();

try {
    const siap = await cekplagiat.periksaSiap(hal, 22);
    console.log('siap   :', siap.siap, '| koin:', siap.koin, '|', siap.pesan);
    if (!siap.siap && siap.koin === null) throw new Error('berhenti: sesi/koin tidak terbaca');

    const tugas = {
        order_number: 'INV-UJI-0000',
        nama_berkas: path.basename(berkas),
        filter: { excl_quotes: true, excl_biblio: true },
    };

    const { biaya } = await cekplagiat.isiForm(hal, tugas, berkas, tugas.nama_berkas);
    console.log('biaya  :', biaya, 'koin');

    const potret = path.join(AKAR, 'potret', `uji-cekplagiat-${Date.now()}.png`);
    fs.mkdirSync(path.dirname(potret), { recursive: true });
    await hal.locator('[role=dialog]').first().screenshot({ path: potret }).catch(() => {});
    console.log('potret :', potret);
    console.log('BERHENTI sebelum "Proses dan Unggah" — tidak ada koin yang terpakai.');
} finally {
    fs.rmSync(berkas, { force: true });
    await ctx.close();
}
