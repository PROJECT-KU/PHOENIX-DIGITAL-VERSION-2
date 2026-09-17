@section('title')
Task Saya || lemon
@stop
<div wire:poll.30s>
    <style>
        /* ===== Kartu task ===== */
        .ts-card {
            position: relative;
            border: 1px solid #eef0f7;
            border-radius: 18px;
            padding: 18px 18px 16px;
            background: linear-gradient(135deg, #ffffff, #fbfcff);
            box-shadow: 0 6px 18px rgba(108, 99, 255, .05);
            height: 100%;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            overflow: hidden;
        }

        .ts-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }

        .ts-card:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(76, 29, 149, .12); border-color: #ddd6fe; }
        .ts-card.acc-success::before { background: linear-gradient(#10b981, #059669); }
        .ts-card.acc-info::before { background: linear-gradient(#0ea5e9, #2563eb); }
        .ts-card.acc-danger::before { background: linear-gradient(#f43f5e, #e11d48); }
        .ts-card.acc-warning::before { background: linear-gradient(#f59e0b, #d97706); }
        .ts-card.acc-primary::before { background: linear-gradient(#7c3aed, #4e46e5); }
        .ts-card.acc-secondary::before { background: linear-gradient(#94a3b8, #64748b); }
        .ts-card.locked { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }

        /* ===== Deadline HARI INI: kartu ditonjolkan ===== */
        .ts-card.ts-today {
            border-color: #fbbf24;
            background: linear-gradient(135deg, #fffdf5, #fff6e5);
            box-shadow: 0 10px 26px rgba(245, 158, 11, .22);
            animation: tsTodayPulse 1.8s ease-in-out infinite;
        }
        .ts-card.ts-today:hover { border-color: #f59e0b; box-shadow: 0 16px 34px rgba(245, 158, 11, .30); }
        .ts-card.ts-today::before { background: linear-gradient(#f59e0b, #d97706) !important; width: 6px; }
        @keyframes tsTodayPulse {
            0%, 100% { box-shadow: 0 8px 22px rgba(245, 158, 11, .18); }
            50% { box-shadow: 0 12px 30px rgba(245, 158, 11, .36); }
        }
        .ts-today-ribbon {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .68rem; font-weight: 800; letter-spacing: .3px; text-transform: uppercase;
            color: #b45309; background: rgba(245, 158, 11, .16);
            padding: 3px 9px; border-radius: 999px; margin-bottom: 8px;
        }

        .ts-title { font-weight: 800; color: #1e293b; font-size: 1.02rem; line-height: 1.25; }
        .ts-meta { font-size: .8rem; color: #64748b; }

        .ts-deadchip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .76rem;
            font-weight: 700;
            padding: 5px 11px;
            border-radius: 999px;
        }

        .ts-badge { font-weight: 700; letter-spacing: .2px; }

        /* ===== Modal glossy ===== */
        .ts-modal-back { position: fixed; inset: 0; background: rgba(15, 23, 42, .5); backdrop-filter: blur(2px); z-index: 1055; }
        /* Yang menggulung hanya JENDELANYA, bukan halaman di belakangnya.

           Dulu lapisan .ts-modal sendiri yang menggulung, jadi saat jendelanya
           panjang: kepala dan tombol keputusannya ikut menghilang ke atas
           layar, dan halaman di belakangnya tetap bisa ikut tergulung —
           menutup jendela lalu mendaratkan pembacanya di tempat yang berbeda
           dari tempat ia menekan tadi. */
        .ts-modal {
            position: fixed; inset: 0; z-index: 1056;
            display: flex; align-items: center; justify-content: center;
            padding: 3vh 12px; overflow: hidden;
        }
        .ts-modal-card {
            background: #fff; border-radius: 22px; width: 100%; max-width: 560px;
            box-shadow: 0 30px 70px rgba(15, 23, 42, .32);
            /* Kolom: kepala & kaki tetap, badan yang menggulung. */
            display: flex; flex-direction: column;
            max-height: 94vh; min-height: 0; overflow: hidden;
        }
        .ts-modal-card > .dsb-jendela-kepala,
        .ts-modal-card > .dsb-jendela-kaki { flex: 0 0 auto; }
        .ts-modal-card > .dsb-jendela-isi { flex: 1 1 auto; min-height: 0; overflow-y: auto; -webkit-overflow-scrolling: touch; }

        /* Halaman di belakang dikunci selama ada jendela terbuka. Lebar bilah
           gulung diganti padding supaya isinya tidak melompat mendatar saat
           bilah itu hilang. */
        body.ts-terkunci { overflow: hidden; }

        .ts-section-lbl { font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin-bottom: 8px; }

        /* Chat bubbles */
        .ts-thread { max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding: 2px; }
        .ts-msg { display: flex; gap: 8px; max-width: 85%; }
        .ts-msg.mine { align-self: flex-end; flex-direction: row-reverse; }
        .ts-msg-av { width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; font-size: .8rem; background: linear-gradient(135deg, #94a3b8, #64748b); }
        .ts-msg.mine .ts-msg-av { background: linear-gradient(135deg, #7c3aed, #4e46e5); }
        .ts-bubble { background: #f4f6fb; border-radius: 14px; padding: 8px 12px; font-size: .86rem; color: #1e293b; }
        .ts-msg.mine .ts-bubble { background: linear-gradient(135deg, rgba(124, 58, 237, .12), rgba(78, 70, 229, .07)); }
        .ts-bubble.ts-bubble-revisi { background: linear-gradient(135deg, rgba(245, 158, 11, .16), rgba(217, 119, 6, .08)); border: 1px solid rgba(245, 158, 11, .45); }
        .ts-msg.mine .ts-bubble.ts-bubble-revisi { background: linear-gradient(135deg, rgba(245, 158, 11, .16), rgba(217, 119, 6, .08)); }
        .ts-bubble .who { font-weight: 700; font-size: .74rem; color: #475569; }
        .ts-bubble .when { font-size: .68rem; color: #94a3b8; }
        .ts-bubble.ts-bubble-pinned { box-shadow: inset 0 0 0 1px rgba(245, 158, 11, .55); }

        /* ===== Pin komentar ===== */
        .ts-pin-btn { border: none; background: transparent; color: #cbd5e1; padding: 0 2px; line-height: 1; cursor: pointer; display: inline-flex; align-items: center; transition: .15s; }
        .ts-pin-btn:hover { color: #d97706; }
        .ts-pin-btn.active { color: #d97706; }
        .ts-pinned { border: 1px solid #fde68a; background: linear-gradient(135deg, #fffbeb, #fff7ed); border-radius: 12px; padding: 8px 10px; }
        .ts-pinned-lbl { font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; color: #b45309; margin-bottom: 4px; display: inline-flex; align-items: center; }
        .ts-pinned-lbl i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .ts-pin-item { display: flex; align-items: center; gap: 6px; font-size: .8rem; color: #334155; padding: 3px 0; }
        .ts-pin-item + .ts-pin-item { border-top: 1px dashed #fde68a; }
        .ts-pin-ico { color: #d97706; font-size: .8rem; flex-shrink: 0; display: inline-flex; align-items: center; line-height: 1; }
        .ts-pin-who { font-weight: 700; flex-shrink: 0; }
        .ts-pin-body { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1 1 auto; }
        .ts-pin-x { border: none; background: transparent; color: #94a3b8; cursor: pointer; padding: 0 2px; line-height: 1; display: inline-flex; align-items: center; flex-shrink: 0; }
        .ts-pin-x:hover { color: #e11d48; }

        /* ===== @mention ===== */
        [x-cloak] { display: none !important; }
        .ts-mention { color: #6d28d9; background: #ede9fe; font-weight: 700; border-radius: 5px; padding: 0 4px; }
        .ts-mention-menu { position: absolute; bottom: calc(100% + 6px); left: 26px; z-index: 20; min-width: 180px; max-height: 190px; overflow-y: auto; background: #fff; border: 1px solid #e6e8f2; border-radius: 12px; box-shadow: 0 12px 28px rgba(15, 23, 42, .16); padding: 5px; }
        .ts-mention-item { display: flex; align-items: center; gap: 7px; width: 100%; text-align: left; border: none; background: transparent; border-radius: 9px; padding: 7px 10px; font-size: .88rem; font-weight: 600; color: #1e293b; cursor: pointer; }
        .ts-mention-item i.bi { color: #7c3aed; display: inline-flex; align-items: center; line-height: 1; }
        .ts-mention-item.active, .ts-mention-item:hover { background: linear-gradient(135deg, rgba(124,58,237,.12), rgba(78,70,229,.06)); }
        .ts-mentioned-badge { background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; box-shadow: 0 3px 8px rgba(124, 58, 237, .3); display: inline-flex; align-items: center; gap: 4px; }
        .ts-mentioned-badge i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }

        /* ===== Composer ===== */
        .ts-composer { border: 1px solid #e6e8f2; border-radius: 14px; padding: 6px 14px; background: #fff; box-shadow: 0 4px 14px rgba(108, 99, 255, .05); transition: .15s; }
        .ts-composer:focus-within { border-color: #c7d2fe; box-shadow: 0 0 0 .18rem rgba(124, 58, 237, .12); }
        .ts-composer textarea, .ts-composer textarea:focus { border: none !important; outline: none !important; box-shadow: none !important; background: transparent; }
        .ts-composer textarea { resize: none; font-size: .9rem; line-height: 1.5; padding: 9px 0; text-align: left; max-height: 120px; }
        .ts-input-ico { color: #a3a9bd; font-size: 1rem; line-height: 1; flex-shrink: 0; }
        .ts-attach-chip { display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; border-radius: 8px; padding: 3px 8px; font-size: .76rem; color: #475569; }
        .ts-iconbtn { width: 38px; height: 38px; border-radius: 10px; border: 1px solid #eef0f7; background: #fff; color: #64748b; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: .15s; }
        .ts-iconbtn:hover { border-color: #c7d2fe; color: #6d28d9; }
        .ts-iconbtn i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .ts-send { border: none; border-radius: 10px; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; padding: 9px 18px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 6px 14px rgba(124, 58, 237, .28); transition: .15s; }
        .ts-send:hover { filter: brightness(1.05); transform: translateY(-1px); }
        .ts-send:disabled { opacity: .7; }
        .ts-send i.bi { display: inline-flex; align-items: center; line-height: 1; }

        /* ===== Empty state glossy ===== */
        .ts-empty-card { border: 1px solid #eef0f7; background: linear-gradient(135deg, #ffffff, #faf9ff); }
        .ts-empty { padding: 56px 24px; }
        .ts-empty-badge {
            width: 96px; height: 96px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #7c3aed, #4e46e5);
            color: #fff; font-size: 2.6rem;
            box-shadow: 0 18px 40px rgba(124, 58, 237, .32), inset 0 2px 8px rgba(255, 255, 255, .45);
            position: relative;
        }
        .ts-empty-badge::after {
            content: ""; position: absolute; inset: -9px; border-radius: 50%;
            border: 2px solid rgba(124, 58, 237, .14);
        }
        .ts-empty-badge i { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .ts-empty h5 { color: #1e293b; }
        .ts-empty p { max-width: 430px; margin-inline: auto; }
        .ts-empty-btn {
            display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid #ddd6fe; background: #fff; color: #6d28d9; font-weight: 600;
            box-shadow: 0 6px 14px rgba(124, 58, 237, .12); transition: .15s;
        }
        .ts-empty-btn:hover { background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; border-color: transparent; transform: translateY(-1px); }
        .ts-empty-btn i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }

        /* ===== Folder task grup (multi-penerima) ===== */
        .ts-folder { border: 1px solid #e6e8f2; border-radius: 18px; background: linear-gradient(135deg, #fbfaff, #f5f3ff); box-shadow: 0 6px 18px rgba(108, 99, 255, .06); overflow: hidden; }
        .ts-folder-head { display: flex; align-items: center; gap: 12px; padding: 14px 18px; cursor: pointer; transition: background .15s; }
        .ts-folder-head:hover { background: rgba(124, 58, 237, .04); }
        .ts-folder-ico { width: 42px; height: 42px; flex-shrink: 0; border-radius: 12px; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 6px 14px rgba(124, 58, 237, .28); }
        .ts-folder-ico i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .ts-folder-info { flex: 1 1 auto; min-width: 0; }
        .ts-folder-title { font-weight: 800; color: #1e293b; font-size: 1.02rem; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .ts-folder-count { font-size: .68rem; font-weight: 700; color: #6d28d9; background: #ede9fe; padding: 2px 9px; border-radius: 999px; display: inline-flex; align-items: center; }
        .ts-folder-count i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .ts-folder-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 4px; font-size: .78rem; color: #64748b; }
        .ts-folder-meta i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .ts-folder-side { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .ts-folder-progress { font-size: .78rem; color: #475569; white-space: nowrap; }
        .ts-folder-progress b { color: #059669; }
        .ts-folder-chev { color: #94a3b8; display: inline-flex; align-items: center; line-height: 1; transition: transform .3s ease; }
        /* Collapse via grid-rows (0fr↔1fr): animasi ke tinggi natural tanpa mengukur
           scrollHeight, sehingga tidak desync setelah re-render Livewire. */
        .ts-folder-body { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .34s cubic-bezier(.4, 0, .2, 1); }
        .ts-folder-body.is-open { grid-template-rows: 1fr; }
        .ts-folder-body-inner { overflow: hidden; min-height: 0; padding: 4px 14px 14px; }
        .ts-folder-chat { position: relative; height: 34px; padding: 0 14px; border-radius: 999px; border: 1px solid #ddd6fe; background: #fff; color: #6d28d9; font-weight: 600; font-size: .82rem; display: inline-flex; align-items: center; gap: 6px; line-height: 1; cursor: pointer; transition: .15s; white-space: nowrap; }
        .ts-folder-chat:hover { background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; border-color: transparent; box-shadow: 0 6px 14px rgba(124, 58, 237, .28); }
        .ts-folder-chat i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .ts-folder-chat-badge { background: #ef4444; color: #fff; font-size: .62rem; font-weight: 700; min-width: 16px; height: 16px; padding: 0 4px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; }

        /* Catatan diskusi grup di detail sub-card */
        .ts-group-note { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; border: 1px dashed #ddd6fe; background: #faf9ff; border-radius: 12px; padding: 12px 14px; font-size: .85rem; color: #64748b; }
        .ts-group-note i.bi { display: inline-flex; align-items: center; line-height: 1; }

        /* ===== Sub-card anggota di dalam folder ===== */
        .ts-sub { border: 1px solid #eef0f7; border-radius: 14px; padding: 12px 14px; background: #fff; cursor: pointer; height: 100%; transition: transform .15s, box-shadow .15s, border-color .15s; }
        .ts-sub:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(76, 29, 149, .10); border-color: #ddd6fe; }
        .ts-sub.mine { border-color: #c7d2fe; background: linear-gradient(135deg, #fff, #f7f5ff); }
        .ts-sub.locked { background: #f8fafc; }
        .ts-sub-top { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
        .ts-sub-av { width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .78rem; color: #fff; background: linear-gradient(135deg, #7c3aed, #4e46e5); }
        .ts-sub-name { font-weight: 700; color: #1e293b; font-size: .9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ts-sub-badges { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }
        .ts-sub-foot { margin-top: 10px; display: flex; justify-content: flex-end; }

        /* ===== Baris pemberi → penerima task ===== */
        .ts-people { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .ts-person {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .74rem; font-weight: 700; color: #475569;
            background: #f1f5f9; border: 1px solid #e6e8f2;
            padding: 3px 10px; border-radius: 999px; max-width: 100%;
        }
        .ts-person i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; font-size: .82rem; color: #7c3aed; }
        .ts-person span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ts-person-arrow { display: inline-flex; align-items: center; justify-content: center; line-height: 1; color: #94a3b8; font-size: .8rem; }

        /* ===== Aksi kelola task (atasan pemberi) ===== */
        .ts-manage { border-top: 1px dashed #e6e8f2; margin-top: 12px; padding-top: 10px; display: flex; align-items: center; gap: 6px; }
        .ts-manage-lbl { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; margin-right: auto; display: inline-flex; align-items: center; gap: 4px; }
        .ts-manage-lbl i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .ts-mini-btn {
            width: 32px; height: 32px; border-radius: 9px; border: 1px solid #eef0f7; background: #fff;
            display: inline-flex; align-items: center; justify-content: center; line-height: 1; cursor: pointer; transition: .15s;
        }
        .ts-mini-btn i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .ts-mini-btn.edit { color: #6d28d9; }
        .ts-mini-btn.edit:hover { border-color: #c7d2fe; background: #f5f3ff; }
        .ts-mini-btn.del { color: #e11d48; }
        .ts-mini-btn.del:hover { border-color: #fecdd3; background: #fff1f2; }
        .ts-mini-btn.reopen { color: #d97706; }
        .ts-mini-btn.reopen:hover { border-color: #fde68a; background: #fffbeb; }

        /* ===== Form modal beri task ===== */
        .ts-form-label { font-weight: 600; font-size: .85rem; color: #334155; margin-bottom: 4px; }

        /* ===== Multi-select penerima ===== */
        .ts-multi { border: 1px solid #eef0f7; border-radius: 14px; padding: 6px; max-height: 230px; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; background: #fff; }
        .ts-multi.is-invalid { border-color: #ef4444; }
        .ts-multi-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 12px; cursor: pointer; margin: 0; border: 1px solid transparent; transition: .13s; }
        .ts-multi-item:hover { background: #f8f7ff; }
        .ts-multi-item.checked { background: linear-gradient(135deg, rgba(124,58,237,.09), rgba(78,70,229,.04)); border-color: #ddd6fe; }
        .ts-multi-item input { position: absolute; opacity: 0; pointer-events: none; }
        .ts-multi-av { width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .82rem; color: #fff; background: linear-gradient(135deg, #a5b4fc, #818cf8); transition: .13s; }
        .ts-multi-item.checked .ts-multi-av { background: linear-gradient(135deg, #7c3aed, #4e46e5); box-shadow: 0 4px 10px rgba(124,58,237,.28); }
        .ts-multi-name { flex: 1 1 auto; font-size: .9rem; font-weight: 600; color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ts-multi-item.checked .ts-multi-name { color: #4c1d95; }
        .ts-multi-check { width: 22px; height: 22px; border-radius: 50%; border: 2px solid #e2e8f0; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; color: #fff; background: #fff; transition: .13s; }
        .ts-multi-check i.bi { display: none; align-items: center; justify-content: center; line-height: 1; font-size: .8rem; }
        .ts-multi-item.checked .ts-multi-check { background: linear-gradient(135deg, #7c3aed, #4e46e5); border-color: transparent; }
        .ts-multi-item.checked .ts-multi-check i.bi { display: inline-flex; }
        .ts-multi-count { font-size: .76rem; color: #7c3aed; font-weight: 600; margin-top: 6px; }
        .ts-multi-search { position: relative; margin-bottom: 6px; }
        .ts-multi-search i.bi { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .85rem; line-height: 1; display: inline-flex; align-items: center; pointer-events: none; }
        .ts-multi-search input { padding-left: 34px; border-radius: 10px; }
        .ts-drop {
            border: 1.5px dashed #d6d9e6; border-radius: 14px; padding: 16px; text-align: center;
            position: relative; background: #fbfcff; transition: .15s;
        }
        .ts-drop:hover { border-color: #c7d2fe; background: #f7f5ff; }
        .ts-drop input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .ts-drop-ico { display: inline-flex; align-items: center; justify-content: center; line-height: 1; font-size: 1.5rem; color: #7c3aed; }
        .ts-thumb { position: relative; width: 74px; }
        .ts-thumb .media { width: 74px; height: 74px; border-radius: 10px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .ts-thumb .media img { width: 100%; height: 100%; object-fit: cover; }
        .ts-thumb .media i.bi { font-size: 1.4rem; color: #64748b; }
        .ts-thumb .cap { font-size: .66rem; color: #64748b; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ts-thumb .rm { position: absolute; top: -7px; right: -7px; width: 20px; height: 20px; border-radius: 50%; border: none; background: #e11d48; color: #fff; display: inline-flex; align-items: center; justify-content: center; line-height: 1; font-size: .7rem; cursor: pointer; }
        .ts-thumb .badge-new { position: absolute; bottom: 26px; left: 4px; font-size: .58rem; font-weight: 700; background: #7c3aed; color: #fff; padding: 1px 6px; border-radius: 999px; }

        /* ===== Tombol pemicu popup picker (Select2-style) ===== */
        .of-picker-btn { cursor: pointer; }
        .of-picker-btn::after { content: "\F282"; font-family: "bootstrap-icons"; float: right; color: #94a3b8; font-size: .8rem; }
        /* ===== Isi popup picker (SweetAlert) ===== */
        .of-pick-list { max-height: 320px; overflow-y: auto; text-align: left; display: flex; flex-direction: column; gap: .4rem; padding: .2rem; }
        .of-pick-item { display: block; width: 100%; text-align: left; border: 1px solid #e6e8f2; background: #fff; border-radius: 12px; padding: .7rem .9rem; font-weight: 600; color: #1e293b; font-size: .92rem; transition: all .15s ease; }
        .of-pick-item:hover { border-color: #6c63ff; background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.04)); transform: translateY(-1px); }
        .of-pick-empty { text-align: center; color: #94a3b8; padding: 1.5rem; font-size: .9rem; }
        .of-pick-row { display: flex; align-items: stretch; gap: .4rem; }
        .of-pick-row .of-pick-item { flex: 1 1 auto; width: auto; }
        .of-pick-del { flex: 0 0 auto; width: 44px; padding: 0; border: 1px solid #fee2e2; background: #fff5f5; color: #ef4444; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; transition: all .15s ease; }
        .of-pick-del:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: translateY(-1px); }
        .of-pick-add { display: flex; gap: .5rem; align-items: stretch; }
        .of-pick-add .form-control { flex: 1 1 auto; border-radius: 12px; }
        .of-pick-addbtn { flex: 0 0 auto; border-radius: 12px; font-weight: 600; white-space: nowrap; box-shadow: 0 6px 14px rgba(124, 58, 237, .22); display: inline-flex; align-items: center; justify-content: center; }
        .of-pick-del i.bi, .of-pick-addbtn i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .of-pick-msg { color: #ef4444; font-size: .82rem; margin-top: .35rem; min-height: 1rem; text-align: left; }
        .of-pick-confirm { display: flex; align-items: center; gap: .5rem; width: 100%; padding: .5rem .8rem; border: 1px dashed #fca5a5; border-radius: 12px; background: #fff5f5; color: #b91c1c; font-weight: 600; font-size: .88rem; }
        .of-pick-confirm span { margin-right: auto; }

        /* ===== Perapian card group (folder) di layar mobile ===== */
        @media (max-width: 575.98px) {
            .ts-folder-head { flex-wrap: wrap; align-items: flex-start; padding: 12px 14px; gap: 10px; }
            .ts-folder-info { flex: 1 1 calc(100% - 52px); min-width: 0; }
            .ts-folder-title { font-size: .95rem; }
            /* Baris aksi turun penuh ke bawah: progres di kiri, tombol di kanan. */
            .ts-folder-side { flex: 1 1 100%; justify-content: flex-end; flex-wrap: wrap; gap: 6px; padding-top: 8px; margin-top: 2px; border-top: 1px dashed #ece9fb; }
            .ts-folder-progress { margin-right: auto; }
            .ts-folder-chat { height: 32px; padding: 0 12px; }
            /* Sub-kartu penerima: satu kolom penuh & lega. */
            .ts-folder-body-inner .row > [class*="col-"] { flex: 0 0 100%; max-width: 100%; }
        }

        /* ===== Chip periode siklus (seragam dengan Cashflow) ===== */
        .siklus-chip { padding: 6px 14px 6px 6px; border-radius: 999px; background: linear-gradient(135deg, rgba(124, 58, 237, .10), rgba(37, 99, 235, .08)); border: 1px solid rgba(124, 58, 237, .2); }
        .siklus-chip-ico { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; font-size: .9rem; flex-shrink: 0; }
        .siklus-chip-ico i.bi { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; line-height: 1; }
        .siklus-chip-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #7c3aed; }
        .siklus-chip-date { font-size: .88rem; font-weight: 700; color: #1e293b; }
        .siklus-chip-arrow { color: #94a3b8; font-size: .8rem; }

        /* ===================== Pemilih cara pandang ===================== */
        .tampilan-switch { display: inline-flex; gap: 4px; padding: 4px; border-radius: 12px; background: #f1f5f9; border: 1px solid #e2e8f0; }
        .tampilan-btn { display: inline-flex; align-items: center; gap: 6px; border: none; background: transparent; color: #64748b; font-weight: 700; font-size: .82rem; padding: 7px 14px; border-radius: 9px; line-height: 1; }
        .tampilan-btn i.bi { display: block; line-height: 1; font-size: .95rem; }
        .tampilan-btn:hover { color: #334155; }
        .tampilan-btn.aktif { background: #fff; color: #7c3aed; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        @media (max-width: 575.98px) { .tampilan-switch { width: 100%; } .tampilan-btn { flex: 1; justify-content: center; padding: 8px 6px; } .tampilan-btn span { display: none; } }

        /* ===================== Aktivitas (ala GitHub) ===================== */
        .akt-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
        @media (max-width: 767.98px) { .akt-stats { grid-template-columns: repeat(2, 1fr); } }
        .akt-stat { background: #fff; border: 1px solid #e9edf3; border-radius: 12px; padding: 14px; text-align: center; }
        .akt-stat-angka { display: block; font-weight: 800; font-size: 1.5rem; color: #7c3aed; line-height: 1.1; }
        .akt-stat-label { display: block; font-size: .72rem; color: #64748b; margin-top: 3px; }
        .akt-graf-card, .akt-linimasa-card { background: #fff; border: 1px solid #e9edf3; border-radius: 14px; padding: 16px; }
        .akt-graf-card { margin-bottom: 14px; }
        .akt-graf-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
        .akt-scroll { overflow-x: auto; padding-bottom: 4px; }
        .akt-graf { display: inline-block; min-width: max-content; }
        .akt-bulan-row { display: flex; gap: 3px; margin-bottom: 4px; }
        .akt-hari-spacer { width: 28px; flex-shrink: 0; }
        .akt-bulan-cell { width: 13px; font-size: .62rem; color: #94a3b8; flex-shrink: 0; }
        .akt-grid-row { display: flex; gap: 3px; }
        .akt-hari-col { display: flex; flex-direction: column; gap: 3px; width: 28px; flex-shrink: 0; }
        .akt-hari-label { height: 13px; font-size: .6rem; color: #94a3b8; line-height: 13px; }
        .akt-minggu { display: flex; flex-direction: column; gap: 3px; }
        .akt-kotak { width: 13px; height: 13px; border-radius: 3px; background: #ebedf0; flex-shrink: 0; }
        .akt-kotak.akt-kosong { background: transparent; }
        .akt-l0 { background: #ebedf0; }
        .akt-l1 { background: #d8c7f5; }
        .akt-l2 { background: #b18cf0; }
        .akt-l3 { background: #8b5cf6; }
        .akt-l4 { background: #6d28d9; }
        .akt-legenda { display: flex; align-items: center; justify-content: flex-end; gap: 4px; margin-top: 10px; font-size: .68rem; color: #94a3b8; }
        .akt-lini-item { display: flex; align-items: flex-start; gap: 10px; padding: 9px 0; border-bottom: 1px dashed #eef0f4; }
        .akt-lini-item:last-child { border-bottom: none; }
        .akt-lini-dot { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #059669; color: #fff; flex-shrink: 0; }
        .akt-lini-dot.is-telat { background: #d97706; }
        .akt-lini-dot i.bi { display: block; line-height: 1; font-size: .8rem; }
        .akt-lini-judul { border: none; background: none; padding: 0; font-weight: 700; font-size: .86rem; color: #1e293b; text-align: left; }
        .akt-lini-judul:hover { color: #7c3aed; }
        .akt-lini-meta { font-size: .74rem; color: #64748b; margin-top: 2px; }

        /* ===== Pelurus ikon (Scrum & Aktivitas) =====
           Bootstrap Icons punya vertical-align:-.125em bawaan dan glyph-nya di
           ::before, jadi align-items:center pada pembungkus SAJA belum cukup —
           ikon tetap melorot sedikit dari teks. Kuncinya: <i>-nya JUGA dibuat
           flex, sehingga glyph dipusatkan flexbox (mengabaikan metrik font).
           Diletakkan paling akhir agar menang atas aturan di atas; font-size
           masing-masing tetap dipakai karena tidak ditimpa di sini. */
        .scrum-kartu-meta i.bi,
        .akt-lini-dot i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            vertical-align: 0;
            flex-shrink: 0;
        }

        .scrum-kartu-meta i.bi::before,
        .akt-lini-dot i.bi::before {
            display: block;
            line-height: 1;
        }
        /* ===== Saringan & pemilih cara pandang (khas layar ini) =====
           Gaya dasarnya dari sistem desain dasbor; yang di sini hanya yang
           memang tidak ada di sana. */
        .ts-saring { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        /* .dsb-isian melebar 100% (benar untuk medan di dalam formulir), tetapi
           di baris kepala ini ketiga kotak pilih harus berdampingan — dengan
           lebar penuh masing-masing merebut satu baris sendiri. */
        .ts-saring .ts-pilih { width: auto; min-width: 168px; flex: 0 1 auto; }
        .ts-pilih {
            appearance: none;
            padding: 8px 32px 8px 13px; border-radius: 11px; min-height: 36px;
            border: 1px solid #e9edf3; background: #fff; color: #1f2b3d;
            font-size: .82rem; font-weight: 700; cursor: pointer;
            /* Panah digambar sendiri sebagai data URI: berkas gambar luar tidak
               ikut ter-deploy (public/build di-gitignore). */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M4.5 6.5 8 10l3.5-3.5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;
        }
        .ts-pilih:focus { outline: none; border-color: #c4b5fd; box-shadow: 0 0 0 3px rgba(124,58,237,.14); }

        .ts-pandang { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .ts-pandang-btn {
            --c: #64748b;
            display: inline-flex; align-items: center; gap: 9px;
            padding: 7px 14px 7px 8px; border-radius: 12px; min-height: 40px;
            background: #fff; border: 1px solid #e9edf3; color: #64748b;
            font-size: .84rem; font-weight: 700; cursor: pointer;
            transition: color .15s ease, border-color .15s ease, background .15s ease;
        }
        /* Ubin ikon mengikuti dasbor: selalu berwarna dan selalu di tengah,
           bukan ikon telanjang di samping teks. */
        .ts-pandang-btn .dsb-ikon { width: 26px; height: 26px; border-radius: 8px; font-size: .8rem; }
        .ts-pandang-btn.aktif {
            color: var(--dsb-tinta, #1f2b3d); border-color: color-mix(in srgb, var(--c) 35%, #fff);
            background: color-mix(in srgb, var(--c) 7%, #fff);
        }
        @media (hover: hover) and (pointer: fine) {
            .ts-pandang-btn:hover { border-color: color-mix(in srgb, var(--c) 35%, #fff); color: var(--dsb-tinta, #1f2b3d); }
        }
        /* ===== Kartu di papan scrum =====
           Yang tersisa khas papan ini hanya kartunya sendiri; kolom, lencana,
           tombol, dan keadaan kosongnya sudah memakai bahasa rupa dasbor. */
        .scrum-tumpuk { display: flex; flex-direction: column; gap: 10px; }
        .scrum-kartu {
            border: 1px solid #eef2f7; border-radius: 14px; padding: 12px 13px;
            background: #fff; display: flex; flex-direction: column; gap: 9px;
        }
        /* Pita merah di tepi, bukan seluruh kartu diwarnai: kartu merah penuh
           membuat judulnya susah dibaca justru saat paling perlu dibaca. */
        .scrum-kartu.is-telat { box-shadow: inset 3px 0 0 #e11d48; border-color: #fecdd3; }
        .scrum-kartu-atas { display: flex; align-items: center; flex-wrap: wrap; gap: 5px; }
        .scrum-kartu-judul {
            display: block; width: 100%; text-align: left; padding: 0;
            background: none; border: 0; cursor: pointer;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 700; font-size: .88rem; line-height: 1.35;
            color: var(--dsb-tinta); overflow-wrap: anywhere;
        }
        @media (hover: hover) and (pointer: fine) {
            .scrum-kartu-judul:hover { color: #7c3aed; }
        }
        .scrum-kartu-meta {
            display: flex; align-items: center; flex-wrap: wrap; gap: 4px 11px;
            color: var(--dsb-redup); font-size: .76rem;
        }
        .scrum-kartu-meta > span { display: inline-flex; align-items: center; gap: 5px; }
        .scrum-kartu-meta .is-telat { color: #e11d48; font-weight: 700; }
        .scrum-kartu-meta .is-tunda { color: #b45309; font-weight: 700; }
        .scrum-kartu-aksi { display: flex; gap: 8px; }
        .scrum-kartu-aksi .dsb-tombol { flex: 1 1 auto; }

        /* ===== Rak saringan =====
           Kisi yang melipat sendiri: tiap medan minimal 180px, kotak cari dua
           kali lebih lebar karena isinya kalimat, bukan pilihan. */
        /* FLEX, bukan grid: dengan grid, baris terakhir yang tidak penuh
           meninggalkan lubang di ujung kanan — dan lubang itulah yang paling
           dulu membuat satu papan kendali terbaca berantakan. Pada flex,
           medan di baris terakhir melar mengisi sisa ruangnya. */
        .ts-saring-rak { display: flex; flex-wrap: wrap; gap: 12px; }
        .ts-saring-rak > .dsb-medan { flex: 1 1 190px; }
        .ts-medan-cari { flex: 2 1 320px; }
        @media (max-width: 575.98px) {
            .ts-saring-rak > .dsb-medan, .ts-medan-cari { flex: 1 1 100%; }
        }
        .ts-saring-kaki {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
            margin-top: 14px; padding-top: 13px; border-top: 1px solid #f1f5f9;
        }
        .ts-saring-kaki .dsb-kartu-sub { display: inline-flex; align-items: center; gap: 7px; }
        .ts-saring-kaki-aksi { display: inline-flex; align-items: center; gap: 9px; flex-wrap: wrap; }
        @media (max-width: 575.98px) {
            .ts-saring-kaki { flex-direction: column; align-items: stretch; }
            .ts-saring-kaki-aksi { width: 100%; }
            .ts-saring-kaki-aksi .dsb-tombol { flex: 1 1 100%; }
        }

        /* Pemilih cara pandang duduk di sisi kanan KEPALA BAGIAN, sejajar
           dengan penggeser periode di dasbor. Di layar sempit ia turun ke
           barisnya sendiri dan melebar penuh. */
        .dsb-kepala > .ts-pandang { flex-shrink: 0; }
        @media (max-width: 767.98px) {
            .dsb-kepala > .ts-pandang { width: 100%; }
            .dsb-kepala > .ts-pandang .ts-pandang-btn { flex: 1 1 0; justify-content: center; }
        }

        /* ===== Aksi massal & kotak centang ===== */
        .ts-massal {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
            padding: 12px clamp(14px, 2vw, 18px);
            background: #f5f3ff; border-bottom: 1px solid #e9e3fb;
        }
        .ts-massal-ket { display: inline-flex; align-items: center; gap: 8px; color: #5b21b6; font-size: .84rem; font-weight: 700; }
        .ts-massal-aksi { display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        @media (max-width: 575.98px) {
            .ts-massal { flex-direction: column; align-items: stretch; }
            .ts-massal-aksi .dsb-tombol { flex: 1 1 auto; }
        }

        .ts-centang-kolom { width: 44px; }
        .ts-centang { display: inline-flex; align-items: center; justify-content: center; cursor: pointer; padding: 6px; }
        .ts-centang input { width: 17px; height: 17px; accent-color: #7c3aed; cursor: pointer; }
        @media (max-width: 767.98px) {
            /* Saat baris jadi kartu bertumpuk, kotak centangnya naik ke pojok
               kanan atas kartu — sebagai baris sendiri ia terbaca seperti data. */
            .dsb-tabel tbody tr { position: relative; }
            .dsb-tabel tbody td.ts-centang-kolom {
                position: absolute; top: 9px; right: 10px; width: auto; padding: 0; justify-content: flex-end;
            }
            .dsb-tabel tbody td.ts-centang-kolom::before { content: none; }
            .dsb-tabel tbody tr.ts-sub td.ts-centang-kolom { display: none; }
        }

        /* ===== Pemilih kolom ===== */
        .ts-daftar-aksi { display: inline-flex; align-items: center; gap: 9px; flex-wrap: wrap; flex-shrink: 0; }
        .ts-kolom { position: relative; }
        .ts-kolom-daftar {
            position: absolute; right: 0; top: calc(100% + 6px); z-index: 20;
            min-width: 178px; padding: 7px; border-radius: 13px;
            background: #fff; border: 1px solid var(--dsb-tepi);
            box-shadow: 0 16px 34px rgba(15, 23, 42, .14);
            display: flex; flex-direction: column; gap: 2px;
        }
        .ts-kolom-item {
            display: flex; align-items: center; gap: 9px; width: 100%;
            padding: 8px 10px; border: 0; border-radius: 9px; background: none;
            color: var(--dsb-tinta); font-size: .83rem; font-weight: 600; cursor: pointer; text-align: left;
        }
        .ts-kolom-item i.bi { color: #7c3aed; font-size: .95rem; }
        @media (hover: hover) and (pointer: fine) {
            .ts-kolom-item:hover { background: #f5f3ff; }
        }
        @media (max-width: 767.98px) {
            .ts-daftar-aksi { width: 100%; }
            .ts-daftar-aksi .dsb-tombol, .ts-kolom { flex: 1 1 0; }
            .ts-kolom .dsb-tombol { width: 100%; }
        }

        /* ===== Kaki halaman tabel ===== */
        .ts-halaman {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
            padding: 13px clamp(16px, 2.2vw, 20px); border-top: 1px solid #f1f5f9;
        }
        .ts-halaman-ket { color: #6b7280; font-size: .8rem; }
        .ts-halaman-aksi { display: inline-flex; align-items: center; gap: 8px; }
        .ts-halaman-aksi .dsb-tabel-btn:disabled { opacity: .45; cursor: not-allowed; }

        /* ===== Sub-baris penerima grup =====
           Task grup adalah SATU pekerjaan untuk beberapa orang. Barisnya tetap
           satu, dan daftar penerimanya dibuka dengan menekan barisnya —
           dengan begitu daftar utama tetap sependek jumlah pekerjaan, bukan
           sepanjang jumlah orang. */
        .ts-panah { font-size: .7rem; margin-left: 2px; }
        .dsb-tabel tbody.ts-grup tr.is-induk.is-terbuka td { background: #faf8ff; }

        .dsb-tabel tbody.ts-grup tr.ts-sub td { background: #fbfcfe; padding-block: 9px; }
        /* Lekukan + garis penghubung ke induknya: tanpa penanda apa pun,
           sub-baris terbaca sebagai task tersendiri di daftar utama. */
        .dsb-tabel tbody.ts-grup tr.ts-sub td:first-child { padding-left: 34px; position: relative; }
        .dsb-tabel tbody.ts-grup tr.ts-sub td:first-child::before {
            content: ""; position: absolute; left: 21px; top: 0; bottom: 0;
            width: 2px; background: #e9e3fb;
        }
        .dsb-tabel tbody.ts-grup tr.ts-sub .dsb-tabel-judul { font-weight: 700; font-size: .85rem; }
        .dsb-tabel tbody.ts-grup tr.ts-sub.is-saya .dsb-tabel-judul { color: #5b21b6; }
        .dsb-tabel tbody.ts-grup tr.ts-sub .dsb-lencana { font-size: .66rem; }
        @media (hover: hover) and (pointer: fine) {
            .dsb-tabel tbody.ts-grup tr.ts-sub:hover td { background: #f5f3ff; }
        }
        @media (max-width: 767.98px) {
            /* Saat baris jadi kartu bertumpuk, lekukan kiri diganti pita ungu:
               padding-left di dalam kartu justru membuat isinya tampak salah
               rata, bukan tampak bersarang. */
            .dsb-tabel tbody.ts-grup tr.ts-sub {
                background: #fbfcfe; box-shadow: inset 3px 0 0 #c4b5fd;
            }
            .dsb-tabel tbody.ts-grup tr.ts-sub td:first-child { padding-left: 0; }
            .dsb-tabel tbody.ts-grup tr.ts-sub td:first-child::before { display: none; }

            /* Sel bertumpuk sudah mencetak judulnya sendiri ("RAMPUNG"), jadi
               keterangan kecil di bawah nilainya tinggal mengulang kata yang
               sama tepat di sebelahnya. */
            .dsb-tabel tbody.ts-grup tr.ts-sub td[data-judul="Rampung"] .dsb-tabel-meta { display: none; }
        }

        /* ===== Isi jendela detail task ===== */
        .ts-uraian {
            background: #f8fafc; border: 1px solid #eef2f7; border-radius: 14px;
            padding: 13px 15px; margin-bottom: 16px;
            color: #475569; font-size: .88rem; line-height: 1.65; white-space: pre-line;
        }
        .ts-bagian { margin-top: 18px; }
        .ts-bagian-judul {
            display: inline-flex; align-items: center; gap: 7px; margin-bottom: 10px;
            color: #94a3b8; font-size: .72rem; font-weight: 800;
            letter-spacing: .04em; text-transform: uppercase;
        }
        .ts-lampiran { display: flex; flex-wrap: wrap; gap: 9px; }
        .ts-lampiran-gambar img {
            width: 62px; height: 62px; object-fit: cover; border-radius: 12px;
            display: block; cursor: zoom-in; border: 1px solid #eef2f7;
        }
        .ts-lampiran-berkas {
            display: inline-flex; align-items: center; gap: 9px;
            padding: 7px 13px 7px 8px; border-radius: 12px; min-height: 44px;
            background: #fff; border: 1px solid #e9edf3; color: #475569;
            font-size: .82rem; font-weight: 700; text-decoration: none;
        }
        .ts-lampiran-berkas .dsb-ikon { width: 30px; height: 30px; border-radius: 9px; font-size: .85rem; }
        @media (hover: hover) and (pointer: fine) {
            .ts-lampiran-berkas:hover { border-color: #cbd5e1; color: #1c1f26; }
        }

        .ts-reset-teks { display: none; }
        @media (max-width: 767.98px) {
            /* Dua kelas, BUKAN satu: gaya dasbor di-include sesudah blok ini,
               dan aturannya `.dsb-tabel-btn { width: 36px }` berbobot sama —
               yang belakangan menang. Dengan lebar 36px sementara isinya ikon
               + teks, teksnya meluber ke luar layar. */
            .ts-saring .ts-reset { width: 100%; padding-inline: 14px; gap: 8px; font-size: .82rem; font-weight: 700; }
            .ts-reset-teks { display: inline; }

            /* Saringan turun ke baris sendiri di bawah judul kartunya, dan tiap
               kotak pilih melebar penuh: tiga kotak sejajar di 390px membuat
               masing-masing hanya selebar ~110px, terlalu sempit untuk membaca
               "Kalender (1–akhir bulan)". */
            .ts-saring { width: 100%; }
            .ts-saring .ts-pilih { flex: 1 1 100%; }
        }
        @media (max-width: 575.98px) {
            /* Tiga tombol sejajar tidak muat di 390px; dibuat penuh selebar
               kartunya supaya tidak ada yang terpotong separuh. */
            .ts-pandang-btn { flex: 1 1 100%; justify-content: flex-start; }
        }
    </style>

    @php
        $badgeBobot = ['ringan'=>'success','sedang'=>'warning','berat'=>'danger'];
        $badgeProg = ['belum'=>'secondary','dikerjakan'=>'info','selesai'=>'success'];
        $labelProg = ['belum'=>'Belum Dikerjakan','dikerjakan'=>'Dikerjakan','selesai'=>'Selesai'];
        $badgeBonus = ['tepat_waktu'=>'success','terlambat'=>'warning','tidak_selesai'=>'danger','tidak_ada_info'=>'primary'];
        $labelBonus = ['tepat_waktu'=>'Tepat Waktu','terlambat'=>'Melebihi Deadline','tidak_selesai'=>'Tidak Selesai','tidak_ada_info'=>'Berjalan'];

        // Palet warna komentar — diurut agar dua warna pertama paling kontras.
        // Ungu ditaruh terakhir supaya tak bentrok dengan bubble "Anda" (ungu).
        // [gradasi awal, warna solid untuk nama & aksen].
        $avatarPalette = [
            ['#3b82f6', '#2563eb'], // biru
            ['#f97316', '#ea580c'], // oranye
            ['#10b981', '#059669'], // hijau
            ['#ec4899', '#db2777'], // pink
            ['#06b6d4', '#0891b2'], // cyan
            ['#ef4444', '#dc2626'], // merah
            ['#eab308', '#ca8a04'], // kuning
            ['#8b5cf6', '#7c3aed'], // ungu
        ];
    @endphp

    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*) supaya layar ini dan
         dasbor terbaca sebagai satu aplikasi, bukan dua. Lihat catatan di
         partials/dasbor-gaya.blade.php. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')

    <div class="dsb">
        {{-- ================== KEPALA ==================
             Iramanya mengikuti dasbor: sapaan di kiri, kartu identitas dan aksi
             utama di kanan. Tanpa sisi kanan itu, kepala halaman ini tampak
             separuh kosong di layar lebar — dan itulah yang membuatnya terbaca
             berbeda dari dasbor meski memakai kelas yang sama. --}}
        @php
            $fotoAku = auth()->user()->profile_photo
                && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->profile_photo)
                    ? \Illuminate\Support\Facades\Storage::url(auth()->user()->profile_photo)
                    : null;
        @endphp

        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Task Saya</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">
                        Pekerjaan yang ditugaskan kepada Anda beserta tenggatnya
                        <span class="dsb-segar"><i class="bi bi-sort-down"></i>Terbaru di atas</span>
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <div class="dsb-aku">
                    <span class="dsb-aku-foto">
                        @if ($fotoAku)
                            <img src="{{ $fotoAku }}" alt="Foto {{ auth()->user()->name }}">
                        @else
                            <span class="dsb-aku-inisial">{{ \Illuminate\Support\Str::substr(auth()->user()->name, 0, 1) }}</span>
                        @endif
                    </span>
                    <span>
                        <span class="dsb-aku-nama">{{ auth()->user()->name }}</span>
                        <span class="dsb-aku-peran">
                            {{ $poin['total'] > 0 ? $poin['total'].' task untuk Anda' : (auth()->user()->role->name ?? 'Pengguna') }}
                        </span>
                    </span>
                </div>

                @if ($canAssign)
                    <button type="button" wire:click="openCreateTask" class="dsb-tombol is-utama">
                        <i class="bi bi-plus-lg"></i><span>Beri Task</span>
                    </button>
                @endif
            </div>
        </header>

        {{-- ================== RINGKASAN ==================
             Susunannya 2 kartu utama + 3 kartu kecil, sama dengan Ringkasan
             Keuangan di dasbor. Yang lewat tenggat sengaja dipisah dari "belum
             selesai": keduanya sama-sama belum selesai, tapi hanya satu yang
             sudah terlambat. --}}
        @php
            $semua = $tasks->count();
            $tsSelesai = $tasks->where('progress', 'selesai')->count();
            $tsLewat = $tasks->filter(fn ($t) => $t->progress !== 'selesai' && $t->bonusStatus() === 'tidak_selesai')->count();
            $tsHariIni = $tasks->filter(fn ($t) => $t->progress !== 'selesai'
                && $t->deadline_selesai && $t->deadline_selesai->isSameDay(today()))->count();
            $tsBerjalan = $semua - $tsSelesai;
            $persenSelesai = $semua > 0 ? (int) round($tsSelesai / $semua * 100) : 0;

            // Susunan kartu ditentukan SEKALI di sini, lalu dirender dari
            // daftarnya. Versi sebelumnya memakai rantai @if terpisah, dan pada
            // satu keadaan — administrator tanpa task sendiri — kartu "Selesai"
            // hilang sama sekali sementara barisnya menyisakan lubang di ujung.
            $adaPoin = $poin['total'] > 0;

            $susunan = \App\Livewire\Pages\Admin\Task\TaskSayaList::susunanKartu($adaPoin, (bool) $bonusRupiah);
            $utamaKedua = $susunan['utama_kedua'];
            $kecil = $susunan['kecil'];
            $lebarKecil = $susunan['lebar_kecil'];
        @endphp

        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #7c3aed">
                    <span class="dsb-kepala-ikon"><i class="bi bi-clipboard-check-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Ringkasan</span>
                        <h2 class="dsb-judul">Keadaan Task</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-list-check"></i>{{ $semua }} task</span>
                            @if ($modePeriode === 'siklus20' && $siklusMulai && $siklusAkhir)
                                <span class="dsb-chip"><i class="bi bi-calendar-range"></i>{{ $siklusMulai->locale('id')->translatedFormat('d M') }} – {{ $siklusAkhir->locale('id')->translatedFormat('d M Y') }}</span>
                            @endif
                            <span class="dsb-chip is-samar">Hanya task yang menyangkut Anda</span>
                        </div>
                    </div>
                </div>

                <article class="dsb-stat is-utama k-6" style="--c: {{ $tsBerjalan > 0 ? '#0284c7' : '#16a34a' }}">
                    <span class="dsb-ikon"><i class="bi bi-hourglass-split"></i></span>
                    <p class="dsb-stat-label">Belum Selesai</p>
                    <p class="dsb-stat-nilai">{{ $tsBerjalan }}<span class="dsb-stat-satuan">task</span></p>
                    @if ($tsHariIni > 0)
                        <span class="dsb-pil"><i class="bi bi-alarm-fill"></i>{{ $tsHariIni }} jatuh tempo hari ini</span>
                    @endif
                    <p class="dsb-stat-ket"><i class="bi bi-list-task"></i><span>Dari {{ $semua }} task periode ini</span></p>
                </article>

                @if ($utamaKedua === 'poin')
                    {{-- POIN, bukan rupiah.

                         Bonus penyelesaian task memang dibagi dari satu pool
                         anggaran, tetapi besaran rupiahnya urusan penggajian —
                         hanya pemegang view_all_gajikaryawan yang boleh
                         melihatnya. Yang dilihat semua orang adalah POIN: bobot
                         dikali persentase status, memakai konstanta yang SAMA
                         dengan perhitungan uangnya. --}}
                    @php $warnaPoin = $poin['persen'] >= 80 ? '#16a34a' : ($poin['persen'] >= 50 ? '#d97706' : '#e11d48'); @endphp
                    <article class="dsb-stat is-utama k-6" style="--c: {{ $warnaPoin }}">
                        <span class="dsb-ikon"><i class="bi bi-award-fill"></i></span>
                        <p class="dsb-stat-label">Poin Task Saya</p>
                        <p class="dsb-stat-nilai">
                            {{ rtrim(rtrim(number_format($poin['didapat'], 1, ',', '.'), '0'), ',') }}<span class="dsb-stat-satuan">dari {{ $poin['maksimum'] }} poin</span>
                        </p>
                        @if ($poin['persen'] !== null)
                            <span class="dsb-kemajuan" style="--c: {{ $warnaPoin }}">
                                <span style="width: {{ min($poin['persen'], 100) }}%"></span>
                            </span>
                        @endif
                        <p class="dsb-stat-ket">
                            <i class="bi bi-info-circle"></i>
                            <span>Ringan 1 &bull; sedang 2 &bull; berat 3. Terlambat 60%, tidak selesai 0.</span>
                        </p>
                    </article>
                @elseif ($utamaKedua === 'bonus')
                    {{-- Nilai rupiahnya HANYA untuk pemegang view_all_gajikaryawan. --}}
                    <article class="dsb-stat is-utama k-6" style="--c: #7c3aed">
                        <span class="dsb-ikon"><i class="bi bi-cash-stack"></i></span>
                        <p class="dsb-stat-label">
                            Bonus Periode Ini
                            <span class="dsb-tanda-kini"><i class="bi bi-shield-lock-fill"></i>Hanya administrator</span>
                        </p>
                        <p class="dsb-stat-nilai">Rp {{ number_format($bonusRupiah['terpakai'], 0, ',', '.') }}</p>
                        @if ($bonusRupiah['pool'] > 0)
                            <span class="dsb-kemajuan" style="--c: #7c3aed">
                                <span style="width: {{ min(round($bonusRupiah['terpakai'] / $bonusRupiah['pool'] * 100), 100) }}%"></span>
                            </span>
                        @endif
                        <p class="dsb-stat-ket">
                            <i class="bi bi-wallet2"></i>
                            <span>Pool Rp {{ number_format($bonusRupiah['pool'], 0, ',', '.') }} &bull; sisa Rp {{ number_format($bonusRupiah['sisa'], 0, ',', '.') }}</span>
                        </p>
                        {{-- Rincian per orang & per task ada di layar Penyelesaian
                             Task; tanpa tautan ini angkanya jadi ujung jalan. --}}
                        @if (\Illuminate\Support\Facades\Route::has('admin.penyelesaian-task.index'))
                            <a class="dsb-tutup-kartu" href="{{ route('admin.penyelesaian-task.index') }}" wire:navigate>
                                <span class="visually-hidden">Buka rincian bonus penyelesaian task</span>
                            </a>
                        @endif
                    </article>
                @else
                    <article class="dsb-stat is-utama k-6" style="--c: #16a34a">
                        <span class="dsb-ikon"><i class="bi bi-check-circle-fill"></i></span>
                        <p class="dsb-stat-label">Selesai</p>
                        <p class="dsb-stat-nilai">{{ $tsSelesai }}<span class="dsb-stat-satuan">task</span></p>
                        @if ($semua > 0)
                            <span class="dsb-kemajuan" style="--c: #16a34a"><span style="width: {{ $persenSelesai }}%"></span></span>
                        @endif
                        <p class="dsb-stat-ket"><i class="bi bi-percent"></i><span>{{ $semua > 0 ? $persenSelesai.'% dari periode ini' : 'Belum ada task' }}</span></p>
                    </article>
                @endif

                {{-- Kartu kecil dirender DARI DAFTARNYA, bukan lewat rantai @if:
                     dengan rantai, satu keadaan yang tidak terpikir membuat
                     kartunya hilang sekaligus meninggalkan lubang di barisnya. --}}
                @foreach ($kecil as $jenis)
                    @if ($jenis === 'hari-ini')
                        <article class="dsb-stat {{ $lebarKecil }}" style="--c: {{ $tsHariIni > 0 ? '#d97706' : '#64748b' }}">
                            <span class="dsb-ikon"><i class="bi bi-alarm-fill"></i></span>
                            <p class="dsb-stat-label">Jatuh Tempo Hari Ini</p>
                            <p class="dsb-stat-nilai">{{ $tsHariIni }}<span class="dsb-stat-satuan">task</span></p>
                            <p class="dsb-stat-ket"><i class="bi bi-calendar-day"></i><span>{{ $tsHariIni > 0 ? 'Kerjakan yang ini lebih dulu' : 'Tidak ada yang jatuh tempo' }}</span></p>
                        </article>
                    @elseif ($jenis === 'lewat')
                        <article class="dsb-stat {{ $lebarKecil }}" style="--c: {{ $tsLewat > 0 ? '#e11d48' : '#16a34a' }}">
                            <span class="dsb-ikon"><i class="bi {{ $tsLewat > 0 ? 'bi-clipboard-x-fill' : 'bi-patch-check-fill' }}"></i></span>
                            <p class="dsb-stat-label">Lewat Tenggat</p>
                            <p class="dsb-stat-nilai">{{ $tsLewat }}<span class="dsb-stat-satuan">task</span></p>
                            <p class="dsb-stat-ket"><i class="bi bi-calendar-x"></i><span>{{ $tsLewat > 0 ? 'Sudah melewati tanggalnya' : 'Tidak ada yang terlambat' }}</span></p>
                        </article>
                    @elseif ($jenis === 'selesai')
                        <article class="dsb-stat {{ $lebarKecil }}" style="--c: #16a34a">
                            <span class="dsb-ikon"><i class="bi bi-check-circle-fill"></i></span>
                            <p class="dsb-stat-label">Selesai</p>
                            <p class="dsb-stat-nilai">{{ $tsSelesai }}<span class="dsb-stat-satuan">task</span></p>
                            @if ($semua > 0)
                                <span class="dsb-kemajuan" style="--c: #16a34a"><span style="width: {{ $persenSelesai }}%"></span></span>
                            @endif
                            <p class="dsb-stat-ket"><i class="bi bi-percent"></i><span>{{ $semua > 0 ? $persenSelesai.'% dari periode ini' : 'Belum ada task' }}</span></p>
                        </article>
                    @elseif ($jenis === 'bonus')
                        {{-- Nilai rupiahnya HANYA untuk pemegang view_all_gajikaryawan. --}}
                        <article class="dsb-stat {{ $lebarKecil }}" style="--c: #7c3aed">
                            <span class="dsb-ikon"><i class="bi bi-cash-stack"></i></span>
                            <p class="dsb-stat-label">
                                Bonus Periode
                                <span class="dsb-tanda-kini"><i class="bi bi-shield-lock-fill"></i>Admin</span>
                            </p>
                            <p class="dsb-stat-nilai">Rp {{ number_format($bonusRupiah['terpakai'], 0, ',', '.') }}</p>
                            <p class="dsb-stat-ket"><i class="bi bi-wallet2"></i><span>Sisa Rp {{ number_format($bonusRupiah['sisa'], 0, ',', '.') }}</span></p>
                        </article>
                    @endif
                @endforeach
            </div>
        </section>

        {{-- ================== APA YANG DITAMPILKAN ==================
             Periode, saringan, dan cara pandang duduk di SATU kartu dengan satu
             kisi. Sebelumnya periode ada di kepala kartu dan saringan di
             badannya — dua baris kendali yang terbaca seperti dua kelompok
             berbeda padahal mengatur hal yang sama. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #0284c7">
                    <span class="dsb-kepala-ikon"><i class="bi bi-funnel-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Tampilan</span>
                        <h2 class="dsb-judul">Apa yang Ditampilkan</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-calendar-range"></i>{{ $bulan ? ($daftarBulan[$bulan] ?? $bulan) : 'Semua bulan' }} {{ $tahun }}</span>
                            @if ($adaSaringan)
                                <span class="dsb-chip"><i class="bi bi-funnel"></i>{{ $totalGrup }} task cocok</span>
                            @endif
                            <span class="dsb-chip is-memuat" wire:loading.inline-flex
                                wire:target="cari,saringStatus,saringOrang,saringKategori,saringArah,bulan,tahun,modePeriode,urutkan,keHalaman,kosongkanSaringan,resetFilter">
                                <span class="dsb-putar is-kecil"></span>Menyaring…
                            </span>
                        </div>
                    </div>

                    <div class="ts-pandang">
                        @foreach ([
                            'daftar' => ['bi-table', 'Tabel', '#7c3aed'],
                            'scrum' => ['bi-kanban-fill', 'Papan', '#0284c7'],
                            'aktivitas' => ['bi-grid-3x3-gap-fill', 'Aktivitas', '#16a34a'],
                        ] as $mode => [$ikon, $label, $warna])
                            <button type="button" wire:click="gantiTampilan('{{ $mode }}')"
                                class="ts-pandang-btn {{ $tampilan === $mode ? 'aktif' : '' }}" style="--c: {{ $warna }}">
                                <span class="dsb-ikon is-kecil"><i class="bi {{ $ikon }}"></i></span>
                                <span>{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="ts-saring-rak">
                            <div class="dsb-medan ts-medan-cari">
                                <label class="dsb-label" for="ts-cari">Cari task</label>
                                <div class="dsb-cari">
                                    <i class="bi bi-search"></i>
                                    <input id="ts-cari" type="search" class="dsb-isian"
                                        wire:model.live.debounce.400ms="cari" placeholder="Nama atau isi task…">
                                    @if ($cari !== '')
                                        <button type="button" class="dsb-cari-hapus" wire:click="$set('cari', '')" title="Hapus pencarian">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ts-status">Status</label>
                                <select id="ts-status" class="dsb-isian" wire:model.live="saringStatus">
                                    <option value="">Semua status</option>
                                    <option value="belum">Belum dikerjakan</option>
                                    <option value="dikerjakan">Sedang dikerjakan</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="telat">Lewat tenggat</option>
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ts-orang">Penerima</label>
                                <select id="ts-orang" class="dsb-isian" wire:model.live="saringOrang">
                                    <option value="">Semua orang</option>
                                    @foreach ($daftarOrang as $o)
                                        <option value="{{ $o['id'] }}">{{ $o['id'] === auth()->id() ? $o['nama'].' (Anda)' : $o['nama'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ts-kategori">Kategori</label>
                                <select id="ts-kategori" class="dsb-isian" wire:model.live="saringKategori">
                                    <option value="">Semua kategori</option>
                                    @foreach ($daftarKategoriSaring as $kat)
                                        <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Bagi atasan, "task untuk saya" dan "task yang saya berikan"
                                 bercampur di satu daftar padahal sifatnya berbeda: yang satu
                                 harus dikerjakan, yang satu harus ditagih. --}}
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ts-arah">Hubungan</label>
                                <select id="ts-arah" class="dsb-isian" wire:model.live="saringArah">
                                    <option value="semua">Semua task</option>
                                    <option value="saya">Ditugaskan ke saya</option>
                                    <option value="dari-saya">Saya yang memberi</option>
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ts-mode">Dasar periode</label>
                                <select id="ts-mode" class="dsb-isian" wire:model.live="modePeriode">
                                    <option value="kalender">Kalender (1–akhir bulan)</option>
                                    <option value="siklus20">Siklus gaji (21–20)</option>
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ts-bulan">Bulan</label>
                                <select id="ts-bulan" class="dsb-isian" wire:model.live="bulan">
                                    <option value="">Semua bulan</option>
                                    @foreach ($daftarBulan as $num => $nama)
                                        <option value="{{ $num }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ts-tahun">Tahun</label>
                                <select id="ts-tahun" class="dsb-isian" wire:model.live="tahun">
                                    <option value="">Semua tahun</option>
                                    @foreach ($daftarTahun as $th)
                                        <option value="{{ $th }}">{{ $th }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if ($adaSaringan || $bulan || $tahun || $modePeriode !== 'kalender')
                            <div class="ts-saring-kaki">
                                <span class="dsb-kartu-sub">
                                    @if ($modePeriode === 'siklus20' && ! ($siklusMulai && $siklusAkhir))
                                        <i class="bi bi-info-circle"></i> Pilih <b>bulan</b> untuk menentukan siklus gajinya — mis. Juli → 21 Jun s/d 20 Jul.
                                    @else
                                        <i class="bi bi-check2"></i> Saringan sedang aktif.
                                    @endif
                                </span>
                                <span class="ts-saring-kaki-aksi">
                                    @if ($adaSaringan)
                                        <button type="button" wire:click="kosongkanSaringan" class="dsb-tombol is-lembut">
                                            <i class="bi bi-x-circle"></i><span>Kosongkan saringan</span>
                                        </button>
                                    @endif
                                    @if ($bulan || $tahun || $modePeriode !== 'kalender')
                                        <button type="button" wire:click="resetFilter" class="dsb-tombol is-lembut">
                                            <i class="bi bi-arrow-counterclockwise"></i><span>Kembalikan periode</span>
                                        </button>
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== ISI ================== --}}
        @php
            // Satu halaman saja — dipenggal per GRUP di komponen, bukan per
            // baris, supaya satu task grup tidak terpotong di tengah.
            $ordered = $grupHalaman;
            $namaUrut = ['terbaru' => 'Terbaru', 'tenggat' => 'Tenggat', 'nama' => 'Nama', 'status' => 'Status'];
        @endphp

        <section class="dsb-bagian" wire:loading.class="dsb-sedang-muat"
            wire:target="cari,saringStatus,saringOrang,saringKategori,saringArah,bulan,tahun,modePeriode,urutkan,keHalaman,kosongkanSaringan,resetFilter,gantiTampilan">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #16a34a">
                    <span class="dsb-kepala-ikon">
                        <i class="bi {{ $tampilan === 'scrum' ? 'bi-kanban-fill' : ($tampilan === 'aktivitas' ? 'bi-grid-3x3-gap-fill' : 'bi-list-task') }}"></i>
                    </span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">{{ $tampilan === 'scrum' ? 'Papan' : ($tampilan === 'aktivitas' ? 'Riwayat' : 'Daftar') }}</span>
                        <h2 class="dsb-judul">
                            {{ $tampilan === 'scrum' ? 'Papan Scrum' : ($tampilan === 'aktivitas' ? 'Aktivitas Tahun Ini' : 'Task Anda') }}
                        </h2>
                        <div class="dsb-chip-deret">
                            @if ($tampilan === 'daftar')
                                <span class="dsb-chip"><i class="bi bi-list-ol"></i>{{ $totalGrup }} task</span>
                                <span class="dsb-chip"><i class="bi bi-sort-down"></i>Urut: {{ $namaUrut[$urut] ?? 'Terbaru' }}</span>
                                <span class="dsb-chip is-samar">Klik barisnya untuk membuka</span>
                            @elseif ($tampilan === 'scrum')
                                <span class="dsb-chip is-samar">Task yang sama dengan tabel, dikelompokkan per status</span>
                            @else
                                <span class="dsb-chip is-samar">Hanya task yang sudah diselesaikan{{ $adaSaringan ? ', mengikuti saringan di atas' : '' }}</span>
                            @endif
                        </div>
                    </div>

                    @if ($tampilan === 'daftar')
                        <div class="ts-daftar-aksi">
                            {{-- Kolom yang boleh ditutup: pilihan PEMBACA, bukan lebar
                                 layar — yang lebar layar sudah diurus CSS. --}}
                            <div class="ts-kolom" x-data="{ buka: false }" x-on:click.outside="buka = false">
                                <button type="button" class="dsb-tombol is-lembut" x-on:click="buka = ! buka"
                                    aria-haspopup="true" x-bind:aria-expanded="buka ? 'true' : 'false'">
                                    <i class="bi bi-layout-three-columns"></i><span>Kolom</span>
                                </button>
                                <div class="ts-kolom-daftar" x-show="buka" x-cloak>
                                    @foreach (\App\Livewire\Pages\Admin\Task\TaskSayaList::KOLOM_BISA_DITUTUP as $kunci => $nama)
                                        <button type="button" class="ts-kolom-item" wire:click="alihkanKolom('{{ $kunci }}')">
                                            <i class="bi {{ in_array($kunci, $kolomSembunyi, true) ? 'bi-square' : 'bi-check-square-fill' }}"></i>
                                            <span>{{ $nama }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <button type="button" wire:click="unduhExcel" class="dsb-tombol is-lembut"
                                wire:loading.attr="disabled" wire:target="unduhExcel">
                                <i class="bi bi-file-earmark-excel"></i><span>Unduh Excel</span>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="k-12">
                    {{-- Papan dan grafik punya keadaan kosongnya sendiri. Tanpa itu,
                         saringan yang tidak menghasilkan apa pun memunculkan papan
                         bertiga kolom kosong — tanpa satu pun jalan keluar. --}}
                    @if ($tampilan === 'scrum' && $tasks->isEmpty())
                        @include('livewire.pages.admin.task.partials.task-kosong')
                    @elseif ($tampilan === 'aktivitas' && ($aktivitas['total'] ?? 0) === 0)
                        @include('livewire.pages.admin.task.partials.task-kosong')
                    @elseif ($tampilan === 'scrum')
                        @include('livewire.pages.admin.task.partials.task-scrum')
                    @elseif ($tampilan === 'aktivitas')
                        @include('livewire.pages.admin.task.partials.task-aktivitas')
                    @elseif ($ordered->isEmpty())
                        <div class="dsb-kartu">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi {{ ($bulan || $tahun || $adaSaringan) ? 'bi-funnel' : 'bi-clipboard-check' }}"></i></span>
                                {{-- Layar kosong karena SARINGAN berbeda dari layar kosong
                                     karena memang belum ada task: yang pertama punya jalan
                                     keluar, yang kedua tidak. --}}
                                @if ($adaSaringan)
                                    <p class="dsb-kosong-judul">Tidak ada task yang cocok</p>
                                    <p class="dsb-kosong-ket">Tidak ada task yang cocok dengan saringan yang sedang aktif.</p>
                                    <button type="button" wire:click="kosongkanSaringan" class="dsb-tombol is-utama" style="margin-top: 14px;">
                                        <i class="bi bi-x-circle"></i><span>Kosongkan saringan</span>
                                    </button>
                                @else
                                    <p class="dsb-kosong-judul">{{ ($bulan || $tahun) ? 'Tidak ada task di periode ini' : 'Belum ada task' }}</p>
                                    <p class="dsb-kosong-ket">
                                        @if ($bulan || $tahun)
                                            Coba ganti periodenya di saringan atas, atau tampilkan semua.
                                        @else
                                            Task yang ditugaskan kepada Anda akan muncul di sini.
                                        @endif
                                    </p>
                                    @if ($bulan || $tahun)
                                        <button type="button" wire:click="resetFilter" class="dsb-tombol is-utama" style="margin-top: 14px;">
                                            <i class="bi bi-arrow-counterclockwise"></i><span>Tampilkan semua periode</span>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @else
                        @include('livewire.pages.admin.task.partials.task-tabel')
                    @endif
                </div>
            </div>
        </section>
    </div>

    {{-- ===== Modal beri/edit task ke bawahan ===== --}}
    @if($showTaskModal)
    <div class="ts-modal-back" wire:click="$set('showTaskModal', false)"></div>
    <div class="ts-modal">
        <div class="ts-modal-card dsb is-datar" style="max-width:600px;" role="dialog" aria-modal="true" tabindex="-1"
            data-tutup="showTaskModal" aria-label="{{ $editingTaskId ? 'Edit task' : 'Beri task ke bawahan' }}">
            <div class="dsb-jendela-kepala">
                <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-{{ $editingTaskId ? 'pencil-square' : 'plus-circle' }}"></i></span>
                <span class="dsb-jendela-teks">
                    <h5 class="dsb-jendela-judul">{{ $editingTaskId ? 'Edit Task' : 'Beri Task ke Bawahan' }}</h5>
                    <span class="dsb-kartu-sub">{{ $editingTaskId ? 'Perubahan berlaku untuk seluruh penerima task ini' : 'Bisa diberikan ke lebih dari satu orang sekaligus' }}</span>
                </span>
                <button type="button" class="dsb-jendela-tutup" wire:click="$set('showTaskModal', false)" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="dsb-jendela-isi">
                <div class="mb-3">
                    <label class="dsb-label">Penerima <span class="dsb-wajib">*</span>
                        <span class="dsb-label-ket">— bisa pilih lebih dari satu</span>
                    </label>
                    <div x-data="{ q: '', names: @js($bawahan->pluck('name')->map(fn ($n) => mb_strtolower($n))->values()), get anyVisible() { return this.names.some(n => n.includes(this.q.toLowerCase())); } }">
                        @if($bawahan->count() > 5)
                        <div class="ts-multi-search">
                            <i class="bi bi-search"></i>
                            <input type="text" x-model="q" placeholder="Cari nama bawahan..." class="dsb-isian">
                        </div>
                        @endif
                        <div class="ts-multi @error('t_user_ids') is-invalid @enderror">
                            @forelse($bawahan as $b)
                            <label class="ts-multi-item {{ in_array((string) $b->id, array_map('strval', $t_user_ids)) ? 'checked' : '' }}"
                                x-show="@js(mb_strtolower($b->name)).includes(q.toLowerCase())">
                                <input type="checkbox" value="{{ $b->id }}" wire:model.live="t_user_ids">
                                <span class="ts-multi-av">{{ strtoupper(mb_substr($b->name, 0, 1)) }}</span>
                                <span class="ts-multi-name">{{ $b->name }}</span>
                                <span class="ts-multi-check"><i class="bi bi-check-lg"></i></span>
                            </label>
                            @empty
                            <div class="text-muted small p-2">Tidak ada bawahan.</div>
                            @endforelse
                            @if($bawahan->count())
                            <div class="text-muted small p-2 text-center" x-show="!anyVisible" x-cloak>Tidak ada nama yang cocok.</div>
                            @endif
                        </div>
                    </div>
                    <div class="ts-multi-count">{{ count($t_user_ids) }} penerima dipilih</div>
                    @error('t_user_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('t_user_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="dsb-label">Nama Task <span class="dsb-wajib">*</span></label>
                    <input type="text" wire:model="t_nama" class="dsb-isian @error('t_nama') is-galat @enderror" placeholder="Mis. Susun laporan mingguan">
                    @error('t_nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="dsb-label">Deskripsi</label>
                    <textarea wire:model="t_deskripsi" rows="2" class="dsb-isian" placeholder="Rincian task (opsional)"></textarea>
                </div>

                {{-- Kategori & Label — popup picker (Select2-style, bisa tambah & hapus) --}}
                @php
                    $selCat = $categories->firstWhere('id', (int) $t_category_id);
                    $selLab = $categoryLabels->firstWhere('id', (int) $t_label_id);
                @endphp
                <div id="tsPickData" hidden
                    data-categories='@json($categories->map(fn ($c) => ['id' => (string) $c->id, 'name' => $c->nama])->values())'
                    data-labels='@json($categoryLabels->map(fn ($l) => ['id' => (string) $l->id, 'name' => $l->nama])->values())'></div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="dsb-label">Kategori</label>
                        <button type="button" onclick="tsKategoriPicker(this)" class="dsb-isian of-picker-btn" style="text-align: left;">
                            @if($selCat)<span class="text-dark">{{ $selCat->nama }}</span>
                            @else<span class="text-muted">Pilih kategori</span>@endif
                        </button>
                    </div>
                    @if($t_category_id)
                    <div class="col-md-6">
                        <label class="dsb-label">Label <span class="dsb-label-ket">— mis. bug / improvement</span></label>
                        <button type="button" onclick="tsLabelPicker(this)" class="dsb-isian of-picker-btn" style="text-align: left;">
                            @if($selLab)<span class="text-dark">{{ $selLab->nama }}</span>
                            @else<span class="text-muted">Pilih label</span>@endif
                        </button>
                    </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="dsb-label">Lampiran <span class="dsb-label-ket">— gambar/file, bisa banyak (maks 2 MB)</span></label>
                    <div class="ts-drop" wire:loading.class="opacity-50" wire:target="newFiles">
                        <input type="file" wire:model="newFiles" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt">
                        <span class="ts-drop-ico"><i class="bi bi-cloud-arrow-up"></i></span>
                        <div class="fw-semibold text-dark" style="font-size:.88rem;">Klik untuk pilih gambar / file</div>
                        <div wire:loading wire:target="newFiles" class="text-primary small mt-1"><span class="spinner-border spinner-border-sm me-1"></span>Mengunggah...</div>
                    </div>
                    @error('newFiles.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                    @if(($editingTaskId && $editAttachments->count()) || !empty($t_files))
                    <div class="d-flex flex-wrap gap-3 mt-3">
                        @if($editingTaskId)
                        @foreach($editAttachments as $att)
                        <div class="ts-thumb">
                            @if($att->isImage())
                            <a href="javascript:void(0)" role="button" class="ts-img-zoom d-block text-decoration-none" data-img-url="{{ Storage::url($att->path) }}" title="Perbesar gambar">
                                <div class="media"><img src="{{ Storage::url($att->path) }}" alt="" style="cursor:zoom-in;"></div>
                            </a>
                            @else
                            <a href="{{ Storage::url($att->path) }}" target="_blank" class="d-block text-decoration-none">
                                <div class="media"><i class="bi bi-file-earmark-text"></i></div>
                            </a>
                            @endif
                            <div class="cap">{{ $att->name }}</div>
                            <button type="button" class="rm" wire:click="removeAttachment('{{ $att->id }}')" title="Hapus"><i class="bi bi-x"></i></button>
                        </div>
                        @endforeach
                        @endif
                        @foreach($t_files as $i => $file)
                        @php $isImg = str_starts_with((string) $file->getMimeType(), 'image/'); @endphp
                        <div class="ts-thumb">
                            <div class="media">@if($isImg)<img src="{{ \App\Support\PratinjauUnggahan::url($file) }}" alt="">@else<i class="bi bi-file-earmark-arrow-up"></i>@endif</div>
                            <span class="badge-new">Baru</span>
                            <div class="cap">{{ $file->getClientOriginalName() }}</div>
                            <button type="button" class="rm" wire:click="removeNewFile({{ $i }})" title="Batal"><i class="bi bi-x"></i></button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="dsb-label">Bobot</label>
                        <select wire:model="t_bobot" class="dsb-isian">
                            <option value="ringan">Ringan (1)</option>
                            <option value="sedang">Sedang (2)</option>
                            <option value="berat">Berat (3)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="dsb-label">Deadline Mulai</label>
                        <input type="date" wire:model="t_deadline_mulai" class="dsb-isian @error('t_deadline_mulai') is-galat @enderror">
                        @error('t_deadline_mulai')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="dsb-label">Deadline Selesai</label>
                        <input type="date" wire:model="t_deadline_selesai" class="dsb-isian @error('t_deadline_selesai') is-galat @enderror">
                        @error('t_deadline_selesai')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            {{-- Kaki jendela, bukan ujung badannya: tombol keputusan harus tetap
                 terlihat walau isinya digulung panjang. --}}
            <div class="dsb-jendela-kaki" style="justify-content: flex-end;">
                <button type="button" class="dsb-tombol is-lembut" wire:click="$set('showTaskModal', false)"><span>Batal</span></button>
                <button type="button" class="dsb-tombol is-utama"
                    wire:click="saveTask" wire:loading.attr="disabled" wire:target="saveTask">
                    <i class="bi bi-check2-circle" style="display:inline-flex;align-items:center;line-height:1;"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Jendela detail task =====
         Memakai bahasa rupa dasbor (dsb-*) seperti sisa layar ini: ubin ikon
         berwarna, lencana yang sama, dan kisi label–nilai yang sama. Kelas
         .is-datar mematikan padding halaman milik .dsb — di dalam jendela,
         tepinya sudah diurus jendela itu sendiri. --}}
    @if($showModal && $activeTask)
    @php
        $locked = $activeTask->isLocked();
        $bs = $activeTask->bonusStatus();
        $selesai = $activeTask->progress === 'selesai';
        $lewatTenggat = ! $selesai && $bs === 'tidak_selesai';
        $sisaHari = $activeTask->deadline_selesai
            ? (int) now()->startOfDay()->diffInDays($activeTask->deadline_selesai->copy()->startOfDay(), false)
            : null;

        // Warna jendela = warna keadaan task, bukan warna merek. Ungu untuk
        // semua membuat task yang terlambat terlihat sama tenangnya dengan
        // yang baru dimulai.
        $warnaTask = $lewatTenggat ? '#e11d48' : ($selesai ? '#16a34a' : ($activeTask->progress === 'dikerjakan' ? '#0284c7' : '#6366f1'));
        $ikonTask = $lewatTenggat ? 'bi-exclamation-triangle-fill' : ($selesai ? 'bi-check-circle-fill' : ($activeTask->progress === 'dikerjakan' ? 'bi-hourglass-split' : 'bi-card-checklist'));
        $lencanaProg = ['belum' => 'is-nila', 'dikerjakan' => 'is-biru', 'selesai' => 'is-hijau'];
        $lencanaBobotM = ['ringan' => 'is-hijau', 'sedang' => 'is-kuning', 'berat' => 'is-merah'];
    @endphp
    <div class="ts-modal-back" wire:click="$set('showModal', false)"></div>
    <div class="ts-modal">
        <div class="ts-modal-card dsb is-datar" role="dialog" aria-modal="true" tabindex="-1"
            data-tutup="showModal" aria-label="Detail task">

            <div class="dsb-jendela-kepala">
                <span class="dsb-ikon is-kecil" style="--c: {{ $warnaTask }}"><i class="bi {{ $ikonTask }}"></i></span>
                <span class="dsb-jendela-teks">
                    <h5 class="dsb-jendela-judul">{{ $activeTask->nama }}</h5>
                    <span class="dsb-jendela-lencana">
                        <span class="dsb-lencana {{ $lewatTenggat ? 'is-merah' : ($lencanaProg[$activeTask->progress] ?? 'is-abu') }}">
                            {{ $lewatTenggat ? 'Lewat Tenggat' : ($labelProg[$activeTask->progress] ?? ucfirst($activeTask->progress)) }}
                        </span>
                        @if ($activeTask->category)
                            <span class="dsb-lencana is-ungu">{{ $activeTask->category->nama }}</span>
                        @endif
                        @if ($activeTask->label)
                            <span class="dsb-lencana is-biru">{{ $activeTask->label->nama }}</span>
                        @endif
                        <span class="dsb-lencana {{ $lencanaBobotM[$activeTask->bobot] ?? 'is-abu' }}">Bobot {{ $activeTask->bobot }}</span>
                        @if ($locked)
                            <span class="dsb-lencana is-abu"><i class="bi bi-lock-fill"></i>Terkunci</span>
                        @endif
                    </span>
                </span>
                <button type="button" class="dsb-jendela-tutup" wire:click="$set('showModal', false)" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="dsb-jendela-isi">
                @if ($activeTask->deskripsi)
                    {{-- Uraian diberi wadah sendiri, bukan paragraf telanjang:
                         di antara lencana dan kisi data, teks tanpa wadah
                         terbaca seperti keterangan kecil, padahal ia isi
                         perintahnya. --}}
                    <div class="ts-uraian">{{ $activeTask->deskripsi }}</div>
                @endif

                <div class="dsb-data">
                    <div class="dsb-data-baris">
                        <span class="dsb-data-label"><i class="bi bi-person-badge"></i>Pemberi</span>
                        <span class="dsb-data-nilai">{{ $activeTask->pemberi?->name ?? $activeTask->pembuat?->name ?? 'Admin' }}</span>
                    </div>
                    <div class="dsb-data-baris">
                        <span class="dsb-data-label"><i class="bi bi-person-check"></i>Dikerjakan oleh</span>
                        <span class="dsb-data-nilai">{{ $activeTask->user_id === auth()->id() ? 'Anda' : ($activeTask->karyawan?->name ?? '-') }}</span>
                    </div>
                    <div class="dsb-data-baris">
                        <span class="dsb-data-label"><i class="bi bi-calendar-range"></i>Rentang</span>
                        <span class="dsb-data-nilai">
                            {{ $activeTask->deadline_mulai?->locale('id')->translatedFormat('d M Y') ?? '—' }}
                            &ndash;
                            {{ $activeTask->deadline_selesai?->locale('id')->translatedFormat('d M Y') ?? '—' }}
                        </span>
                    </div>
                    <div class="dsb-data-baris">
                        <span class="dsb-data-label"><i class="bi bi-hourglass-split"></i>Sisa waktu</span>
                        <span class="dsb-data-nilai">
                            @if ($selesai)
                                <span style="color: #15803d;">
                                    {{ $activeTask->completed_at ? 'Rampung '.$activeTask->completed_at->locale('id')->translatedFormat('d M Y') : 'Sudah rampung' }}
                                </span>
                            @elseif ($lewatTenggat)
                                <span style="color: #b91c1c;">Lewat {{ abs($sisaHari ?? 0) }} hari</span>
                            @elseif ($sisaHari === 0)
                                <span style="color: #b45309;">Jatuh tempo hari ini</span>
                            @elseif ($sisaHari !== null)
                                {{ $sisaHari }} hari lagi
                            @else
                                —
                            @endif
                        </span>
                    </div>
                </div>

                @if($activeTask->attachments->count())
                    <div class="ts-bagian">
                        <span class="ts-bagian-judul"><i class="bi bi-paperclip"></i>Lampiran</span>
                        <div class="ts-lampiran">
                            @foreach($activeTask->attachments as $att)
                                @if($att->isImage())
                                    <a href="javascript:void(0)" role="button" class="ts-img-zoom ts-lampiran-gambar"
                                        data-img-url="{{ Storage::url($att->path) }}" title="Perbesar gambar">
                                        <img src="{{ Storage::url($att->path) }}" alt="Lampiran">
                                    </a>
                                @else
                                    <a href="{{ Storage::url($att->path) }}" target="_blank" rel="noopener" class="ts-lampiran-berkas">
                                        <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-file-earmark-text"></i></span>
                                        <span>{{ Str::limit($att->name, 22) }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($activeIsSolo)
                    <div class="ts-bagian">
                        @include('livewire.pages.admin.task.partials.discussion', ['activeTask' => $activeTask])
                    </div>
                @endif
            </div>

            @php
                $isOwner = $activeTask->user_id === auth()->id();
                $isPemberi = $activeTask->assigned_by === auth()->id();
                $canManageActive = $activeTask->assigned_by && in_array($activeTask->assigned_by, $manageGiverIds);
                $lewat = $activeTask->isLewatDeadline();
            @endphp
            <div class="dsb-jendela-kaki">
                @if (! $isOwner)
                    <span class="dsb-jendela-kaki-ket">
                        <i class="bi bi-eye"></i>
                        <span>{{ $isPemberi ? 'Anda pemberi task ini' : ($canManageActive ? 'Anda atasan pemberinya' : 'Anda memantau task ini') }} — bisa memantau progres dan memberi komentar.</span>
                    </span>
                    @if ($canManageActive && $locked)
                        <span class="dsb-jendela-aksi">
                            <button type="button" wire:click="openReopen('{{ $activeTask->id }}')" class="dsb-tombol is-kuning is-penuh-sempit">
                                <i class="bi bi-arrow-counterclockwise"></i><span>Buka Kembali</span>
                            </button>
                        </span>
                    @endif
                @elseif ($locked)
                    <span class="dsb-jendela-kaki-ket">
                        <i class="bi bi-lock-fill"></i>
                        <span>{{ $selesai ? 'Task sudah selesai — statusnya terkunci.' : 'Melewati tenggat → Tidak Selesai. Statusnya terkunci.' }}</span>
                    </span>
                @else
                    <span class="dsb-jendela-kaki-ket">
                        <i class="bi {{ $lewat ? 'bi-alarm-fill' : 'bi-info-circle' }}"></i>
                        <span>{{ $lewat ? 'Sudah melebihi tenggat — bonus berkurang bila diselesaikan sekarang.' : 'Perbarui status task Anda.' }}</span>
                    </span>
                    <span class="dsb-jendela-aksi">
                        @if ($activeTask->progress !== 'dikerjakan')
                            <button type="button" wire:click="mulaiKerjakan('{{ $activeTask->id }}')" class="dsb-tombol is-biru is-penuh-sempit">
                                <i class="bi bi-play-circle"></i><span>Mulai Kerjakan</span>
                            </button>
                        @endif

                        @if ($activeTask->progress === 'dikerjakan')
                            @if ($lewat)
                                <button type="button" data-id="{{ $activeTask->id }}" class="dsb-tombol is-kuning is-penuh-sempit ts-selesai-late-btn">
                                    <i class="bi bi-alarm-fill"></i><span>Tandai Selesai (Terlambat)</span>
                                </button>
                            @else
                                <button type="button" data-id="{{ $activeTask->id }}" class="dsb-tombol is-hijau is-penuh-sempit ts-selesai-btn">
                                    <i class="bi bi-check2-circle"></i><span>Tandai Selesai</span>
                                </button>
                            @endif
                        @else
                            {{-- Tetap ditampilkan, bukan disembunyikan: tombol yang hilang
                                 membuat orang mengira fiturnya tidak ada. Yang dijelaskan
                                 adalah SYARATNYA. --}}
                            <button type="button" disabled class="dsb-tombol is-lembut is-penuh-sempit" title="Tekan 'Mulai Kerjakan' lebih dulu">
                                <i class="bi bi-lock-fill"></i><span>Tandai Selesai</span>
                            </button>
                        @endif
                    </span>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Modal Buka Kembali (revisi) — pemberi task ===== --}}
    @if($showReopenModal && $reopenTask)
    <div class="ts-modal-back" wire:click="$set('showReopenModal', false)"></div>
    <div class="ts-modal">
        <div class="ts-modal-card dsb is-datar" role="dialog" aria-modal="true" tabindex="-1"
            data-tutup="showReopenModal" aria-label="Buka kembali task">
            <div class="dsb-jendela-kepala">
                <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-arrow-counterclockwise"></i></span>
                <span class="dsb-jendela-teks">
                    <h5 class="dsb-jendela-judul">Buka Kembali Task</h5>
                    <span class="dsb-kartu-sub">Task yang terkunci dibuka lagi untuk direvisi</span>
                </span>
                <button type="button" class="dsb-jendela-tutup" wire:click="$set('showReopenModal', false)" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="dsb-jendela-isi">
                <p class="text-muted mb-3" style="font-size:.9rem;">
                    <b class="text-dark">{{ $reopenTask->nama }}</b> akan diaktifkan kembali menjadi
                    <span class="badge bg-info-subtle text-info border border-info rounded-pill">Dikerjakan</span>
                    agar bawahan bisa mengerjakan revisi. Alasan di bawah dikirim sebagai komentar &amp; notifikasi.
                </p>
                <div class="mb-3">
                    <label class="dsb-label">Alasan revisi <span class="dsb-wajib">*</span></label>
                    <textarea wire:model="reopen_alasan" rows="2" class="dsb-isian @error('reopen_alasan') is-galat @enderror" placeholder="Mis. Ada bug pada fitur login / revisi tanda tangan surat"></textarea>
                    @error('reopen_alasan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    @if($reopenTask->category && $reopenTask->category->labels->count())
                    <div class="col-md-6">
                        <label class="dsb-label">Label baru <span class="dsb-label-ket">— opsional</span></label>
                        <select wire:model="reopen_label_id" class="dsb-isian">
                            <option value="">— Tanpa label —</option>
                            @foreach($reopenTask->category->labels as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label class="dsb-label">Deadline baru <span class="dsb-wajib">*</span></label>
                        <input type="date" wire:model="reopen_deadline" class="dsb-isian @error('reopen_deadline') is-galat @enderror">
                        @error('reopen_deadline')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            {{-- Kaki jendela, bukan ujung badannya: tombol keputusan harus tetap
                 terlihat walau isinya digulung panjang. --}}
            <div class="dsb-jendela-kaki" style="justify-content: flex-end;">
                <button type="button" class="dsb-tombol is-lembut" wire:click="$set('showReopenModal', false)"><span>Batal</span></button>
                <button type="button" class="dsb-tombol is-kuning" wire:click="bukaKembali">
                    <i class="bi bi-arrow-counterclockwise" style="display:inline-flex;align-items:center;line-height:1;"></i> Buka Kembali
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Modal Diskusi Grup (komentar dipusatkan di folder) ===== --}}
    @if($showGroupChat && $activeTask)
    <div class="ts-modal-back" wire:click="$set('showGroupChat', false)"></div>
    <div class="ts-modal">
        <div class="ts-modal-card dsb is-datar" role="dialog" aria-modal="true" tabindex="-1"
            data-tutup="showGroupChat" aria-label="Diskusi grup">
            <div class="dsb-jendela-kepala">
                <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-chat-dots-fill"></i></span>
                <span class="dsb-jendela-teks">
                    <h5 class="dsb-jendela-judul">Diskusi Grup</h5>
                    <span class="dsb-kartu-sub">Satu kolom komentar untuk seluruh penerima task ini</span>
                </span>
                <button type="button" class="dsb-jendela-tutup" wire:click="$set('showGroupChat', false)" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="dsb-jendela-isi">
                @include('livewire.pages.admin.task.partials.discussion', ['activeTask' => $activeTask, 'mentionMembers' => $chatMembers])
            </div>
        </div>
    </div>
    @endif

    @include('livewire.layout.sweetalert')

    @push('scripts')
    <script>
        // Popup glossy untuk memperbesar lampiran/komentar gambar (seragam dgn fitur lain,
        // center & tanpa scroll). Di-bind sekali via guard.
        if (!window.__tsImgZoomBound) {
            window.__tsImgZoomBound = true;
            document.addEventListener('click', function (e) {
                const trigger = e.target.closest && e.target.closest('.ts-img-zoom');
                if (!trigger) return;
                e.preventDefault();
                const url = trigger.getAttribute('data-img-url');
                if (!url) return;
                if (typeof Swal === 'undefined') { window.open(url, '_blank'); return; }
                Swal.fire({
                    html: '<div style="display:flex; align-items:center; justify-content:center; width:100%;"><img src="' + url + '" alt="Gambar" style="max-width:88vw; max-height:82vh; width:auto; height:auto; object-fit:contain; border-radius:12px;"></div>',
                    background: 'rgba(255, 255, 255, 0.92)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0' },
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: 'auto',
                    padding: '1rem',
                });
            });
        }

        // Widget @mention untuk composer diskusi grup (Alpine).
        window.tsMention = function (members) {
            return {
                members: members || [],
                open: false,
                query: '',
                active: 0,
                get filtered() {
                    const q = this.query.toLowerCase();
                    return this.members.filter(m => m.toLowerCase().includes(q)).slice(0, 6);
                },
                onInput() {
                    if (!this.members.length) { this.open = false; return; }
                    const ta = this.$refs.ta;
                    const before = ta.value.slice(0, ta.selectionStart);
                    const m = before.match(/@([\p{L}\p{N}_]*)$/u);
                    if (m) { this.query = m[1]; this.active = 0; this.open = this.filtered.length > 0; }
                    else { this.open = false; }
                },
                onKeydown(e) {
                    if (!this.open) return;
                    if (e.key === 'ArrowDown') { e.preventDefault(); this.active = Math.min(this.active + 1, this.filtered.length - 1); }
                    else if (e.key === 'ArrowUp') { e.preventDefault(); this.active = Math.max(this.active - 1, 0); }
                    else if (e.key === 'Enter' && this.filtered.length) { e.preventDefault(); this.pick(this.filtered[this.active]); }
                    else if (e.key === 'Escape') { this.open = false; }
                },
                pick(name) {
                    const ta = this.$refs.ta;
                    const pos = ta.selectionStart;
                    const before = ta.value.slice(0, pos).replace(/@([\p{L}\p{N}_]*)$/u, '@' + name + ' ');
                    const after = ta.value.slice(pos);
                    ta.value = before + after;
                    ta.dispatchEvent(new Event('input')); // sync ke wire:model
                    this.open = false;
                    this.$nextTick(() => { ta.focus(); ta.setSelectionRange(before.length, before.length); });
                }
            };
        };

        if (!window.__tsConfirmBound) {
            window.__tsConfirmBound = true;
            const glossyConfig = {
                background: 'rgba(255, 255, 255, 0.8)', backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel', title: 'swal-glossy-title' },
                buttonsStyling: false
            };
            document.addEventListener('click', function (event) {
                const b = event.target.closest('.ts-selesai-btn');
                if (!b) return;
                event.preventDefault();
                const c = b.closest('[wire\\:id]'); if (!c) return;
                const id = b.getAttribute('data-id');
                Swal.fire({
                    title: 'Tandai task selesai?',
                    text: 'Waktu penyelesaian dicatat sekarang & status akan terkunci.',
                    icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, selesai!', cancelButtonText: 'Batal', ...glossyConfig
                }).then(r => { if (r.isConfirmed) Livewire.find(c.getAttribute('wire:id')).call('tandaiSelesai', id); });
            });

            // Tandai selesai MELEBIHI deadline (bonus dikurangi).
            document.addEventListener('click', function (event) {
                const b = event.target.closest('.ts-selesai-late-btn');
                if (!b) return;
                event.preventDefault();
                const c = b.closest('[wire\\:id]'); if (!c) return;
                const id = b.getAttribute('data-id');
                Swal.fire({
                    title: 'Selesaikan melebihi deadline?',
                    text: 'Task ini sudah lewat deadline. Bonusnya akan dikurangi otomatis (sesuai bobot). Lanjutkan?',
                    icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, tandai selesai', cancelButtonText: 'Batal', ...glossyConfig
                }).then(r => { if (r.isConfirmed) Livewire.find(c.getAttribute('wire:id')).call('tandaiSelesai', id); });
            });
        }

        // ===== Popup picker Bawahan / Kategori / Label (Select2-style) =====
        window.__tsBawahan = @json($bawahan->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])->values());

        const tsPickGlossy = {
            background: 'rgba(255, 255, 255, 0.92)', backdrop: 'rgba(139, 92, 246, 0.15)',
            customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
            buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem'
        };

        if (!window.__tsBawahanPickerBound) {
            window.__tsBawahanPickerBound = true;
            window.tsBawahanPicker = function (btn) {
                if (typeof Swal === 'undefined') return;
                const comp = btn.closest('[wire\\:id]'); if (!comp) return;
                const cid = comp.getAttribute('wire:id');
                const items = window.__tsBawahan || [];
                const rows = items.length
                    ? items.map(it => `<button type="button" class="of-pick-item" data-id="${it.id}" data-search="${it.name.toLowerCase()}">${it.name}</button>`).join('')
                    : '<div class="of-pick-empty">Tidak ada bawahan</div>';
                Swal.fire({
                    title: 'Pilih Bawahan',
                    html: `<input id="tsPickSearch" class="form-control mb-2" placeholder="Ketik untuk mencari...">
                           <div id="tsPickList" class="of-pick-list">${rows}</div>`,
                    ...tsPickGlossy,
                    didOpen: () => {
                        const search = document.getElementById('tsPickSearch');
                        const listEl = document.getElementById('tsPickList');
                        if (search) {
                            search.addEventListener('input', () => {
                                const q = search.value.toLowerCase();
                                listEl.querySelectorAll('.of-pick-item').forEach(b => { b.style.display = b.dataset.search.includes(q) ? '' : 'none'; });
                            });
                            setTimeout(() => search.focus(), 100);
                        }
                        listEl.querySelectorAll('.of-pick-item').forEach(b => {
                            b.addEventListener('click', () => {
                                Livewire.find(cid).set('t_user_id', b.dataset.id);
                                Swal.close();
                            });
                        });
                    }
                });
            };
        }

        if (!window.__tsCatPickerBound) {
            window.__tsCatPickerBound = true;

            function tsData() {
                const el = document.getElementById('tsPickData');
                if (!el) return { categories: [], labels: [] };
                try {
                    return {
                        categories: JSON.parse(el.dataset.categories || '[]'),
                        labels: JSON.parse(el.dataset.labels || '[]'),
                    };
                } catch (e) { return { categories: [], labels: [] }; }
            }

            function tsRows(items) {
                if (!items.length) return '<div class="of-pick-empty">Belum ada data. Tambah di bawah.</div>';
                return items.map(it => `
                    <div class="of-pick-row" data-row="${it.id}">
                        <button type="button" class="of-pick-item" data-id="${it.id}" data-search="${it.name.toLowerCase()}">${it.name}</button>
                        <button type="button" class="of-pick-del" data-del="${it.id}" title="Hapus"><i class="bi bi-trash"></i></button>
                    </div>`).join('');
            }

            function tsEntityPicker(btn, cfg) {
                if (typeof Swal === 'undefined') return;
                const comp = btn.closest('[wire\\:id]'); if (!comp) return;
                const cid = comp.getAttribute('wire:id');
                const lw = () => Livewire.find(cid);

                Swal.fire({
                    title: cfg.title,
                    html: `
                        <input id="tsCatSearch" class="form-control mb-2" placeholder="Cari...">
                        <div id="tsCatList" class="of-pick-list">${tsRows(cfg.items())}</div>
                        <div class="of-pick-add mt-3">
                            <input id="tsCatNew" class="form-control" placeholder="${cfg.addPlaceholder}">
                            <button type="button" id="tsCatAdd" class="btn btn-primary of-pick-addbtn"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
                        </div>
                        <div id="tsCatMsg" class="of-pick-msg"></div>`,
                    ...tsPickGlossy,
                    didOpen: () => {
                        const listEl = document.getElementById('tsCatList');
                        const search = document.getElementById('tsCatSearch');
                        const newInp = document.getElementById('tsCatNew');
                        const addBtn = document.getElementById('tsCatAdd');
                        const msg = document.getElementById('tsCatMsg');

                        const applyFilter = () => {
                            const q = (search.value || '').toLowerCase();
                            listEl.querySelectorAll('.of-pick-row').forEach(row => {
                                const item = row.querySelector('.of-pick-item');
                                row.style.display = (item && item.dataset.search.includes(q)) ? '' : 'none';
                            });
                        };

                        const confirmDelete = (row, id) => {
                            if (!row) return;
                            const original = row.innerHTML;
                            const restore = () => { row.innerHTML = original; wireRow(row, id); };
                            row.innerHTML = `<div class="of-pick-confirm">
                                <span>Hapus item ini?</span>
                                <button type="button" class="btn btn-sm btn-danger of-pick-yes">Ya</button>
                                <button type="button" class="btn btn-sm btn-light of-pick-no">Batal</button></div>`;
                            row.querySelector('.of-pick-no').addEventListener('click', restore);
                            row.querySelector('.of-pick-yes').addEventListener('click', async () => {
                                await cfg.del(lw(), id);
                                rebuild();
                            });
                        };

                        function wireRow(row, id) {
                            row.querySelector('.of-pick-item')?.addEventListener('click', () => { cfg.pick(lw(), id); Swal.close(); });
                            row.querySelector('.of-pick-del')?.addEventListener('click', () => confirmDelete(row, id));
                        }

                        function rebuild() {
                            listEl.innerHTML = tsRows(cfg.items());
                            listEl.querySelectorAll('.of-pick-row').forEach(row => wireRow(row, row.dataset.row));
                            applyFilter();
                        }

                        search.addEventListener('input', applyFilter);
                        setTimeout(() => search.focus(), 100);

                        addBtn.addEventListener('click', async () => {
                            const name = (newInp.value || '').trim();
                            msg.textContent = '';
                            if (!name) { msg.textContent = 'Nama tidak boleh kosong.'; return; }
                            if (cfg.items().some(it => it.name.toLowerCase() === name.toLowerCase())) {
                                msg.textContent = 'Nama tersebut sudah ada.'; return;
                            }
                            await cfg.add(lw(), name);
                            Swal.close(); // item baru otomatis terpilih
                        });
                        newInp.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); addBtn.click(); } });

                        listEl.querySelectorAll('.of-pick-row').forEach(row => wireRow(row, row.dataset.row));
                    }
                });
            }

            window.tsKategoriPicker = function (btn) {
                tsEntityPicker(btn, {
                    title: 'Pilih Kategori',
                    addPlaceholder: 'Kategori baru, mis. Parafrase',
                    items: () => tsData().categories,
                    pick: (lw, id) => lw.set('t_category_id', id),
                    add: (lw, name) => { lw.set('newCategoryName', name, false); return lw.call('addCategory'); },
                    del: (lw, id) => lw.call('deleteCategory', id),
                });
            };

            window.tsLabelPicker = function (btn) {
                tsEntityPicker(btn, {
                    title: 'Pilih Label',
                    addPlaceholder: 'Label baru, mis. Bug',
                    items: () => tsData().labels,
                    pick: (lw, id) => lw.set('t_label_id', id),
                    add: (lw, name) => { lw.set('newLabelName', name, false); return lw.call('addLabel'); },
                    del: (lw, id) => lw.call('deleteLabel', id),
                });
            };
        }

        /* Kunci gulung halaman selama ada jendela terbuka.

           Dipasang sebagai PENGAMAT, bukan ditempel di tiap tombol buka/tutup:
           jendela di layar ini dibuka dan ditutup oleh Livewire (dan bisa juga
           oleh tombol Esc, klik latar, atau perpindahan halaman), jadi satu-
           satunya tempat yang pasti tahu keadaannya adalah DOM itu sendiri. */
        (function () {
            const badan = document.body;

            const lebarBilah = () => window.innerWidth - document.documentElement.clientWidth;

            // Elemen yang dipegang fokus SEBELUM jendela terbuka, supaya bisa
            // dikembalikan saat ditutup. Tanpa itu, pengguna papan ketik
            // mendarat di awal halaman tiap kali menutup satu task.
            let fokusSebelumnya = null;

            const perbarui = () => {
                const jendela = document.querySelector('.ts-modal [role="dialog"]');
                const adaJendela = !!jendela;
                if (adaJendela === badan.classList.contains('ts-terkunci')) return;

                if (adaJendela) {
                    const bilah = lebarBilah();
                    badan.classList.add('ts-terkunci');
                    // Tanpa ini, hilangnya bilah gulung melebarkan halaman dan
                    // seluruh isinya bergeser beberapa piksel saat jendela dibuka.
                    if (bilah > 0) badan.style.paddingRight = bilah + 'px';

                    fokusSebelumnya = document.activeElement;
                    // Fokus ke jendelanya, bukan ke tombol pertamanya: pembaca
                    // layar lalu membacakan judul dialognya lebih dulu.
                    jendela.focus({ preventScroll: true });
                } else {
                    badan.classList.remove('ts-terkunci');
                    badan.style.paddingRight = '';

                    if (fokusSebelumnya && document.contains(fokusSebelumnya)) {
                        fokusSebelumnya.focus({ preventScroll: true });
                    }
                    fokusSebelumnya = null;
                }
            };

            /* Esc menutup jendela teratas.

               Ditulis di sini, bukan di tiap jendela: keempat jendela di layar
               ini ditutup lewat properti Livewire yang berbeda-beda, dan
               nama propertinya sudah dibawa tiap kartu lewat data-tutup. */
            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;

                const jendela = [...document.querySelectorAll('.ts-modal [data-tutup]')].pop();
                if (!jendela) return;

                const akar = jendela.closest('[wire\\:id]');
                if (!akar || !window.Livewire) return;

                e.preventDefault();
                window.Livewire.find(akar.getAttribute('wire:id'))?.set(jendela.dataset.tutup, false);
            });

            perbarui();
            new MutationObserver(perbarui).observe(document.documentElement, { childList: true, subtree: true });

            // Berpindah halaman lewat wire:navigate tidak selalu melepas kelasnya
            // sendiri — dan halaman berikutnya lalu tidak bisa digulung sama sekali.
            document.addEventListener('livewire:navigating', () => {
                badan.classList.remove('ts-terkunci');
                badan.style.paddingRight = '';
            });
        })();

        // Bersihkan ?open_task dari URL agar hard refresh tidak membuka popup lagi.
        (function () {
            if (window.location.search.includes('open_task')) {
                const url = new URL(window.location.href);
                url.searchParams.delete('open_task');
                window.history.replaceState({}, '', url.pathname + (url.search ? url.search : ''));
            }
        })();
    </script>
    @endpush
</div>
