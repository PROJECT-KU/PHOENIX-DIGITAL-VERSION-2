// ==UserScript==
// @name         Phoenix — Bot Turnitin (submitin.id)
// @namespace    https://phoenixdigitalwarehouse.com/
// @version      1.0.0
// @description  Mengambil antrean cek plagiasi dari Phoenix, mengerjakannya di submitin.id memakai paket Standard (Turnitin V1), lalu mengirim laporannya kembali ke pesanan yang benar.
// @match        https://submitin.id/*
// @run-at       document-idle
// @noframes
// @grant        GM_xmlhttpRequest
// @grant        GM_getValue
// @grant        GM_setValue
// @grant        unsafeWindow
// @connect      phoenixdigitalwarehouse.com
// @connect      127.0.0.1
// @connect      localhost
// @connect      submitin.id
// @connect      *
// @updateURL    https://phoenixdigitalwarehouse.com/bot/phoenix-turnitin.user.js
// @downloadURL  https://phoenixdigitalwarehouse.com/bot/phoenix-turnitin.user.js
// ==/UserScript==

/*
 * CARA KERJA SINGKAT
 * ------------------
 * Biarkan SATU tab https://submitin.id/services/plagiarism terbuka (sudah login
 * akun submitin). Tiap menit skrip bertanya ke Phoenix apakah ada unggahan cek
 * plagiasi baru. Kalau ada:
 *   1. unduh berkas customer dari Phoenix (nama berkas diganti penanda
 *      PD-xxxxxxxx-INV-…, nama asli customer tidak pernah dikirim),
 *   2. isi form: Turnitin V1, filter sesuai pilihan customer, paket Standard,
 *   3. PERIKSA metode bayar = paket Standard & tombol "Gunakan Paket" — kalau
 *      bukan, BERHENTI. Bot tidak pernah memilih QRIS maupun Saldo.
 *      (Angka "Total Bayar" TIDAK dipakai: submitin tetap menampilkan harga
 *      per dokumen walau dibayar dengan paket.)
 *   4. klik "Gunakan Paket", catat kode order SC-… ke Phoenix,
 *   5. tunggu di halaman status sampai "Unduh Plagiasi" muncul,
 *   6. pastikan penanda berkas & kode cocok, unduh PDF, kirim ke Phoenix.
 * Phoenix menyimpan hasil ke pesanan yang kodenya cocok lalu mengirim email
 * ke customer. Semua kegagalan dilaporkan ke kartu dashboard admin.
 *
 * Tidak ada data customer (nama, HP, email) yang diisikan ke submitin.id:
 * judul = penanda + nomor order, nomor WhatsApp = nomor akun submitin sendiri.
 */

