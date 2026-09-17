<div>
    <style>
        /* Kulit bagian form mengikuti kartu dasbor (dsb-kartu): putih rata,
           garis tipis, ikon berwarna lembut di tengah ubinnya. Kelas lama
           (of-section/of-icon) dipertahankan agar markup & JS tidak berubah. */
        .rsc-form { --c: #7c3aed; }
        .of-section {
            background: #fff; border: 1px solid #e9edf3; border-radius: 18px;
            padding: clamp(16px, 2.2vw, 22px) !important; margin-bottom: 16px !important;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        @media (hover: hover) and (pointer: fine) {
            .of-section:hover { border-color: #dfe5ee; box-shadow: 0 10px 24px rgba(15, 23, 42, .05); }
        }
        .of-section > .d-flex:first-child {
            padding-bottom: 14px; margin-bottom: 16px !important; border-bottom: 1px solid #f1f5f9;
        }
        .of-section h5 { font-size: .98rem; font-weight: 800 !important; color: #1c1f26; line-height: 1.25; }
        .of-section h5 + small, .of-section .text-muted { font-size: .78rem; }
        .of-icon {
            --c: #7c3aed;
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem;
            background: color-mix(in srgb, var(--c) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: var(--c);
        }
        .of-icon.green { --c: #16a34a; }
        .of-icon.amber { --c: #d97706; }
        .of-icon.rose { --c: #e11d48; }
        .of-icon.blue { --c: #0284c7; }
        .of-icon i.bi, .of-icon i.bi::before { display: block; line-height: 1; }
        .of-form-label { font-weight: 700; color: #334155; font-size: .8rem; margin-bottom: 6px; }

        /* Isian Bootstrap dibuat serupa .dsb-isian */
        .rsc-form .form-control, .rsc-form .form-select {
            min-height: 42px; border: 1px solid #e9edf3; border-radius: 11px !important;
            color: #1c1f26; font-size: .88rem; font-weight: 600; box-shadow: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .rsc-form .form-control::placeholder { color: #9aa5b5; font-weight: 500; }
        .rsc-form .form-control:focus, .rsc-form .form-select:focus {
            border-color: #c4b5fd; box-shadow: 0 0 0 3px rgba(124, 58, 237, .14);
        }
        .rsc-form textarea.form-control { font-weight: 500; line-height: 1.6; }
        .rsc-form .form-control.is-invalid, .rsc-form .form-select.is-invalid { border-color: #fca5a5; }
        /* Ikon galat Bootstrap menimpa akhiran "Bulan" di dalam isian. */
        .rsc-form #jumlah_pemesanan.is-invalid { background-image: none; }

        /* ===== Tata letak: kolom utama + kolom ringkasan lengket =====
           Di layar lebar, harga/PIC/status dan tombol simpan selalu terlihat
           sambil mengisi peserta yang panjang. Di bawah 1200px kolomnya
           kembali bertumpuk sesuai urutan semula. */
        .rsc-form-tata { display: flex; flex-direction: column; gap: 0; }
        .rsc-form-utama, .rsc-form-samping { min-width: 0; }
        @media (min-width: 1200px) {
            .rsc-form-tata { flex-direction: row; align-items: flex-start; gap: 16px; }
            .rsc-form-utama { flex: 1 1 auto; }
            .rsc-form-samping { flex: 0 0 380px; }
        }
        /* Lengket hanya bila kolomnya muat setinggi layar; di laptop pendek
           kolom lengket yang lebih tinggi dari layar menyembunyikan tombol
           simpan sampai halaman habis digulung. */
        @media (min-width: 1200px) and (min-height: 980px) {
            .rsc-form-samping { position: sticky; top: 84px; }
        }
        .rsc-simpan {
            display: flex; align-items: center; justify-content: center; gap: 9px; width: 100%;
            min-height: 50px; border: 0; border-radius: 14px; cursor: pointer;
            background: #7c3aed; color: #fff; font-weight: 800; font-size: .95rem;
            box-shadow: 0 10px 22px rgba(124, 58, 237, .28);
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .rsc-simpan i.bi { font-size: 1.1rem; line-height: 1; }
        .rsc-simpan:disabled { opacity: .7; cursor: wait; }
        @media (hover: hover) and (pointer: fine) {
            .rsc-simpan:hover { background: #6d28d9; transform: translateY(-2px); }
        }
        .rsc-simpan-muat { display: none; }

        /* Pesan di atas form */
        .rsc-pesan {
            --c: #dc2626;
            display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px;
            padding: 13px 15px; border-radius: 14px;
            background: color-mix(in srgb, var(--c) 7%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 25%, #fff);
            color: #1c1f26; font-size: .86rem; scroll-margin-top: 90px;
        }
        .rsc-pesan.is-sukses { --c: #16a34a; }
        .rsc-pesan.is-peringatan { --c: #d97706; margin-bottom: 0; }
        .rsc-pesan-ikon { color: var(--c); font-size: 1.15rem; line-height: 1; padding-top: 1px; }
        .rsc-pesan-isi { flex: 1 1 auto; min-width: 0; }
        .rsc-pesan-isi ul { margin: 6px 0 0; padding-left: 18px; }
        .rsc-pesan-isi li { margin: 2px 0; overflow-wrap: anywhere; }
        .rsc-pesan-ket { display: block; margin-top: 4px; color: #6b7280; font-size: .78rem; }
        .rsc-pesan-tutup { border: 0; background: transparent; color: #6b7280; padding: 2px 4px; line-height: 1; }

        /* Bagian impor yang dilipat: garis kepala hanya saat terbuka */
        .rsc-lipat:not(.is-buka) > .d-flex:first-child { padding-bottom: 0; margin-bottom: 0 !important; border-bottom: 0; }

        /* Status: empat pilihan berwarna, bukan kotak pilih */
        .rsc-status { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .rsc-status.is-empat { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (max-width: 380px) { .rsc-status { grid-template-columns: minmax(0, 1fr); } }
        .rsc-status-opsi {
            position: relative; display: flex; align-items: center; justify-content: center; gap: 7px;
            min-height: 40px; padding: 0 10px; border-radius: 11px; cursor: pointer; margin: 0;
            border: 1px solid #e9edf3; background: #fff; color: #475569; font-size: .84rem; font-weight: 700;
            transition: border-color .15s ease, background .15s ease, color .15s ease;
        }
        .rsc-status-opsi input { position: absolute; opacity: 0; width: 1px; height: 1px; }
        .rsc-status-opsi i.bi { color: var(--c); line-height: 1; }
        .rsc-status-opsi.is-dipilih {
            background: color-mix(in srgb, var(--c) 10%, #fff); color: var(--c);
            border-color: color-mix(in srgb, var(--c) 40%, #fff);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 12%, transparent);
        }
        .rsc-status-opsi:focus-within { outline: 2px solid #7c3aed; outline-offset: 2px; }
        @media (hover: hover) and (pointer: fine) {
            .rsc-status-opsi:hover { border-color: color-mix(in srgb, var(--c) 40%, #fff); }
        }
        .rsc-status-ket { display: flex; gap: 6px; align-items: flex-start; margin: 8px 0 0; color: #6b7280; font-size: .76rem; line-height: 1.45; }

        /* Bar simpan bawah (< 1200px) */
        .rsc-bar-simpan { display: none; }
        @media (max-width: 1199.98px) {
            .rsc-simpan-kartu { display: none; }
            .rsc-form { padding-bottom: 84px; }
            .rsc-bar-simpan {
                display: flex; align-items: center; gap: 12px;
                position: fixed; left: 0; right: 0; bottom: 0; z-index: 1030;
                padding: 10px 16px calc(10px + env(safe-area-inset-bottom, 0px));
                background: rgba(255, 255, 255, .96); backdrop-filter: blur(8px);
                border-top: 1px solid #e9edf3; box-shadow: 0 -8px 24px rgba(15, 23, 42, .08);
            }
            .rsc-bar-simpan .rsc-simpan { width: auto; flex: 0 0 auto; min-height: 44px; padding: 0 20px; }
            .rsc-bar-total { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; line-height: 1.2; }
            .rsc-bar-total span { color: #6b7280; font-size: .72rem; font-weight: 700; }
            .rsc-bar-total b { color: #14532d; font-size: 1.05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        }
        .rsc-simpan-ket { color: #6b7280; font-size: .74rem; text-align: center; margin: 8px 0 0; }

        /* ===== Field read-only (otomatis dari akun) ===== */
        .rsc-ro-field {
            display: flex; align-items: center; gap: 9px; min-height: 42px; padding: 0 13px; min-width: 0;
            background: #f8fafc;
            border: 1px dashed #e2e8f0; border-radius: 11px;
        }
        .rsc-ro-field .rsc-ro-ico { color: #94a3b8; font-size: 1rem; line-height: 1; flex-shrink: 0; display: inline-flex; }
        .rsc-ro-field .rsc-ro-input { border: 0; outline: 0; background: transparent; width: 100%; color: #475569; font-weight: 500; font-size: .9rem; padding: 0; }
        .rsc-ro-field .rsc-ro-input::placeholder { color: #b6bcc6; }
        .rsc-ro-field .rsc-ro-lock { color: #cbd5e1; font-size: .78rem; line-height: 1; flex-shrink: 0; display: inline-flex; }
        .rsc-ro-field .rsc-ro-input[type="date"]::-webkit-calendar-picker-indicator { opacity: .35; }

        /* ===== Ikon di dalam input (sejajar teks) ===== */
        .rsc-ico-wrap { position: relative; }
        .rsc-ico-left {
            position: absolute; top: 50%; left: 13px; transform: translateY(-50%);
            color: #94a3b8; font-size: 1rem; line-height: 1; pointer-events: none; z-index: 4; display: inline-flex;
        }
        .rsc-ico-left.top { top: 13px; transform: none; }
        .rsc-has-ico { padding-left: 38px !important; }
        .of-peserta-table thead th { font-size: .8rem; color: #64748b; font-weight: 600; background: transparent; border-bottom: 1.5px solid #eef0f7; }
        .rsc-del-btn {
            width: 36px; height: 36px; border-radius: 10px; padding: 0;
            border: 1px solid #fee2e2; background: #fff5f5; color: #ef4444;
            display: inline-flex; align-items: center; justify-content: center; transition: all .15s ease;
        }
        .rsc-del-btn:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: translateY(-1px); }
        .rsc-del-btn i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .of-total-box {
            display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
            border-radius: 14px; padding: 14px 16px; font-weight: 800;
            background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d;
        }
        .of-total-box-label { display: inline-flex; align-items: center; gap: 8px; font-size: .8rem; color: #166534; }
        .of-total-box-label i.bi { line-height: 1; }
        .of-total-box-nilai { font-size: clamp(1.15rem, 2.4vw, 1.4rem); letter-spacing: -.01em; color: #14532d; }

        .rsc-tambah {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            min-height: 38px; padding: 0 14px; border-radius: 11px; white-space: nowrap;
            background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe;
            font-size: .82rem; font-weight: 700; transition: background .15s ease, color .15s ease;
        }
        .rsc-tambah i.bi { line-height: 1; }
        @media (hover: hover) and (pointer: fine) {
            .rsc-tambah:hover { background: #7c3aed; color: #fff; border-color: transparent; }
        }
        .rsc-akun-card {
            border: 1px solid #eef2f7; border-radius: 14px; background: #fcfcfd;
            padding: 10px 12px; margin-bottom: 8px;
        }
        .rsc-akun-card-kepala {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .rsc-akun-card-no {
            flex: 0 0 30px; width: 30px; height: 30px; border-radius: 9px;
            display: inline-flex; align-items: center; justify-content: center;
            background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-weight: 800; font-size: .8rem;
        }
        .rsc-akun-card-pilih {
            flex: 1 1 220px; width: auto; min-width: 0;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; background-color: #fff;
        }
        .rsc-akun-card-harga {
            display: inline-flex; align-items: center; gap: 6px; min-height: 30px; padding: 0 11px;
            border-radius: 999px; background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;
            font-size: .78rem; font-weight: 800; white-space: nowrap;
        }
        .rsc-akun-card-harga i.bi { line-height: 1; }
        .rsc-akun-card-kepala .rsc-del-btn { flex-shrink: 0; margin-left: auto; }
        /* Tiga kredensial selalu satu baris sejajar (tanpa label: ikon &
           tooltip sudah menjelaskan); bertumpuk hanya di HP. */
        .rsc-akun-card-isi { display: grid; gap: 8px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        @media (max-width: 575.98px) { .rsc-akun-card-isi { grid-template-columns: minmax(0, 1fr); } }
        .rsc-akun-card .rsc-ro-field { min-height: 38px; padding: 0 11px; }
        .rsc-akun-card .rsc-ro-input { font-size: .84rem; text-overflow: ellipsis; }
        .rsc-akun-card-pilih { min-height: 38px !important; }
        .rsc-akun-card-medan { min-width: 0; }
        .rsc-akun-card .rsc-ro-field { background: #fff; }
        .rsc-jumlah-peserta {
            display: inline-flex; align-items: center; gap: 7px; min-height: 32px; padding: 0 12px;
            border-radius: 999px; background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe;
            font-size: .78rem; font-weight: 700;
        }
        .rsc-rincian-akun { border: 1px solid #eef2f7; border-radius: 14px; padding: 12px 14px; background: #fcfcfd; }
        .rsc-rumus { display: flex; align-items: center; gap: 7px; color: #6b7280; font-size: .78rem; }

        /* ===== Tabs metode harga ===== */
        .rsc-price-tabs { display: flex; gap: .5rem; }
        .rsc-price-tab {
            flex: 1 1 0; text-align: center; border: 1px solid #e9edf3; background: #fff; border-radius: 12px;
            padding: 10px 12px; font-weight: 700; color: #64748b; transition: all .15s; cursor: pointer; line-height: 1.25;
        }
        .rsc-price-tab small { font-weight: 500; font-size: .72rem; color: #94a3b8; }
        .rsc-price-tab:hover { border-color: #c7d2fe; }
        .rsc-price-tab.active { border-color: #c4b5fd; background: #f5f3ff; color: #6d28d9; box-shadow: 0 0 0 3px rgba(124, 58, 237, .10); }
        .rsc-price-tab.active small { color: #7c3aed; }

        /* ===== Drop zone import Excel ===== */
        .rsc-drop {
            display: block; position: relative; cursor: pointer; text-align: center;
            border: 1.5px dashed #e2e8f0; border-radius: 16px; padding: 22px 18px;
            background: #fcfcfd;
            transition: border-color .15s, background .15s, box-shadow .15s;
        }
        .rsc-drop:hover { border-color: #fcd34d; background: #fffbeb; }
        .rsc-drop.is-loading { opacity: .7; pointer-events: none; }
        .rsc-drop-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .rsc-drop-ico {
            width: 54px; height: 54px; margin: 0 auto 10px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #d97706;
            background: #fef3c7; border: 1px solid #fde68a;
        }
        .rsc-drop-ico i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .rsc-drop-file {
            display: inline-flex; align-items: center; gap: 4px; font-size: .82rem; font-weight: 600;
            color: #059669; background: rgba(16, 185, 129, .12); padding: 4px 12px; border-radius: 999px;
        }
        .rsc-fmt { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .rsc-fmt-title { font-size: .8rem; font-weight: 700; color: #64748b; }
        .rsc-fmt-chip {
            display: inline-flex; align-items: center; gap: 6px; font-size: .78rem; color: #475569;
            background: #f4f6fb; border: 1px solid #e6e8f2; border-radius: 999px; padding: 4px 11px;
        }
        .rsc-fmt-chip b {
            display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px;
            border-radius: 6px; background: #eef0f7; color: #6d28d9; font-size: .72rem;
        }
        .rsc-fmt-note { font-size: .75rem; color: #94a3b8; }
        .rsc-btn-template {
            display: inline-flex; align-items: center; font-size: .84rem; font-weight: 700; color: #b45309;
            background: #fff; border: 1px solid #fde68a; border-radius: 12px;
            padding: 0 15px; min-height: 40px; gap: 6px; transition: all .18s ease; white-space: nowrap;
        }
        .rsc-btn-template:hover { color: #92400e; background: #fffbeb; transform: translateY(-1px); }
        .rsc-btn-template:disabled { opacity: .65; }
        .rsc-btn-template span { display: inline-flex; align-items: center; line-height: 1; }
        .rsc-btn-template i.bi { display: inline-flex; align-items: center; line-height: 1; }

        /* ===== Popup download template ===== */
        .rsc-tpl { text-align: left; }
        .rsc-tpl-hero {
            display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; margin-bottom: 18px;
        }
        .rsc-tpl-badge {
            width: 68px; height: 68px; border-radius: 20px; display: inline-flex; align-items: center; justify-content: center;
            font-size: 2rem; color: #fff; background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 12px 26px rgba(245, 158, 11, .38); position: relative;
        }
        .rsc-tpl-badge::after {
            content: ""; position: absolute; inset: 3px 3px 55% 3px; border-radius: 17px 17px 40px 40px;
            background: linear-gradient(180deg, rgba(255,255,255,.45), rgba(255,255,255,0)); pointer-events: none;
        }
        .rsc-tpl-badge i.bi {
            position: relative; z-index: 1; display: inline-flex; align-items: center; justify-content: center;
            width: 100%; height: 100%; line-height: 1;
        }
        .rsc-tpl-hero h3 { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
        .rsc-tpl-hero p { font-size: .86rem; color: #64748b; margin: 0; max-width: 320px; }
        .rsc-tpl-label { font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; margin-bottom: 8px; }
        .rsc-tpl-cols { display: flex; flex-direction: column; gap: 8px; }
        .rsc-tpl-col {
            display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 14px;
            background: linear-gradient(135deg, #ffffff, #f8fafc); border: 1px solid #eef0f7;
            box-shadow: 0 2px 6px rgba(15, 23, 42, .04);
        }
        .rsc-tpl-col-letter {
            width: 30px; height: 30px; flex: 0 0 30px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: .82rem; color: #b45309; background: linear-gradient(135deg, #fff3d6, #ffe4a3); border: 1px solid #fcd34d;
        }
        .rsc-tpl-col-name { font-weight: 700; color: #334155; font-size: .9rem; }
        .rsc-tpl-col-hint { margin-left: auto; font-size: .75rem; color: #94a3b8; }
        .rsc-tpl-note {
            display: flex; align-items: center; gap: 8px; margin-top: 16px; padding: 10px 13px; border-radius: 12px;
            background: rgba(59, 130, 246, .08); border: 1px solid rgba(59, 130, 246, .18); color: #1d4ed8; font-size: .8rem; font-weight: 600;
        }
        .swal-tpl-download {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important; color: #fff !important; border: 0 !important;
            font-weight: 700 !important; padding: 11px 26px !important; border-radius: 13px !important;
            box-shadow: 0 10px 22px rgba(245, 158, 11, .35) !important; transition: all .18s ease !important;
        }
        .swal-tpl-download:hover { transform: translateY(-1px); box-shadow: 0 14px 28px rgba(245, 158, 11, .45) !important; }
        .swal-tpl-cancel {
            background: #f1f5f9 !important; color: #475569 !important; border: 0 !important;
            font-weight: 700 !important; padding: 11px 22px !important; border-radius: 13px !important;
        }
        .swal-tpl-cancel:hover { background: #e2e8f0 !important; }

        /* ===== Picker akun (popup select searchable, seperti toko) ===== */
        .of-picker-btn { cursor: pointer; }
        /* Panahnya sudah digambar .form-select; ::after lama membuatnya ganda. */
        .of-pick-list { max-height: 46vh; overflow-y: auto; text-align: left; display: flex; flex-direction: column; gap: .4rem; padding: .2rem; }
        .of-pick-item[hidden], .of-pick-empty[hidden] { display: none !important; }
        .of-pick-item { display: flex !important; align-items: center; gap: 10px; }
        .of-pick-inisial {
            flex: 0 0 30px; width: 30px; height: 30px; border-radius: 9px;
            display: inline-flex; align-items: center; justify-content: center;
            background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; font-weight: 800; font-size: .8rem;
        }
        .of-pick-item { display: block; width: 100%; text-align: left; border: 1px solid #e6e8f2; background: #fff; border-radius: 12px; padding: .7rem .9rem; font-weight: 600; color: #1e293b; font-size: .92rem; transition: all .15s ease; }
        .of-pick-item:hover, .of-pick-item:focus-visible { border-color: #c4b5fd; background: #f5f3ff; outline: none; }
        .of-pick-empty { text-align: center; color: #94a3b8; padding: 1.5rem; font-size: .9rem; }

        /* Mobile: tombol/badge tertentu memenuhi lebar & isi (ikon+teks) di tengah.
           Desktop tetap seperti semula. */
        @media (max-width: 575.98px) {
            .rsc-m-full {
                width: 100% !important;
                display: flex !important;
                align-items: center;
                justify-content: center !important;
                text-align: center;
            }

            /* Tabel peserta ditumpuk: nomor & tombol hapus mengapit dua
               isian yang selebar layar, bukan empat kolom yang berdesakan. */
            .of-peserta-table thead { display: none; }
            .of-peserta-table tr {
                display: grid; grid-template-columns: 26px minmax(0, 1fr) 36px;
                gap: 8px 10px; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f5f9;
            }
            .of-peserta-table td { display: block; padding: 0 !important; border: 0 !important; }
            .of-peserta-table td:nth-child(1) { grid-column: 1; grid-row: 1 / span 2; font-weight: 800; color: #6d28d9; }
            .of-peserta-table td:nth-child(2) { grid-column: 2; grid-row: 1; }
            .of-peserta-table td:nth-child(3) { grid-column: 2; grid-row: 2; }
            .of-peserta-table td:nth-child(4) { grid-column: 3; grid-row: 1 / span 2; }

            /* Box Total: ikon & teks "Rp" sejajar & berada di tengah (agar kanan tak kosong). */
            .of-total-box { display: flex; align-items: center; justify-content: center; text-align: center; }
            .of-total-box i.bi { display: inline-flex; align-items: center; line-height: 1; }

            /* Rincian harga per akun: harga "Rp" tetap utuh & rata kanan, nama menyusut. */
            .rsc-akun-row { gap: 10px; }
            .rsc-akun-row > span:first-child { min-width: 0; }
            .rsc-akun-row > span:last-child { white-space: nowrap; flex-shrink: 0; text-align: right; }
        }
    </style>

    <form wire:submit="save" x-cloak class="rsc-form" id="rsc-form" data-rsc-form>
        {{-- Pesan layar & ringkasan kesalahan: form ini panjang, jadi yang
             salah dikumpulkan di atas, lalu halaman menggulung ke isian salah
             yang pertama (lihat skrip rsc-form-galat di bawah). --}}
        @if ($pesanGalat || $errors->any())
            <div class="rsc-pesan is-galat" role="alert" id="rsc-ringkasan-galat">
                <span class="rsc-pesan-ikon"><i class="bi bi-exclamation-octagon-fill"></i></span>
                <div class="rsc-pesan-isi">
                    <strong>{{ $pesanGalat ? $pesanGalat : 'Belum bisa disimpan — periksa '.count($errors->all()).' isian berikut:' }}</strong>
                    @if ($errors->any())
                        <ul>
                            @foreach (collect($errors->all())->unique()->take(6) as $galat)
                                <li>{{ $galat }}</li>
                            @endforeach
                            @if (count(array_unique($errors->all())) > 6)
                                <li>… dan {{ count(array_unique($errors->all())) - 6 }} lainnya</li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        @endif
        @if ($pesanSukses && ! $errors->any())
            <div class="rsc-pesan is-sukses" role="status">
                <span class="rsc-pesan-ikon"><i class="bi bi-check-circle-fill"></i></span>
                <div class="rsc-pesan-isi"><strong>{{ $pesanSukses }}</strong></div>
                <button type="button" class="rsc-pesan-tutup" wire:click="$set('pesanSukses', null)" aria-label="Tutup pesan"><i class="bi bi-x-lg"></i></button>
            </div>
        @endif

        <div class="rsc-form-tata">
        <div class="rsc-form-utama">
        {{-- ================== Import Excel (create & edit) ==================
             Dilipat bawaan: impor opsional, dan kotak unggah besar tidak perlu
             memenuhi layar pertama. Terbuka sendiri bila ada galat berkas. --}}
        <div class="of-section p-4 mb-4 rsc-lipat" x-data="{ buka: @js($errors->has('file_excel')) }" :class="{ 'is-buka': buka }">
            <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                <span class="of-icon amber"><i class="bi bi-file-earmark-spreadsheet-fill"></i></span>
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-0">{{ $mode === 'edit' ? 'Tambah Peserta dari Excel' : 'Import Data Peserta' }}</h5>
                    <small class="text-muted">
                        @if($mode === 'edit')
                            Peserta dari file akan <b>ditambahkan</b> — data peserta yang sudah ada tetap aman
                        @else
                            Opsional — unduh template, isi, lalu unggah kembali
                        @endif
                    </small>
                </div>
                <button type="button" class="rsc-tambah rsc-m-full" x-on:click="buka = ! buka" x-bind:aria-expanded="buka ? 'true' : 'false'" aria-controls="rsc-impor-isi">
                    <i class="bi" x-bind:class="buka ? 'bi-chevron-up' : 'bi-upload'"></i>
                    <span x-text="buka ? 'Tutup' : 'Impor dari Excel'"></span>
                </button>
            </div>

            <div id="rsc-impor-isi" x-show="buka" x-transition.opacity>
            <div class="d-flex justify-content-end mb-2">
                <button type="button" onclick="rscTemplatePopup(this)" class="btn rsc-btn-template rsc-m-full">
                    <span><i class="bi bi-download me-1"></i>Download Template</span>
                </button>
            </div>

            <label class="rsc-drop" wire:loading.class="is-loading" wire:target="file_excel">
                <input type="file" wire:model="file_excel" class="rsc-drop-input" accept=".xlsx,.xls,.csv">
                <span class="rsc-drop-ico"><i class="bi bi-cloud-arrow-up"></i></span>
                <div class="fw-semibold text-dark">{{ $mode === 'edit' ? 'Klik untuk menambah peserta dari file Excel' : 'Klik untuk memilih file Excel' }}</div>
                <div class="text-muted small">Format didukung: .xlsx · .xls · .csv (maks 2 MB)</div>
                <div wire:loading wire:target="file_excel" class="text-warning fw-semibold small mt-2">
                    <span class="spinner-border spinner-border-sm me-1"></span> Memuat &amp; memproses...
                </div>
                @if($file_excel && is_object($file_excel))
                <div class="rsc-drop-file mt-2" wire:loading.remove wire:target="file_excel">
                    <i class="bi bi-file-earmark-check-fill"></i>{{ $file_excel->getClientOriginalName() }}
                </div>
                @endif
            </label>
            @error('file_excel')<div class="text-danger small mt-2">{{ $message }}</div>@enderror

            <div class="rsc-fmt mt-3">
                <span class="rsc-fmt-title">Format kolom:</span>
                <span class="rsc-fmt-chip"><b>A</b> Nama Camp</span>
                <span class="rsc-fmt-chip"><b>B</b> Batch Camp</span>
                <span class="rsc-fmt-chip"><b>C</b> Nama Pembeli</span>
                <span class="rsc-fmt-chip"><b>D</b> No Telp</span>
                <span class="rsc-fmt-note"><i class="bi bi-info-circle me-1"></i>
                    @if($mode === 'edit')
                        Baris 1 = header (diabaikan) · hanya kolom <b>C &amp; D</b> yang dipakai
                    @else
                        Baris 1 = header (diabaikan)
                    @endif
                </span>
            </div>
            </div>{{-- /#rsc-impor-isi --}}
        </div>

        {{-- ================== Data Kategori ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="of-icon"><i class="bi bi-folder2-open"></i></span>
                <h5 class="fw-bold mb-0">Data Kategori</h5>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nama_camp" class="of-form-label d-block">Nama Kategori <span class="text-danger">*</span></label>
                    <div class="rsc-ico-wrap">
                        <span class="rsc-ico-left"><i class="bi bi-tag"></i></span>
                        <input type="text" wire:model="nama_camp" id="nama_camp"
                            class="form-control rsc-has-ico @error('nama_camp') is-invalid @enderror" placeholder="contoh: Scopus Camp Yogyakarta">
                    </div>
                    @error('nama_camp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="batch_camp" class="of-form-label d-block">Batch <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary fw-semibold"
                            style="pointer-events:none; z-index:5;">#</span>
                        <input type="number" wire:model="batch_camp" id="batch_camp" style="padding-left: 30px;"
                            class="form-control @error('batch_camp') is-invalid @enderror" placeholder="contoh: 3">
                    </div>
                    @error('batch_camp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="tanggal_mulai_camp" class="of-form-label d-block">Tanggal Mulai <span class="text-danger">*</span></label>
                    <div class="rsc-ico-wrap">
                        <span class="rsc-ico-left"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" wire:model="tanggal_mulai_camp" id="tanggal_mulai_camp"
                            class="form-control rsc-has-ico @error('tanggal_mulai_camp') is-invalid @enderror">
                    </div>
                    @error('tanggal_mulai_camp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="tanggal_akhir_camp" class="of-form-label d-block">Tanggal Berakhir <span class="text-danger">*</span></label>
                    <div class="rsc-ico-wrap">
                        <span class="rsc-ico-left"><i class="bi bi-calendar-check"></i></span>
                        <input type="date" wire:model="tanggal_akhir_camp" id="tanggal_akhir_camp"
                            class="form-control rsc-has-ico @error('tanggal_akhir_camp') is-invalid @enderror">
                    </div>
                    @error('tanggal_akhir_camp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- ================== Data Akun ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="of-icon green"><i class="bi bi-person-badge-fill"></i></span>
                <div>
                    <h5 class="fw-bold mb-0">Data Akun Utama</h5>
                    <small class="text-muted">Kredensial &amp; harga diambil otomatis dari Data Akun{{ $akun ? ' · harga Rp '.number_format($this->hargaUtama(), 0, ',', '.').'/bulan' : '' }}</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="of-form-label d-block">Pilih Akun <span class="text-danger">*</span></label>
                    @php $selAkun = $akuns->firstWhere('id', (string) $akun); @endphp
                    <button type="button" onclick="rscAkunPicker(this)"
                        class="form-select text-start of-picker-btn @error('akun') is-invalid @enderror">
                        @if($selAkun)
                        <span class="text-dark">{{ $selAkun->nama_akun }}</span>
                        @else
                        <span class="text-muted">-- Pilih Akun --</span>
                        @endif
                    </button>
                    @error('akun')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="of-form-label d-block">Username</label>
                    <div class="rsc-ro-field">
                        <i class="bi bi-person rsc-ro-ico"></i>
                        <input type="text" wire:model="username" class="rsc-ro-input" placeholder="—" readonly>
                        <i class="bi bi-lock-fill rsc-ro-lock" title="Otomatis dari akun"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="of-form-label d-block">Password</label>
                    <div class="rsc-ro-field">
                        <i class="bi bi-key rsc-ro-ico"></i>
                        <input type="text" wire:model="password" class="rsc-ro-input" placeholder="—" readonly>
                        <i class="bi bi-lock-fill rsc-ro-lock" title="Otomatis dari akun"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="of-form-label d-block">Link Akses</label>
                    <div class="rsc-ro-field">
                        <i class="bi bi-link-45deg rsc-ro-ico"></i>
                        <input type="text" wire:model="link_akses" class="rsc-ro-input" placeholder="—" readonly>
                        <i class="bi bi-lock-fill rsc-ro-lock" title="Otomatis dari akun"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== Akun Tambahan (kredensial saja) ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-3">
                    <span class="of-icon blue"><i class="bi bi-collection-fill"></i></span>
                    <div>
                        <h5 class="fw-bold mb-0">Akun Tambahan</h5>
                        <small class="text-muted">Kredensial saja (mis. Grammarly, DeepL) — tidak memengaruhi harga</small>
                    </div>
                </div>
                <button type="button" wire:click="addAkunTambahan"
                    class="rsc-tambah rsc-m-full">
                    <i class="bi bi-plus-circle"></i> Tambah Akun
                </button>
            </div>

            @forelse($akunTambahan as $tmpId => $a)
            {{-- Satu kartu per akun: baris kepala (nomor, pilihan akun, harga,
                 hapus) lalu tiga kredensial yang masing-masing mendapat ruang
                 sendiri. Dulu empat kolom sejajar membuat nama akun patah dua
                 baris dan username/link terpotong. --}}
            <div class="rsc-akun-card" wire:key="akt-{{ $tmpId }}">
                <div class="rsc-akun-card-kepala">
                    <span class="rsc-akun-card-no">{{ $loop->iteration }}</span>
                    <button type="button" onclick="rscAkunTambahanPicker(this, '{{ $tmpId }}')"
                        class="form-select text-start of-picker-btn rsc-akun-card-pilih" aria-label="Pilih akun tambahan {{ $loop->iteration }}">
                        @if($a['akun_id'])<span class="text-dark">{{ $a['nama_akun'] }}</span>
                        @else<span class="text-muted">-- Pilih Akun --</span>@endif
                    </button>
                    @if($metode_harga==='per_akun' && $a['akun_id'])
                    <span class="rsc-akun-card-harga"><i class="bi bi-tag"></i>Rp {{ number_format($a['harga'] ?? 0, 0, ',', '.') }}</span>
                    @endif
                    <button type="button" class="rsc-del-btn" title="Hapus akun" aria-label="Hapus akun tambahan {{ $loop->iteration }}"
                        wire:click="removeAkunTambahan('{{ $tmpId }}')"><i class="bi bi-trash"></i></button>
                </div>
                <div class="rsc-akun-card-isi">
                    <div class="rsc-akun-card-medan">
                        <div class="rsc-ro-field" title="Username: {{ $a['username'] }}">
                            <i class="bi bi-person rsc-ro-ico"></i>
                            <input type="text" class="rsc-ro-input" value="{{ $a['username'] }}" placeholder="Username" aria-label="Username" readonly>
                        </div>
                    </div>
                    <div class="rsc-akun-card-medan">
                        <div class="rsc-ro-field" title="Password: {{ $a['password'] }}">
                            <i class="bi bi-key rsc-ro-ico"></i>
                            <input type="text" class="rsc-ro-input" value="{{ $a['password'] }}" placeholder="Password" aria-label="Password" readonly>
                        </div>
                    </div>
                    <div class="rsc-akun-card-medan">
                        <div class="rsc-ro-field" title="Link Akses: {{ $a['link_akses'] }}">
                            <i class="bi bi-link-45deg rsc-ro-ico"></i>
                            <input type="text" class="rsc-ro-input" value="{{ $a['link_akses'] }}" placeholder="Link Akses" aria-label="Link Akses" readonly>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Belum ada akun tambahan. Klik <b>Tambah Akun</b> bila batch ini memakai lebih dari satu akun.
            </p>
            @endforelse
        </div>

        {{-- ================== Data Pembeli ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-3">
                    <span class="of-icon rose"><i class="bi bi-person-vcard-fill"></i></span>
                    <h5 class="fw-bold mb-0">Data Pembeli</h5>
                </div>
                <button type="button" wire:click="addPeserta"
                    class="rsc-tambah rsc-m-full">
                    <i class="bi bi-plus-circle"></i> Tambah Peserta
                </button>
            </div>

            <div class="table-responsive" wire:loading.class="opacity-50" wire:target="removePeserta">
                <table class="table align-middle of-peserta-table mb-0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="45%">Nama Pembeli <span class="text-danger">*</span></th>
                            <th width="40%">No. Telepon <span class="text-danger">*</span></th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($peserta as $tmpId => $p)
                        <tr wire:key="row-{{ $tmpId }}">
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <div class="rsc-ico-wrap">
                                    <span class="rsc-ico-left"><i class="bi bi-person"></i></span>
                                    <input type="text" wire:model.defer="peserta.{{ $tmpId }}.nama_pembeli"
                                        class="form-control rsc-has-ico @error('peserta.'.$tmpId.'.nama_pembeli') is-invalid @enderror"
                                        placeholder="Nama Peserta">
                                </div>
                                @error('peserta.'.$tmpId.'.nama_pembeli')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </td>
                            <td>
                                <div class="rsc-ico-wrap">
                                    <span class="rsc-ico-left"><i class="bi bi-telephone"></i></span>
                                    <input type="text" wire:model.defer="peserta.{{ $tmpId }}.telp_pembeli"
                                        class="form-control rsc-has-ico @error('peserta.'.$tmpId.'.telp_pembeli') is-invalid @enderror"
                                        placeholder="0812..." inputmode="tel" onkeypress="filterPhoneNumberInput(event)">
                                </div>
                                @error('peserta.'.$tmpId.'.telp_pembeli')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </td>
                            <td class="text-center">
                                @if(count($peserta) > 1)
                                <button type="button" title="Hapus peserta" class="rsc-del-btn"
                                    x-on:click="let y = window.scrollY; $wire.removePeserta('{{ $tmpId }}').then(() => requestAnimationFrame(() => window.scrollTo(0, y)))">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @php $telpGanda = $this->telpGanda(); @endphp
            @if ($telpGanda)
                <div class="rsc-pesan is-peringatan mt-3" role="status">
                    <span class="rsc-pesan-ikon"><i class="bi bi-exclamation-triangle-fill"></i></span>
                    <div class="rsc-pesan-isi">
                        <strong>Nomor telepon yang sama dipakai beberapa peserta</strong>
                        <ul>
                            @foreach ($telpGanda as $g)
                                <li>{{ $g['telp'] }} — peserta nomor {{ implode(', ', $g['nomor']) }}</li>
                            @endforeach
                        </ul>
                        <span class="rsc-pesan-ket">Tetap bisa disimpan. Periksa lagi bila ini hasil impor ganda.</span>
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-end mt-3">
                <span class="rsc-jumlah-peserta rsc-m-full">
                    <i class="bi bi-people-fill"></i> Total peserta: {{ count($peserta) }} orang
                </span>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <label for="jumlah_pemesanan" class="of-form-label d-block">Jumlah Pesanan <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="number" id="jumlah_pemesanan" wire:model.live="jumlah_pemesanan" style="padding-right: 58px;"
                            class="form-control @error('jumlah_pemesanan') is-invalid @enderror" placeholder="Durasi">
                        <span class="position-absolute top-50 end-0 translate-middle-y pe-3 text-secondary fw-semibold"
                            style="pointer-events:none; z-index:5;">Bulan</span>
                    </div>
                    @error('jumlah_pemesanan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="tanggal_pemesanan" class="of-form-label d-block">Tanggal Pemesanan <span class="text-danger">*</span></label>
                    <div class="rsc-ico-wrap">
                        <span class="rsc-ico-left"><i class="bi bi-calendar-date"></i></span>
                        <input type="date" id="tanggal_pemesanan" wire:model.live="tanggal_pemesanan"
                            class="form-control rsc-has-ico @error('tanggal_pemesanan') is-invalid @enderror">
                    </div>
                    @error('tanggal_pemesanan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="tanggal_berakhir" class="of-form-label d-block">Tanggal Berakhir</label>
                    <div class="rsc-ro-field">
                        <i class="bi bi-calendar-check rsc-ro-ico"></i>
                        <input type="date" id="tanggal_berakhir" wire:model="tanggal_berakhir" class="rsc-ro-input" readonly>
                        <i class="bi bi-lock-fill rsc-ro-lock" title="Dihitung otomatis"></i>
                    </div>
                </div>
            </div>
        </div>

        </div>{{-- /.rsc-form-utama --}}

        <aside class="rsc-form-samping">
        {{-- ================== Harga & Status ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="of-icon amber"><i class="bi bi-sliders"></i></span>
                <div>
                    <h5 class="fw-bold mb-0">Harga &amp; Status</h5>
                    <small class="text-muted">Total dihitung otomatis</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="of-form-label d-block">Metode Perhitungan Harga</label>
                    <div class="rsc-price-tabs mb-2">
                        <button type="button" wire:click="$set('metode_harga','per_peserta')"
                            class="rsc-price-tab @if($metode_harga==='per_peserta') active @endif">
                            <i class="bi bi-people-fill me-1"></i> Per Peserta
                            <small class="d-block">harga × jumlah peserta</small>
                        </button>
                        <button type="button" wire:click="$set('metode_harga','per_akun')"
                            class="rsc-price-tab @if($metode_harga==='per_akun') active @endif">
                            <i class="bi bi-collection-fill me-1"></i> Per Akun
                            <small class="d-block">harga × jumlah akun</small>
                        </button>
                    </div>

                    @if($metode_harga==='per_akun')
                    {{-- Rincian harga tiap akun (utama + tambahan) --}}
                    <div class="rsc-rincian-akun mb-2">
                        <div class="fw-semibold small text-dark mb-2"><i class="bi bi-list-ul me-1"></i>Rincian harga per akun</div>
                        @php $selUtama = $akuns->firstWhere('id', (string) $akun); @endphp
                        @if($akun)
                        <div class="rsc-akun-row d-flex justify-content-between align-items-center small py-1 border-bottom">
                            <span><i class="bi bi-star-fill text-warning me-1"></i>{{ $selUtama->nama_akun ?? 'Akun Utama' }}
                                <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill ms-1" style="font-size:.6rem;">UTAMA</span></span>
                            <span class="fw-semibold">Rp {{ number_format($this->hargaUtama(), 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @foreach($akunTambahan as $a)
                        @if(!empty($a['akun_id']))
                        <div class="rsc-akun-row d-flex justify-content-between align-items-center small py-1 border-bottom">
                            <span><i class="bi bi-collection me-1 text-primary"></i>{{ $a['nama_akun'] }}</span>
                            <span class="fw-semibold">Rp {{ number_format($a['harga'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @endforeach
                        <div class="rsc-akun-row d-flex justify-content-between align-items-center pt-2 fw-bold text-success">
                            <span>Jumlah harga {{ $this->jumlahAkun() }} akun</span>
                            <span>Rp {{ number_format($this->sumHargaAkun(), 0, ',', '.') }}</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.75rem;">× {{ (int)($jumlah_pemesanan ?: 0) }} bulan</div>
                    </div>
                    @else
                    <div class="rsc-rumus mb-2">
                        <i class="bi bi-calculator"></i>{{ (int)($jumlah_pemesanan ?: 0) }} bulan × Rp {{ number_format($this->hargaUtama(), 0, ',', '.') }} × {{ count($peserta) }} peserta
                    </div>
                    @endif

                    <div class="of-total-box">
                        <span class="of-total-box-label"><i class="bi bi-cash-stack"></i>Total harga</span>
                        <span class="of-total-box-nilai">Rp {{ number_format($this->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="col-md-6 col-xl-12">
                    <label class="of-form-label d-block">Pilih PIC <span class="text-danger">*</span></label>
                    @php $selPic = $users->firstWhere('id', (int) $pic); @endphp
                    <button type="button" onclick="rscPicPicker(this)"
                        class="form-select text-start of-picker-btn @error('pic') is-invalid @enderror">
                        @if($selPic)
                        <span class="text-dark">{{ $selPic->name }}</span>
                        @else
                        <span class="text-muted">-- Pilih PIC --</span>
                        @endif
                    </button>
                    @error('pic')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 col-xl-12">
                    <span class="of-form-label d-block" id="rsc-status-label">Status <span class="text-danger">*</span></span>
                    @php
                        $gayaStatus = [
                            'baru' => ['Baru', 'bi-stars', '#16a34a'],
                            'pengganti' => ['Pengganti', 'bi-arrow-left-right', '#d97706'],
                            'habis' => ['Habis', 'bi-hourglass-bottom', '#e11d48'],
                            'perpanjang' => ['Perpanjang', 'bi-arrow-repeat', '#0284c7'],
                        ];
                        $opsiStatus = $this->pilihanStatus();
                    @endphp
                    <div class="rsc-status {{ count($opsiStatus) > 3 ? 'is-empat' : '' }}" role="radiogroup" aria-labelledby="rsc-status-label">
                        @foreach ($opsiStatus as $nilai)
                            @php [$label, $ikon, $warna] = $gayaStatus[$nilai]; @endphp
                            <label class="rsc-status-opsi {{ $status === $nilai ? 'is-dipilih' : '' }}" style="--c: {{ $warna }}">
                                <input type="radio" name="status" value="{{ $nilai }}" wire:model.live="status">
                                <i class="bi {{ $ikon }}"></i><span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    {{-- Semua status tercatat di Cash Flow: status menggambarkan
                         masa akun, bukan pembayaran. --}}
                    <p class="rsc-status-ket"><i class="bi bi-info-circle"></i><span>Semua status tetap tercatat di Cash Flow. Perpanjangan akun dibeli pelanggan lewat <b>Pemesanan Toko</b>.</span></p>
                    @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="deskripsi" class="of-form-label d-block">Deskripsi</label>
                    <div class="rsc-ico-wrap">
                        <span class="rsc-ico-left top"><i class="bi bi-card-text"></i></span>
                        <textarea id="deskripsi" wire:model="deskripsi" rows="3"
                            class="form-control rsc-has-ico @error('deskripsi') is-invalid @enderror" placeholder="Masukkan deskripsi (opsional)"></textarea>
                    </div>
                    @error('deskripsi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="of-section rsc-simpan-kartu">
            <button type="submit" class="rsc-simpan" wire:loading.attr="disabled" wire:target="save">
                <i class="bi bi-check2-circle" wire:loading.class="rsc-simpan-muat" wire:target="save"></i>
                <span wire:loading.remove wire:target="save">{{ $this->mode === 'create' ? 'Simpan Batch' : 'Simpan Perubahan' }}</span>
                <span wire:loading wire:target="save">Menyimpan…</span>
            </button>
            <p class="rsc-simpan-ket">Kolom bertanda <span class="text-danger">*</span> wajib diisi</p>
        </div>
        </aside>
        </div>{{-- /.rsc-form-tata --}}

        {{-- Bar simpan di layar < 1200px: total & tombol selalu terjangkau
             tanpa menggulung ke ujung form yang panjang. --}}
        <div class="rsc-bar-simpan">
            <div class="rsc-bar-total">
                <span>Total harga</span>
                <b>Rp {{ number_format($this->grand_total, 0, ',', '.') }}</b>
            </div>
            <button type="submit" class="rsc-simpan" wire:loading.attr="disabled" wire:target="save">
                <i class="bi bi-check2-circle" wire:loading.class="rsc-simpan-muat" wire:target="save"></i>
                <span wire:loading.remove wire:target="save">{{ $this->mode === 'create' ? 'Simpan' : 'Simpan Perubahan' }}</span>
                <span wire:loading wire:target="save">Menyimpan…</span>
            </button>
        </div>
    </form>
</div>

<!--================== FORMAT TELP ==================-->
<script>
    function formatPhoneNumber(input) {
        if (input.value.startsWith('0')) {
            input.value = '+62' + input.value.substring(1);
        }
    }

    function validatePhoneNumber(input) {
        const regex = /^\+62[0-9]{8,13}$/;
        const errorDiv = document.getElementById('telp_error');
        if (!regex.test(input.value)) {
            input.classList.add('is-invalid');
            if (errorDiv) errorDiv.textContent = 'Nomor telepon harus diawali +62 dan berisi 9–14 digit angka.';
        } else {
            input.classList.remove('is-invalid');
            if (errorDiv) errorDiv.textContent = '';
        }
    }

    // Batasi input agar hanya angka + simbol "+" (hanya di awal)
    function filterPhoneNumberInput(event) {
        const char = String.fromCharCode(event.which);
        if ([8, 37, 39, 46].includes(event.keyCode)) {
            return;
        }
        if (event.target.value.length === 0 && char === '+') {
            return;
        }
        if (!/[0-9]/.test(char)) {
            event.preventDefault();
        }
    }
</script>
<!--================== END ==================-->

<!--================== PICKER AKUN & PIC (popup searchable) ==================-->
<script>
    {{-- Hanya akun status active yang boleh dipilih (utama & tambahan). --}}
    window.__rscAkuns = {!! json_encode($akunsAktif->map(fn ($a) => ['id' => (string) $a->id, 'name' => $a->nama_akun])->values(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
    window.__rscUsers = {!! json_encode($users->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])->values(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};

    if (!window.__rscPickerBound) {
        window.__rscPickerBound = true;

        const rscGlossy = {
            background: 'rgba(255, 255, 255, 0.92)',
            backdrop: 'rgba(139, 92, 246, 0.15)',
            customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
            buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem',
        };

        // Picker generik (dipakai akun & PIC).
        //
        // Jendelanya memakai cangkang yang sama dengan jendela lain di lemon
        // (ts-modal + dsb-jendela, partials/dasbor-gaya), bukan popup
        // SweetAlert: gaya seragam, dan pengunci gulir halaman ikut bekerja
        // karena pengamatnya mencari .ts-modal [role="dialog"].
        const esc = (t) => String(t).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        window.__rscPicker = function (title, placeholder, items, emptyText, onPick) {
            document.getElementById('rsc-pilih-jendela')?.remove();

            const rows = items.length
                ? items.map(it => `<button type="button" class="of-pick-item" data-id="${esc(it.id)}" data-search="${esc(it.name.toLowerCase())}">
                        <span class="of-pick-inisial">${esc(it.name.charAt(0).toUpperCase())}</span><span>${esc(it.name)}</span></button>`).join('')
                : '';

            const wadah = document.createElement('div');
            wadah.id = 'rsc-pilih-jendela';
            wadah.innerHTML = `
                <div class="ts-modal-back" data-tutup-pilih></div>
                <div class="ts-modal" data-tutup-pilih-luar>
                    <div class="ts-modal-card dsb is-datar" role="dialog" aria-modal="true" aria-label="${esc(title)}" tabindex="-1" style="max-width: 460px;">
                        <div class="dsb-jendela-kepala">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-search"></i></span>
                            <span class="dsb-jendela-teks">
                                <h5 class="dsb-jendela-judul">${esc(title)}</h5>
                                <span class="dsb-kartu-sub">${items.length} pilihan</span>
                            </span>
                            <button type="button" class="dsb-jendela-tutup" data-tutup-pilih title="Tutup"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div class="dsb-jendela-isi">
                            <div class="dsb-cari" style="margin-bottom: 12px;">
                                <i class="bi bi-search"></i>
                                <input type="search" class="dsb-isian" placeholder="${esc(placeholder)}" autocomplete="off">
                            </div>
                            <div class="of-pick-list">${rows}</div>
                            <div class="of-pick-empty" ${items.length ? 'hidden' : ''}>${esc(items.length ? 'Tidak ada yang cocok' : emptyText)}</div>
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(wadah);

            const tutup = () => { wadah.remove(); document.removeEventListener('keydown', tombol); };
            const tombol = (e) => { if (e.key === 'Escape') { e.preventDefault(); tutup(); } };
            document.addEventListener('keydown', tombol);
            document.addEventListener('livewire:navigating', tutup, { once: true });

            wadah.querySelectorAll('[data-tutup-pilih]').forEach(el => el.addEventListener('click', tutup));
            wadah.querySelector('[data-tutup-pilih-luar]').addEventListener('click', (e) => { if (e.target === e.currentTarget) tutup(); });

            const cari = wadah.querySelector('input[type="search"]');
            const kosong = wadah.querySelector('.of-pick-empty');
            const tombolPilih = [...wadah.querySelectorAll('.of-pick-item')];
            cari.addEventListener('input', () => {
                const q = cari.value.toLowerCase().trim();
                let ada = 0;
                tombolPilih.forEach(b => { const cocok = b.dataset.search.includes(q); b.hidden = !cocok; ada += cocok ? 1 : 0; });
                if (items.length) kosong.hidden = ada > 0;
            });
            // Enter memilih hasil pertama yang terlihat.
            cari.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                tombolPilih.find(b => !b.hidden)?.click();
            });
            tombolPilih.forEach(b => b.addEventListener('click', () => {
                onPick(b.dataset.id);
                window.__rscTandaiUbah?.();
                tutup();
            }));
            setTimeout(() => cari.focus(), 60);
        };

        window.rscAkunPicker = function (btn) {
            const comp = btn.closest('[wire\\:id]'); if (!comp) return;
            const cid = comp.getAttribute('wire:id');
            window.__rscPicker('Pilih Akun', 'Ketik untuk mencari akun...', window.__rscAkuns || [], 'Tidak ada akun',
                (id) => Livewire.find(cid).set('akun', id));
        };

        window.rscPicPicker = function (btn) {
            const comp = btn.closest('[wire\\:id]'); if (!comp) return;
            const cid = comp.getAttribute('wire:id');
            window.__rscPicker('Pilih PIC', 'Ketik untuk mencari PIC...', window.__rscUsers || [], 'Tidak ada data PIC',
                (id) => Livewire.find(cid).set('pic', id));
        };

        // Picker untuk baris akun tambahan (kredensial saja)
        window.rscAkunTambahanPicker = function (btn, tmpId) {
            const comp = btn.closest('[wire\\:id]'); if (!comp) return;
            const cid = comp.getAttribute('wire:id');
            window.__rscPicker('Pilih Akun', 'Ketik untuk mencari akun...', window.__rscAkuns || [], 'Tidak ada akun',
                (id) => Livewire.find(cid).call('setAkunTambahan', tmpId, id));
        };

        // Popup glossy untuk download template Excel
        window.rscTemplatePopup = function (btn) {
            if (typeof Swal === 'undefined') return;
            const comp = btn.closest('[wire\\:id]'); if (!comp) return;
            const cid = comp.getAttribute('wire:id');

            const cols = [
                { l: 'A', name: 'Nama Camp', hint: 'wajib' },
                { l: 'B', name: 'Batch Camp', hint: 'angka' },
                { l: 'C', name: 'Nama Pembeli', hint: 'wajib' },
                { l: 'D', name: 'No Telp', hint: 'wajib' },
            ];
            const colsHtml = cols.map(c => `
                <div class="rsc-tpl-col">
                    <span class="rsc-tpl-col-letter">${c.l}</span>
                    <span class="rsc-tpl-col-name">${c.name}</span>
                    <span class="rsc-tpl-col-hint">${c.hint}</span>
                </div>`).join('');

            Swal.fire({
                html: `
                    <div class="rsc-tpl">
                        <div class="rsc-tpl-hero">
                            <span class="rsc-tpl-badge"><i class="bi bi-file-earmark-arrow-down"></i></span>
                            <h3>Download Template Excel</h3>
                            <p>Unduh template, isi data peserta, lalu unggah kembali pada kotak import di bawah.</p>
                        </div>
                        <div class="rsc-tpl-label">Struktur Kolom</div>
                        <div class="rsc-tpl-cols">${colsHtml}</div>
                        <div class="rsc-tpl-note">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>Baris pertama adalah header — jangan dihapus atau diubah.</span>
                        </div>
                    </div>`,
                background: 'rgba(255, 255, 255, 0.94)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                width: 460,
                padding: '1.6rem 1.4rem 1.4rem',
                buttonsStyling: false,
                showCancelButton: true,
                showCloseButton: true,
                reverseButtons: true,
                confirmButtonText: '<i class="bi bi-download me-1"></i> Download Sekarang',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'swal-glossy-popup rounded-4 shadow-lg border-0',
                    confirmButton: 'swal-tpl-download',
                    cancelButton: 'swal-tpl-cancel',
                    actions: 'gap-2 mt-3',
                },
            }).then((res) => {
                if (res.isConfirmed) {
                    Livewire.find(cid).call('downloadTemplate');
                    if (typeof window.fireGlossySwal === 'function') {
                        window.fireGlossySwal('Sedang Diunduh', 'Template Excel sedang disiapkan…', 'success');
                    }
                }
            });
        };
    }
</script>
<!--================== END PICKER AKUN & PIC ==================-->

<!--================== PERUBAHAN BELUM DISIMPAN & GULIR KE GALAT ==================-->
<script>
    if (!window.__rscFormJaga) {
        window.__rscFormJaga = true;
        let berubah = false;
        const adaForm = () => document.querySelector('[data-rsc-form]');

        window.__rscTandaiUbah = () => { if (adaForm()) berubah = true; };

        // Hanya masukan DARI PENGGUNA di dalam form yang dihitung.
        document.addEventListener('input', (e) => { if (e.target.closest?.('[data-rsc-form]')) berubah = true; }, true);
        document.addEventListener('change', (e) => { if (e.target.closest?.('[data-rsc-form]')) berubah = true; }, true);
        document.addEventListener('click', (e) => {
            const t = e.target.closest?.('[data-rsc-form] [wire\\:click]');
            if (t) berubah = true;
        }, true);
        // Dikirim = tidak perlu ditanya lagi saat dialihkan sesudah simpan.
        // Bila simpan ditolak, penanda dipasang lagi oleh rsc-form-galat.
        document.addEventListener('submit', (e) => { if (e.target.matches?.('[data-rsc-form]')) berubah = false; }, true);

        // Menutup/memuat ulang tab.
        window.addEventListener('beforeunload', (e) => {
            if (!berubah || !adaForm()) return;
            e.preventDefault();
            e.returnValue = '';
        });

        // Berpindah halaman lewat wire:navigate (menu samping, tombol kembali).
        document.addEventListener('livewire:navigate', (e) => {
            if (!berubah || !adaForm()) return;
            if (!window.confirm('Perubahan pada form belum disimpan. Tinggalkan halaman ini?')) {
                e.preventDefault();
                return;
            }
            berubah = false;
        });
        document.addEventListener('livewire:navigated', () => { berubah = false; });

        window.addEventListener('rsc-form-galat', () => {
            // Simpan ditolak: isiannya tetap belum tersimpan.
            berubah = true;
            requestAnimationFrame(() => requestAnimationFrame(() => {
                const pertama = document.querySelector('[data-rsc-form] .is-invalid, [data-rsc-form] .invalid-feedback');
                const sasaran = pertama || document.getElementById('rsc-ringkasan-galat');
                if (!sasaran) return;
                sasaran.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const isian = sasaran.matches('input, select, textarea, button') ? sasaran : sasaran.closest('div')?.querySelector('input, select, textarea, button');
                isian?.focus({ preventScroll: true });
            }));
        });
    }
</script>
<!--================== END ==================-->
