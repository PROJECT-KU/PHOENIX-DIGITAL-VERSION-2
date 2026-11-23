/**
 * Pembaca metode bayar submitin.id — DIJALANKAN DI DALAM HALAMAN.
 *
 * Dipisahkan dari bot.mjs supaya potongan kode yang sama persis bisa diuji di
 * halaman submitin sungguhan lewat `uji-bayar.mjs`, tanpa menjalankan botnya
 * (lihat README). Karena isinya diserialkan ke browser oleh Playwright,
 * fungsi ini TIDAK BOLEH menyentuh apa pun dari luar dirinya sendiri: tidak ada
 * import, tidak ada konstanta modul.
 *
 * URUTAN PILIHAN: paket ("Max") lebih dulu, baru "Gunakan Saldo" (keputusan
 * 23 Nov 2026). Paketnya sudah dibayar di muka, jadi memakainya tidak menambah
 * ongkos, sedangkan saldo terpotong per dokumen. QRIS tidak pernah dipilih —
 * ia menunggu pembayaran manual, jadi pesanan akan menggantung.
 *
 * Bentuk nyata barisnya di submitin.id (dipetakan 23 Nov 2026, bukan tebakan):
 *   <label class="pay-method-label" data-pm="package" data-pkg-id="6933">
 *     <span class="pm-text">Max</span>
 *     <span class="pm-badge">25x tersisa - s/d 07/12/2026</span>
 *   <label class="pay-method-label pm-disabled" data-pm="wallet" data-balance="2100">
 * Baris yang tidak bisa dipakai diberi kelas `pm-disabled` DAN radionya
 * `disabled` — keduanya diperiksa.
 *
 * Angka "Total Bayar" SENGAJA tidak dipakai sebagai pengaman kecukupan:
 * submitin tetap menampilkan harga per dokumen (Rp 12.900) walau dibayar
 * dengan paket.
 *
 * @returns {{ada: boolean, habis: boolean, pilihan: 'package'|'wallet'|null,
 *            kuota: number, harga: number|null, paket?: object[],
 *            paketBisa?: object|null, saldo?: object, pesan: string}}
 */
export function bacaBayarDiHalaman() {

    const bersih = (t) => String(t ?? '').replace(/\s+/g, ' ').trim();
    const angka = (t) => {
        const m = bersih(t).match(/Rp\s*([\d.]+)/i);

        return m ? parseInt(m[1].replace(/\./g, ''), 10) : null;
    };
    const rp = (n) => 'Rp ' + n.toLocaleString('id-ID');

    const ringkas = bersih(document.querySelector('#orderForm')?.innerText);
    const hargaM = ringkas.match(/Harga per dokumen\s*Rp\s*([\d.]+)/i);
    const harga = hargaM ? parseInt(hargaM[1].replace(/\./g, ''), 10) : null;

    if (!document.querySelector('#payMethods')) {
        return {
            ada: false,
            habis: true,
            pilihan: null,
            kuota: 0,
            harga,
            pesan: 'Metode pembayaran tidak muncul di submitin.id (sesi login mungkin putus).',
        };
    }

    const mati = (el) => el.classList.contains('pm-disabled') || !!el.querySelector('input:disabled');

    /* --- Paket aktif. Bisa lebih dari satu; dipakai yang pertama yang sehat. */
    const hariIni = new Date();
    hariIni.setHours(0, 0, 0, 0);

    const paket = [...document.querySelectorAll('#payMethods [data-pm="package"]')].map((el, indeks) => {
        const nama = bersih(el.querySelector('.pm-text')?.textContent) || 'Paket';
        const label = bersih(el.querySelector('.pm-badge')?.textContent);
        const sisaM = label.match(/(\d+)\s*x\s*tersisa/i);
        const tglM = label.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})/);
        const sisa = sisaM ? parseInt(sisaM[1], 10) : null;
        const id = el.dataset.pkgId || '';
        const kedaluwarsa = tglM ? new Date(+tglM[3], +tglM[2] - 1, +tglM[1]) < hariIni : false;
        const tertutup = mati(el) || /habis|kedaluwarsa|kadaluarsa|expired/i.test(label);

        return {
            indeks,
            id,
            nama,
            label,
            sisa,
            sampai: tglM ? tglM[0] : null,
            kedaluwarsa,
            // sisa null = labelnya tidak menyebut angka; barisnya tetap
            // dipakai selama submitin sendiri tidak mematikannya.
            bisa: !tertutup && !kedaluwarsa && (sisa === null || sisa > 0) && !!id && id !== '0',
        };
    });

    const paketBisa = paket.find((p) => p.bisa) ?? null;

    /* --- Saldo. */
    const elSaldo = document.querySelector('#payMethods [data-pm="wallet"]');
    const nilaiData = elSaldo?.dataset.balance;
    const nilai = elSaldo
        ? (nilaiData !== undefined && nilaiData !== '' ? parseInt(nilaiData, 10) : angka(elSaldo.textContent))
        : null;

    const saldo = {
        ada: !!elSaldo,
        nilai: Number.isFinite(nilai) ? nilai : null,
        mati: elSaldo ? mati(elSaldo) : true,
    };
    saldo.bisa = harga && saldo.nilai !== null ? Math.floor(saldo.nilai / harga) : null;
    saldo.cukup = !!elSaldo && !saldo.mati && saldo.nilai !== null && harga !== null && saldo.nilai >= harga;

    /* --- Pilihan bot: paket dulu, saldo belakangan, QRIS tidak pernah. */
    const pilihan = paketBisa ? 'package' : (saldo.cukup ? 'wallet' : null);
    const kuota = paketBisa ? (paketBisa.sisa ?? 1) : (saldo.cukup ? (saldo.bisa ?? 0) : 0);

    const pesanSaldo = !saldo.ada
        ? 'metode "Gunakan Saldo" tidak ada di halaman submitin.id'
        : (saldo.nilai === null
            ? 'saldo submitin.id tidak terbaca'
            : 'saldo ' + rp(saldo.nilai) + (saldo.cukup
                ? (saldo.bisa ? ' (±' + saldo.bisa + ' dokumen)' : '')
                : ' kurang dari harga per dokumen' + (harga ? ' ' + rp(harga) : '')));

    let pesan;
    if (pilihan === 'package') {
        pesan = 'Paket ' + paketBisa.nama
            + (paketBisa.sisa === null ? '' : ' sisa ' + paketBisa.sisa + 'x')
            + (paketBisa.sampai ? ' (s/d ' + paketBisa.sampai + ')' : '');
    } else if (pilihan === 'wallet') {
        pesan = (paket.length ? 'Paket submitin habis, pakai ' : '') + pesanSaldo;
    } else if (!saldo.ada && !paket.length) {
        // Hanya QRIS: pesanan akan menggantung menunggu pembayaran manual, jadi
        // bot tidak pernah memakainya.
        pesan = 'submitin.id tidak menyediakan paket maupun saldo (hanya QRIS, yang tidak pernah dipakai bot).';
    } else {
        pesan = (paket.length ? 'Paket submitin habis dan ' : '') + pesanSaldo + '.';
    }

    return {
        ada: true,
        habis: pilihan === null,
        pilihan,
        kuota,
        harga,
        paket,
        paketBisa,
        saldo,
        pesan: pesan.charAt(0).toUpperCase() + pesan.slice(1),
    };
}