(function () {
    'use strict';

    const VERSI = '1.0.0';
    const JEDA_TANYA_MS = 60 * 1000;          // tanya antrean tiap 1 menit
    const JEDA_DETAK_TUNGGU_MS = 3 * 60 * 1000; // kabari Phoenix saat menunggu hasil
    const BATAS_REDIRECT_MS = 10 * 60 * 1000;  // unggah + kirim form
    const BATAS_HASIL_MS = 60 * 60 * 1000;     // laporan harus keluar dalam 60 menit
    const MUAT_ULANG_STATUS_MS = 30 * 1000;

    /* ================================================================
     | Penyimpanan
     * ================================================================ */
    const K = {
        url: 'pd_url', token: 'pd_token', nyala: 'pd_nyala', keadaan: 'pd_keadaan', log: 'pd_log', kunci: 'pd_kunci',
    };
    const ambil = (k, d) => { try { const v = GM_getValue(k); return v === undefined ? d : v; } catch (_) { return d; } };
    const simpan = (k, v) => GM_setValue(k, v);

    const cfg = () => ({
        url: String(ambil(K.url, 'https://phoenixdigitalwarehouse.com')).replace(/\/+$/, ''),
        token: String(ambil(K.token, '')),
        nyala: !!ambil(K.nyala, false),
    });

    /** Keadaan tugas bertahan melewati pindah halaman (form → status). */
    const keadaan = () => ambil(K.keadaan, { tahap: 'siaga' });
    const setKeadaan = (st) => simpan(K.keadaan, st);
    const siaga = () => setKeadaan({ tahap: 'siaga' });

    function log(pesan) {
        const baris = new Date().toLocaleTimeString('id-ID') + '  ' + pesan;
        const arr = ambil(K.log, []);
        arr.unshift(baris);
        simpan(K.log, arr.slice(0, 30));
        console.log('[Bot Phoenix]', pesan);
        gambarPanel();
    }

    /* ================================================================
     | Satu tab saja yang boleh bekerja
     * ================================================================ */
    // Identitas tab bertahan saat tab ini pindah halaman (form → status), jadi
    // gilirannya tidak direbut tab submitin lain di tengah mengunggah.
    const ID_TAB = (() => {
        try {
            let id = sessionStorage.getItem('pd_bot_tab');
            if (!id) { id = Math.random().toString(36).slice(2); sessionStorage.setItem('pd_bot_tab', id); }
            return id;
        } catch (_) { return Math.random().toString(36).slice(2); }
    })();
    function pegangKunci() {
        const k = ambil(K.kunci, null);
        const kini = Date.now();
        if (!k || k.tab === ID_TAB || kini - k.at > 20000) {
            simpan(K.kunci, { tab: ID_TAB, at: kini });
            return true;
        }
        return false;
    }

    /* ================================================================
     | API Phoenix
     * ================================================================ */
    function api(metode, jalur, data, jenisRespons) {
        const c = cfg();
        return new Promise((resolve, reject) => {
            GM_xmlhttpRequest({
                method: metode,
                url: c.url + '/api/bot-turnitin' + jalur,
                headers: { Authorization: 'Bearer ' + c.token, Accept: 'application/json' },
                data: data,
                responseType: jenisRespons || 'json',
                timeout: 5 * 60 * 1000,
                onload: (r) => {
                    if (jenisRespons === 'blob') {
                        return r.status === 200 ? resolve(r.response) : reject(new Error('HTTP ' + r.status + ' saat mengunduh berkas'));
                    }
                    let body = r.response;
                    if (typeof body === 'string') { try { body = JSON.parse(body); } catch (_) { body = null; } }
                    if (r.status >= 200 && r.status < 300) return resolve(body || {});
                    const e = new Error((body && (body.pesan || body.message)) || ('HTTP ' + r.status));
                    e.status = r.status;
                    reject(e);
                },
                onerror: () => reject(new Error('Tidak bisa menghubungi Phoenix')),
                ontimeout: () => reject(new Error('Phoenix tidak menjawab (timeout)')),
            });
        });
    }
    const formData = (obj) => { const fd = new FormData(); Object.entries(obj).forEach(([k, v]) => fd.append(k, v)); return fd; };

    async function laporGagal(tugasId, pesan, kuotaHabis) {
        log('GAGAL: ' + pesan);
        try {
            await api('POST', '/tugas/' + tugasId + '/gagal', formData({ pesan: pesan.slice(0, 480), kuota_habis: kuotaHabis ? '1' : '0' }));
        } catch (e) {
            log('Laporan gagal tidak terkirim: ' + e.message);
        }
        siaga();
    }

    /* ================================================================
     | Alat bantu DOM
     * ================================================================ */
    const $ = (sel, akar) => (akar || document).querySelector(sel);
    const $$ = (sel, akar) => Array.from((akar || document).querySelectorAll(sel));
    const tidur = (ms) => new Promise((r) => setTimeout(r, ms));
    const angka = (teks) => parseInt(String(teks || '').replace(/[^\d]/g, ''), 10);

    async function tunggu(fn, ms, jeda) {
        const akhir = Date.now() + (ms || 15000);
        while (Date.now() < akhir) {
            const v = fn();
            if (v) return v;
            await tidur(jeda || 300);
        }
        return null;
    }

    function setNilai(el, nilai) {
        const proto = Object.getPrototypeOf(el);
        const setter = Object.getOwnPropertyDescriptor(proto, 'value');
        setter && setter.set ? setter.set.call(el, String(nilai)) : (el.value = String(nilai));
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function setCentang(el, mau) {
        if (!el) throw new Error('Kotak centang tidak ditemukan di form submitin');
        if (el.disabled) throw new Error('Kotak centang ' + el.name + ' dinonaktifkan oleh submitin');
        if (el.checked !== !!mau) el.click();
        if (el.checked !== !!mau) throw new Error('Gagal mengubah centang ' + el.name);
    }

    /* ================================================================
     | Halaman form /services/plagiarism
     * ================================================================ */
    const diForm = () => location.pathname.replace(/\/+$/, '') === '/services/plagiarism';
    const diStatus = () => location.pathname.replace(/\/+$/, '') === '/status';

    /** Label paket Standard + sisa kuotanya. */
    function bacaPaketStandard() {
        const label = $$('#payMethods .pay-method-label[data-pm="package"]').find((l) => /standard/i.test(l.textContent));
        if (!label) return { ada: false, pesan: 'Paket Standard tidak ada di akun submitin.id.' };
        const teks = label.textContent.replace(/\s+/g, ' ');
        const sisaM = teks.match(/(\d+)\s*x\s*tersisa/i);
        const tglM = teks.match(/s\/d\s*(\d{1,2})\/(\d{1,2})\/(\d{4})/i);
        const sisa = sisaM ? parseInt(sisaM[1], 10) : null;
        let kedaluwarsa = false;
        if (tglM) {
            const batas = new Date(+tglM[3], +tglM[2] - 1, +tglM[1], 23, 59, 59);
            kedaluwarsa = batas < new Date();
        }
        const mati = label.classList.contains('pm-disabled');
        const habis = sisa === 0 || kedaluwarsa || (mati && sisa !== null && sisa < 1);
        return {
            ada: true, label, sisa, kedaluwarsa, mati, habis,
            pesan: habis
                ? ('Paket Standard ' + (kedaluwarsa ? 'sudah kedaluwarsa' : 'habis') + (tglM ? ' (s/d ' + tglM[1] + '/' + tglM[2] + '/' + tglM[3] + ')' : '') + '.')
                : ('Paket Standard: ' + (sisa === null ? '?' : sisa) + 'x tersisa'),
        };
    }

    /** Syarat sebelum mengambil tugas: login, WA terisi, paket Standard ada & bersisa. */
    function periksaSiap() {
        if (!diForm()) return { siap: false, masalah: null };
        if (!$('#orderForm') || !$('#payMethods')) return { siap: false, masalah: 'Belum login di submitin.id (form paket tidak muncul).' };
        const wa = $('#waInput');
        if (!wa || wa.value.replace(/\D/g, '').length < 9) {
            return { siap: false, masalah: 'Nomor WhatsApp akun submitin.id kosong. Isi di profil submitin (bot tidak memakai nomor customer).' };
        }
        const pkt = bacaPaketStandard();
        if (!pkt.ada) return { siap: false, masalah: pkt.pesan, kuotaHabis: true };
        if (pkt.habis) return { siap: false, masalah: pkt.pesan, kuotaHabis: true };
        return { siap: true, paket: pkt };
    }

    /**
     * Isi form sesuai tugas. Tidak mengirim apa pun — pengiriman dilakukan
     * kirimForm() sesudah semua pemeriksaan lolos.
     */
    async function isiForm(tugas, berkas) {
        const form = await tunggu(() => $('#orderForm'), 20000);
        if (!form) throw new Error('Form submitin tidak termuat');

        // Layanan: Cek Plagiasi
        const tab = $('button.svc-btn[data-svc="plagiarism"]');
        if (tab && !tab.classList.contains('is-active')) { tab.click(); await tidur(600); }

        // Turnitin V1
        const kartuV1 = $('.plat-card[data-engine="v1"]') || $('input[name="_plat_ui"][value="v1"]');
        if (kartuV1) { kartuV1.click(); await tidur(400); }
        if (($('#plagiarismEngine') || {}).value !== 'v1') throw new Error('Gagal memilih Turnitin V1');

        // Hapus berkas sisa, lalu masukkan berkas tugas
        const hapus = $('#fpRemove');
        if (hapus && hapus.offsetParent !== null) { hapus.click(); await tidur(400); }
        const input = $('#fileInput');
        if (!input) throw new Error('Kolom unggah berkas tidak ditemukan');
        const dt = new DataTransfer();
        dt.items.add(berkas);
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        const terlihat = await tunggu(() => document.body.innerText.includes(berkas.name), 15000);
        if (!terlihat) throw new Error('Berkas tidak terbaca oleh form submitin');

        // Judul: penanda + nomor order (bukan nama customer)
        const judul = $('#judulInput');
        if (judul) setNilai(judul, tugas.judul);

        // Promo kosong, bundle AI mati
        const promo = $('#promoInput');
        if (promo && promo.value) setNilai(promo, '');
        const bundle = $('#bundleOtherService');
        if (bundle && bundle.checked) { bundle.click(); await tidur(300); }

        // Filter
        const f = tugas.filter || {};
        setCentang($('input[name="excl_biblio"]', form), !!f.excl_biblio);
        setCentang($('input[name="excl_quotes"]', form), !!f.excl_quotes);

        setCentang($('#exclSourceChk'), !!f.excl_source);
        if (f.excl_source) {
            await tidur(200);
            const words = f.excl_source.tipe === 'words';
            const radio = $(words ? '#sourceTypeWords' : '#sourceTypePercent');
            if (!radio) throw new Error('Pilihan satuan Exclude Sources tidak ditemukan');
            if (!radio.checked) radio.click();
            const kolom = $(words ? '#exclSourceWords' : '#exclSourcePercent');
            kolom.disabled = false;
            setNilai(kolom, f.excl_source.nilai);
        }

        setCentang($('#exclMatchChk'), !!f.excl_match);
        if (f.excl_match) {
            await tidur(200);
            setNilai($('#exclMatchWords'), f.excl_match.kata);
        }

        // Paket Standard
        const pkt = bacaPaketStandard();
        if (!pkt.ada || pkt.habis) {
            const e = new Error(pkt.pesan); e.kuotaHabis = true; throw e;
        }
        if (pkt.label.classList.contains('pm-disabled')) throw new Error('Paket Standard tidak bisa dipilih untuk pesanan ini');
        pkt.label.click();
        await tidur(700);
        if ($('#payMethodType').value !== 'package' || $('#payPackageId').value === '0') {
            throw new Error('Gagal memilih paket Standard');
        }
        return pkt;
    }

    /** Pemeriksaan terakhir sebelum klik. Melempar galat bila ada yang janggal. */
    function periksaSebelumKirim(tugas, berkas) {
        const salah = [];
        if (($('#plagiarismEngine') || {}).value !== 'v1') salah.push('bukan Turnitin V1');
        if ($('#payMethodType').value !== 'package') salah.push('metode bayar bukan paket');
        if ($('#payPackageId').value === '0') salah.push('paket belum terpilih');
        // Pengaman uang: HANYA paket. Angka Total Bayar tidak dipakai karena
        // submitin menampilkan harga per dokumen walau dibayar dengan paket.
        const pkt = bacaPaketStandard();
        if (!pkt.ada || !pkt.label.classList.contains('is-selected') || $('#payPackageId').value !== (pkt.label.getAttribute('data-pkg-id') || '')) {
            salah.push('yang terpilih bukan paket Standard');
        }
        const tombol = $('#payBtn');
        if (!tombol || !/gunakan\s+paket/i.test(tombol.textContent)) salah.push('tombol bukan "Gunakan Paket" (bot tidak membayar dengan QRIS/Saldo)');
        if (!document.body.innerText.includes(berkas.name)) salah.push('berkas tugas tidak terlihat di form');
        if (salah.length) throw new Error('Dibatalkan sebelum kirim: ' + salah.join('; ') + '. Tidak ada yang dikirim ke submitin.');
        return tombol;
    }

    async function kerjakanForm(st) {
        const tugas = st.tugas;
        log('Mengerjakan ' + tugas.order_number + ' (' + tugas.penanda + ')');

        let blob;
        try {
            blob = await api('GET', '/tugas/' + tugas.id + '/berkas', null, 'blob');
        } catch (e) {
            return laporGagal(tugas.id, 'Tidak bisa mengunduh berkas customer dari Phoenix: ' + e.message);
        }
        const ext = (tugas.nama_berkas.split('.').pop() || '').toLowerCase();
        const mime = ext === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        const berkas = new File([blob], tugas.nama_berkas, { type: mime });

        try {
            await isiForm(tugas, berkas);
            await tidur(900);
            const tombol = periksaSebelumKirim(tugas, berkas);

            // Dicatat SEBELUM klik. Kalau tab tertutup di tengah unggah, keadaan
            // 'mengirim' mencegah form dikirim dua kali (kuota terpakai dua kali).
            setKeadaan({ tahap: 'mengirim', tugas, sejak: Date.now() });
            log('Klik "Gunakan Paket" untuk ' + tugas.order_number);
            tombol.click();

            // Tunggu pindah ke halaman status. Kalau submitin menampilkan pesan
            // galat tanpa pindah halaman, pesanan belum dibuat.
            const galat = await tunggu(() => {
                const al = $('#alertError');
                return al && al.classList.contains('show') ? (($('#alertMsg') || al).textContent || '').trim() : null;
            }, BATAS_REDIRECT_MS, 1000);
            if (galat && diForm()) {
                return laporGagal(tugas.id, 'submitin.id menolak: ' + galat);
            }
        } catch (e) {
            return laporGagal(tugas.id, e.message || String(e), !!e.kuotaHabis);
        }
    }

    /* ================================================================
     | Halaman status /status?order=SC-…
     * ================================================================ */
    function kodeDariUrl() {
        const k = new URLSearchParams(location.search).get('order');
        return k ? k.trim().toUpperCase() : null;
    }

    async function setelahKirim(st) {
        const tugas = st.tugas;
        const kode = kodeDariUrl();
        if (!diForm() && !diStatus()) {
            return laporGagal(tugas.id, 'Setelah "Gunakan Paket", submitin.id membuka ' + location.href.split('?')[0]
                + ' (bukan halaman status). Bot TIDAK membayar apa pun. Cek riwayat pesanan di submitin.id untuk ' + tugas.penanda + '.');
        }
        if (!diStatus() || !kode) {
            // Masih di form lama padahal sudah lewat batas: tidak pasti terkirim.
            if (Date.now() - st.sejak > 2 * 60 * 1000) {
                return laporGagal(tugas.id, 'Tidak pasti apakah form sudah terkirim ke submitin (halaman tertutup/termuat ulang saat mengunggah). '
                    + 'Cek riwayat pesanan di submitin.id untuk ' + tugas.penanda + ' sebelum mengerjakan manual.');
            }
            return;
        }
        const cocok = await tunggu(() => document.body.innerText.includes(tugas.penanda), 20000);
        if (!cocok) {
            return laporGagal(tugas.id, 'Halaman status ' + kode + ' tidak memuat penanda ' + tugas.penanda + '. Hasil tidak diambil agar tidak tertukar.');
        }
        try {
            await api('POST', '/tugas/' + tugas.id + '/terkirim', formData({ kode }));
        } catch (e) {
            if (e.status === 409) return laporGagal(tugas.id, 'Phoenix menolak kode ' + kode + ': ' + e.message);
            log('Kode belum tercatat, dicoba lagi: ' + e.message);
            return;
        }
        log('Terkirim: ' + tugas.order_number + ' → ' + kode);
        setKeadaan({ tahap: 'menunggu_hasil', tugas, kode, sejak: st.sejak || Date.now(), detak: Date.now() });
    }

    /** URL berkas di balik tombol "Unduh Plagiasi". */
    async function urlUnduh() {
        const el = $$('a, button').find((e) => /unduh\s+plagiasi/i.test(e.textContent));
        if (!el) return null;
        const href = el.getAttribute('href');
        if (el.tagName === 'A' && href && !/^javascript:/i.test(href) && href !== '#') return new URL(href, location.href).href;

        const dataUrl = el.getAttribute('data-url') || el.getAttribute('data-href');
        if (dataUrl) return new URL(dataUrl, location.href).href;

        const onclick = el.getAttribute('onclick') || '';
        const m = onclick.match(/['"]((?:https?:\/\/|\/)[^'"]+)['"]/);
        if (m) return new URL(m[1], location.href).href;

        // Tombol ber-skrip: tangkap URL yang dibukanya tanpa benar-benar membuka.
        const w = unsafeWindow;
        let tertangkap = null;
        const asliOpen = w.open;
        const asliKlik = w.HTMLAnchorElement.prototype.click;
        w.open = function (u) { tertangkap = u; return null; };
        w.HTMLAnchorElement.prototype.click = function () { tertangkap = this.href; };
        try { el.click(); await tidur(1500); } finally {
            w.open = asliOpen;
            w.HTMLAnchorElement.prototype.click = asliKlik;
        }
        return tertangkap ? new URL(tertangkap, location.href).href : null;
    }

    function unduhBlob(url) {
        return new Promise((resolve, reject) => {
            GM_xmlhttpRequest({
                method: 'GET', url, responseType: 'blob', timeout: 5 * 60 * 1000,
                onload: (r) => (r.status === 200 ? resolve(r.response) : reject(new Error('HTTP ' + r.status))),
                onerror: () => reject(new Error('gagal mengunduh laporan')),
                ontimeout: () => reject(new Error('unduhan laporan timeout')),
            });
        });
    }

    async function tungguHasil(st) {
        const tugas = st.tugas;
        if (!diStatus() || kodeDariUrl() !== st.kode) {
            location.href = 'https://submitin.id/status?order=' + encodeURIComponent(st.kode);
            return;
        }
        if (!document.body.innerText.includes(tugas.penanda)) {
            return laporGagal(tugas.id, 'Halaman status ' + st.kode + ' tidak memuat penanda ' + tugas.penanda + '. Hasil tidak diambil.');
        }
        if ($('.badge-failed, .badge-refund')) {
            return laporGagal(tugas.id, 'submitin.id menandai pesanan ' + st.kode + ' gagal/refund.');
        }

        const tombolAda = $$('a, button').some((e) => /unduh\s+plagiasi/i.test(e.textContent));
        if (!tombolAda) {
            if (Date.now() - st.sejak > BATAS_HASIL_MS) {
                return laporGagal(tugas.id, 'Laporan ' + st.kode + ' belum keluar setelah 60 menit.');
            }
            if (Date.now() - (st.detak || 0) > JEDA_DETAK_TUNGGU_MS) {
                api('POST', '/detak', formData({ tugas: tugas.id })).catch(() => {});
                setKeadaan(Object.assign({}, st, { detak: Date.now() }));
            }
            return 'tunggu';
        }

        try {
            const url = await urlUnduh();
            if (!url) throw new Error('Tombol "Unduh Plagiasi" ada, tapi alamat berkasnya tidak dikenali.');
            const blob = await unduhBlob(url);
            const kepala = await blob.slice(0, 5).text();
            if (kepala !== '%PDF-') throw new Error('Yang terunduh bukan PDF.');

            const persen = angka(($('.sim-value') || {}).textContent);
            const fd = new FormData();
            fd.append('kode', st.kode);
            if (!isNaN(persen)) fd.append('persen', String(persen));
            fd.append('berkas', new File([blob], 'Turnitin-' + tugas.order_number + '.pdf', { type: 'application/pdf' }));

            const r = await api('POST', '/tugas/' + tugas.id + '/hasil', fd);
            log('SELESAI ' + tugas.order_number + ' (' + (isNaN(persen) ? '?' : persen) + '%) — ' + (r.status === 'selesai' ? 'terkirim ke customer' : 'perlu dilengkapi admin'));
            siaga();
            setTimeout(() => { location.href = 'https://submitin.id/services/plagiarism'; }, 2000);
        } catch (e) {
            if (e.status === 409) {
                log('Phoenix menolak hasil: ' + e.message);
                siaga();
                return;
            }
            const coba = (st.cobaUnggah || 0) + 1;
            if (coba >= 3) return laporGagal(tugas.id, 'Gagal mengambil/mengirim laporan ' + st.kode + ': ' + e.message);
            log('Gagal kirim hasil (percobaan ' + coba + '): ' + e.message);
            setKeadaan(Object.assign({}, st, { cobaUnggah: coba }));
        }
    }

    /* ================================================================
     | Putaran utama
     * ================================================================ */
    let sibuk = false;
    let terakhirTanya = 0;
    let dimuatPada = Date.now();

    async function putaran() {
        if (sibuk) return;
        const c = cfg();
        gambarPanel();
        if (!c.nyala || !c.token) return;
        if (!pegangKunci()) return;

        sibuk = true;
        try {
            const st = keadaan();

            if (st.tahap === 'mengirim') return await setelahKirim(st);

            if (st.tahap === 'menunggu_hasil') {
                const h = await tungguHasil(st);
                if (h === 'tunggu' && Date.now() - dimuatPada > MUAT_ULANG_STATUS_MS) location.reload();
                return;
            }

            if (st.tahap === 'mengisi') {
                if (!diForm()) { location.href = 'https://submitin.id/services/plagiarism'; return; }
                // Form belum pernah dikirim untuk tugas ini, aman diulang.
                return await kerjakanForm(st);
            }

            // siaga
            if (Date.now() - terakhirTanya < JEDA_TANYA_MS) return;
            terakhirTanya = Date.now();

            if (!diForm()) { location.href = 'https://submitin.id/services/plagiarism'; return; }

            const siap = periksaSiap();
            if (!siap.siap) {
                await api('POST', '/detak', formData({ masalah: siap.masalah || '', kuota_habis: siap.kuotaHabis ? '1' : '0' }));
                if (siap.masalah) log('Menunggu: ' + siap.masalah);
                return;
            }

            const r = await api('GET', '/tugas');
            if (r.dijeda) { log('Bot dijeda dari dashboard admin.'); return; }
            if (r.kuota_habis) { log('Dashboard mencatat kuota habis — klik "Kuota sudah diisi" di dashboard.'); return; }
            if (!r.tugas) { gambarPanel('Antrean kosong · ' + siap.paket.pesan); return; }

            const t = r.tugas;
            if (t.bot_status === 'menunggu_hasil' && t.bot_kode) {
                log('Melanjutkan ' + t.order_number + ' (' + t.bot_kode + ')');
                setKeadaan({ tahap: 'menunggu_hasil', tugas: t, kode: t.bot_kode, sejak: Date.now(), detak: Date.now() });
                location.href = 'https://submitin.id/status?order=' + encodeURIComponent(t.bot_kode);
                return;
            }
            setKeadaan({ tahap: 'mengisi', tugas: t, sejak: Date.now() });
            await kerjakanForm(keadaan());
        } catch (e) {
            log('Galat: ' + (e.message || e));
        } finally {
            sibuk = false;
        }
    }

    /* ================================================================
     | Uji isi form (tanpa Phoenix, tanpa klik "Gunakan Paket")
     * ================================================================ */
    async function ujiIsiForm() {
        if (!diForm()) { alert('Buka https://submitin.id/services/plagiarism dulu.'); return; }
        const siap = periksaSiap();
        if (!siap.siap) { alert('Belum siap: ' + siap.masalah); return; }
        const tugas = {
            id: 'uji', order_number: 'INV-UJI', penanda: 'PD-UJICOBA', nama_berkas: 'PD-UJICOBA-INV-UJI.txt', judul: 'PD-UJICOBA INV-UJI',
            filter: { excl_biblio: true, excl_quotes: true, excl_source: { tipe: 'percent', nilai: 5 }, excl_match: { kata: 10 } },
        };
        const berkas = new File(['Uji isi form bot Phoenix. Berkas ini tidak dikirim.'], tugas.nama_berkas, { type: 'text/plain' });
        try {
            const pkt = await isiForm(tugas, berkas);
            await tidur(900);
            periksaSebelumKirim(tugas, berkas);
            log('UJI BERHASIL — ' + pkt.pesan + '. Tombol "Gunakan Paket" TIDAK diklik.');
            alert('Uji berhasil.\n\n' + pkt.pesan + ' (terpilih)\n\nForm sudah terisi, tombol "Gunakan Paket" TIDAK diklik. Muat ulang halaman untuk membersihkan form.');
        } catch (e) {
            log('UJI GAGAL: ' + e.message);
            alert('Uji gagal: ' + e.message);
        }
    }

    /* ================================================================
     | Panel kecil di pojok halaman
     * ================================================================ */
    let panel;
    function gambarPanel(info) {
        if (!document.body) return;
        const c = cfg();
        const st = keadaan();
        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'pd-bot-panel';
            panel.style.cssText = 'position:fixed;left:14px;bottom:14px;z-index:2147483000;width:300px;font:12px/1.45 system-ui,sans-serif;'
                + 'background:#fff;color:#1e293b;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 12px 30px rgba(15,23,42,.18);overflow:hidden';
            document.body.appendChild(panel);
            panel.addEventListener('click', (ev) => {
                const aksi = ev.target.closest('[data-aksi]');
                if (!aksi) return;
                const a = aksi.getAttribute('data-aksi');
                if (a === 'nyala') { simpan(K.nyala, !cfg().nyala); terakhirTanya = 0; gambarPanel(); }
                if (a === 'atur') aturBot();
                if (a === 'uji') ujiIsiForm();
                if (a === 'lipat') { panel.dataset.lipat = panel.dataset.lipat === '1' ? '0' : '1'; gambarPanel(); }
            });
        }
        const tahapLabel = { siaga: 'Siaga', mengisi: 'Mengisi form', mengirim: 'Mengirim', menunggu_hasil: 'Menunggu laporan' }[st.tahap] || st.tahap;
        const lipat = panel.dataset.lipat === '1';
        const logs = ambil(K.log, []).slice(0, 6).map((l) => '<div style="padding:2px 0;border-top:1px dashed #eef2f7;color:#475569">' + esc(l) + '</div>').join('');
        panel.innerHTML =
            '<div style="display:flex;align-items:center;gap:8px;padding:9px 12px;background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff">'
            + '<b style="flex:1">🤖 Bot Phoenix v' + VERSI + '</b>'
            + '<span style="background:rgba(255,255,255,.22);border-radius:99px;padding:1px 8px">' + (c.nyala ? (c.token ? 'ON' : 'Token?') : 'OFF') + '</span>'
            + '<span data-aksi="lipat" style="cursor:pointer;padding:0 4px">' + (lipat ? '▴' : '▾') + '</span></div>'
            + (lipat ? '' :
                '<div style="padding:10px 12px">'
                + '<div><b>Tahap:</b> ' + esc(tahapLabel) + (st.tugas ? ' — ' + esc(st.tugas.order_number) : '') + (st.kode ? ' (' + esc(st.kode) + ')' : '') + '</div>'
                + (info ? '<div style="color:#64748b">' + esc(info) + '</div>' : '')
                + '<div style="display:flex;gap:6px;margin:8px 0">'
                + btn('nyala', c.nyala ? 'Matikan' : 'Nyalakan', c.nyala ? '#dc2626' : '#059669')
                + btn('atur', 'Pengaturan', '#475569') + btn('uji', 'Uji isi form', '#6366f1')
                + '</div>'
                + '<div style="max-height:130px;overflow:auto">' + (logs || '<div style="color:#94a3b8">Belum ada catatan.</div>') + '</div>'
                + '<div style="color:#94a3b8;margin-top:6px">Biarkan tab ini terbuka. Jangan klik apa pun saat bot mengisi form.</div>'
                + '</div>');
    }
    const btn = (aksi, teks, warna) => '<button type="button" data-aksi="' + aksi + '" style="flex:1;border:0;border-radius:8px;padding:6px 4px;color:#fff;background:' + warna + ';cursor:pointer;font:600 11px system-ui">' + teks + '</button>';
    const esc = (s) => String(s).replace(/[&<>"]/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch]));

    function aturBot() {
        const c = cfg();
        const url = prompt('Alamat Phoenix (tanpa garis miring di akhir):', c.url);
        if (url === null) return;
        const token = prompt('Token bot (dari dashboard admin Phoenix → Bot Turnitin → Buat token).\nKosongkan untuk tetap memakai token lama.', '');
        simpan(K.url, url.trim().replace(/\/+$/, ''));
        if (token && token.trim()) simpan(K.token, token.trim());
        if (confirm('Kosongkan juga tugas yang sedang tercatat di tab ini? (Pilih Batal bila bot sedang menunggu laporan.)')) siaga();
        terakhirTanya = 0;
        log('Pengaturan disimpan.');
    }

    /* ================================================================ */
    gambarPanel();
    // Giliran diperbarui terpisah dari putaran: saat putaran sedang menunggu
    // unggahan (bisa beberapa menit), giliran tetap milik tab ini.
    setInterval(() => {
        const k = ambil(K.kunci, null);
        if (cfg().nyala && k && k.tab === ID_TAB) simpan(K.kunci, { tab: ID_TAB, at: Date.now() });
    }, 5000);
    setInterval(putaran, 5000);
    setTimeout(putaran, 1500);
})();
