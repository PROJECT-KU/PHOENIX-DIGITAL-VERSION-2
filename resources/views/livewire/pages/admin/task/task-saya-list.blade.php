@section('title')
Task Saya || PT. Asthana Cipta Mandiri
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
        .ts-modal { position: fixed; inset: 0; z-index: 1056; display: flex; align-items: flex-start; justify-content: center; padding: 4vh 12px; overflow-y: auto; }
        .ts-modal-card { background: #fff; border-radius: 22px; width: 100%; max-width: 560px; box-shadow: 0 30px 70px rgba(15, 23, 42, .32); overflow: hidden; }
        .ts-modal-head { padding: 22px 24px; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; position: relative; }
        .ts-modal-head h5, .ts-modal-head small, .ts-modal-head .badge { color: #fff; }
        .ts-modal-head .btn-close { filter: invert(1) grayscale(1) brightness(2); opacity: .9; position: absolute; top: 18px; right: 20px; }

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
        .ts-mentioned-badge { background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; box-shadow: 0 3px 8px rgba(124, 58, 237, .3); }
        .ts-mentioned-badge i.bi { display: inline-flex; align-items: center; line-height: 1; }

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
        .ts-folder-chev { color: #94a3b8; display: inline-flex; align-items: center; line-height: 1; }
        .ts-folder-body { padding: 4px 14px 14px; }
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
        .ts-multi { border: 1px solid #e6e8f2; border-radius: 12px; padding: 6px; max-height: 190px; overflow-y: auto; display: flex; flex-direction: column; gap: 3px; background: #fff; }
        .ts-multi.is-invalid { border-color: #ef4444; }
        .ts-multi-item { display: flex; align-items: center; gap: 9px; padding: 7px 10px; border-radius: 9px; cursor: pointer; font-size: .9rem; color: #1e293b; font-weight: 500; transition: .12s; margin: 0; }
        .ts-multi-item:hover { background: #f7f5ff; }
        .ts-multi-item.checked { background: linear-gradient(135deg, rgba(124,58,237,.10), rgba(78,70,229,.05)); }
        .ts-multi-item input { position: absolute; opacity: 0; pointer-events: none; }
        .ts-multi-check { width: 20px; height: 20px; border-radius: 6px; border: 1.5px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff; background: #fff; transition: .12s; }
        .ts-multi-check i.bi { display: none; line-height: 1; font-size: .8rem; }
        .ts-multi-item.checked .ts-multi-check { background: linear-gradient(135deg, #7c3aed, #4e46e5); border-color: transparent; }
        .ts-multi-item.checked .ts-multi-check i.bi { display: inline-flex; }
        .ts-multi-count { font-size: .76rem; color: #7c3aed; font-weight: 600; margin-top: 5px; }
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
    </style>

    @php
        $badgeBobot = ['ringan'=>'success','sedang'=>'warning','berat'=>'danger'];
        $badgeProg = ['belum'=>'secondary','dikerjakan'=>'info','selesai'=>'success'];
        $labelProg = ['belum'=>'Belum Dikerjakan','dikerjakan'=>'Dikerjakan','selesai'=>'Selesai'];
        $badgeBonus = ['tepat_waktu'=>'success','terlambat'=>'warning','tidak_selesai'=>'danger','tidak_ada_info'=>'primary'];
        $labelBonus = ['tepat_waktu'=>'Tepat Waktu','terlambat'=>'Terlambat','tidak_selesai'=>'Tidak Selesai','tidak_ada_info'=>'Berjalan'];

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

    <div class="container-fluid">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h3 class="gradient-text fw-bold mb-1">Task Saya</h3>
                    <p class="text-muted mb-0 small">Daftar task Anda beserta deadline &amp; statusnya. Klik kartu untuk detail, komentar, dan unggah file.</p>
                </div>
                @if($canAssign)
                <div class="header-action d-flex flex-shrink-0">
                    <button type="button" wire:click="openCreateTask"
                        class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                        <i class="bi bi-plus-lg"></i>
                        <span class="ms-2">Beri Task ke Bawahan</span>
                    </button>
                </div>
                @endif
            </div>
        </div>

        {{-- Filter Periode (pola sama seperti Pengeluaran) --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 px-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                        <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0" style="width:40px;height:40px;font-size:1.1rem;border-radius:12px;">
                            <i class="bi bi-funnel" style="display:flex;align-items:center;justify-content:center;line-height:1;"></i>
                        </span>
                        <span>Filter Periode</span>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <select wire:model.live="bulan" class="form-select rounded-3" style="min-width:160px;">
                            <option value="">Semua Bulan</option>
                            @foreach($daftarBulan as $num => $nama)
                            <option value="{{ $num }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="tahun" class="form-select rounded-3" style="min-width:130px;">
                            <option value="">Semua Tahun</option>
                            @foreach($daftarTahun as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                            @endforeach
                        </select>
                        @if($bulan || $tahun)
                        <button wire:click="resetFilter" type="button" class="btn btn-danger rounded-3" title="Reset filter">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Kelompokkan per grup: >1 penerima = FOLDER berisi sub-card; 1 = card biasa. --}}
            @forelse($tasks->groupBy('group_id') as $gid => $gtasks)
                @if($gtasks->count() > 1)
                    @include('livewire.pages.admin.task.partials.task-folder', ['gtasks' => $gtasks])
                @else
                    @include('livewire.pages.admin.task.partials.task-card', ['task' => $gtasks->first()])
                @endif
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 ts-empty-card">
                    <div class="card-body ts-empty text-center">
                        <div class="ts-empty-badge mb-3"><i class="bi {{ ($bulan || $tahun) ? 'bi-calendar-x' : 'bi-clipboard-check' }}"></i></div>
                        <h5 class="fw-bold mb-2">{{ ($bulan || $tahun) ? 'Tidak Ada Task di Periode Ini' : 'Belum Ada Task' }}</h5>
                        <p class="text-muted mb-0" style="font-size:.95rem;">
                            @if($bulan || $tahun)
                            Tidak ada task pada bulan &amp; tahun yang dipilih. Coba ganti periode di filter atas, atau tampilkan semua.
                            @else
                            Task yang ditugaskan kepada Anda akan muncul di sini.
                            @endif
                        </p>
                        @if($bulan || $tahun)
                        <button type="button" wire:click="resetFilter" class="btn btn-sm rounded-pill px-4 mt-3 ts-empty-btn"><i class="bi bi-arrow-counterclockwise me-1"></i>Tampilkan Semua Periode</button>
                        @endif
                    </div>
                </div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ===== Modal beri/edit task ke bawahan ===== --}}
    @if($showTaskModal)
    <div class="ts-modal-back" wire:click="$set('showTaskModal', false)"></div>
    <div class="ts-modal">
        <div class="ts-modal-card" style="max-width:600px;">
            <div class="ts-modal-head">
                <button type="button" class="btn-close" wire:click="$set('showTaskModal', false)"></button>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-{{ $editingTaskId ? 'pencil-square' : 'plus-circle' }}" style="display:inline-flex;align-items:center;line-height:1;font-size:1.15rem;"></i>
                    <h5 class="fw-bold mb-0">{{ $editingTaskId ? 'Edit Task' : 'Beri Task ke Bawahan' }}</h5>
                </div>
            </div>
            <div class="p-4">
                <div class="mb-3">
                    <label class="ts-form-label">Penerima <span class="text-danger">*</span>
                        <span class="text-muted fw-normal" style="font-size:.8rem;">— bisa pilih lebih dari satu</span>
                    </label>
                    <div class="ts-multi @error('t_user_ids') is-invalid @enderror">
                        @forelse($bawahan as $b)
                        <label class="ts-multi-item {{ in_array((string) $b->id, array_map('strval', $t_user_ids)) ? 'checked' : '' }}">
                            <input type="checkbox" value="{{ $b->id }}" wire:model.live="t_user_ids">
                            <span class="ts-multi-check"><i class="bi bi-check-lg"></i></span>
                            <span>{{ $b->name }}</span>
                        </label>
                        @empty
                        <div class="text-muted small p-2">Tidak ada bawahan.</div>
                        @endforelse
                    </div>
                    <div class="ts-multi-count">{{ count($t_user_ids) }} penerima dipilih</div>
                    @error('t_user_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('t_user_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="ts-form-label">Nama Task <span class="text-danger">*</span></label>
                    <input type="text" wire:model="t_nama" class="form-control rounded-3 @error('t_nama') is-invalid @enderror" placeholder="Mis. Susun laporan mingguan">
                    @error('t_nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="ts-form-label">Deskripsi</label>
                    <textarea wire:model="t_deskripsi" rows="2" class="form-control rounded-3" placeholder="Rincian task (opsional)"></textarea>
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
                        <label class="ts-form-label">Kategori</label>
                        <button type="button" onclick="tsKategoriPicker(this)" class="form-select text-start of-picker-btn rounded-3">
                            @if($selCat)<span class="text-dark">{{ $selCat->nama }}</span>
                            @else<span class="text-muted">Pilih kategori</span>@endif
                        </button>
                    </div>
                    @if($t_category_id)
                    <div class="col-md-6">
                        <label class="ts-form-label">Label <span class="text-muted fw-normal" style="font-size:.8rem;">— mis. bug / improvement</span></label>
                        <button type="button" onclick="tsLabelPicker(this)" class="form-select text-start of-picker-btn rounded-3">
                            @if($selLab)<span class="text-dark">{{ $selLab->nama }}</span>
                            @else<span class="text-muted">Pilih label</span>@endif
                        </button>
                    </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="ts-form-label">Lampiran <span class="text-muted fw-normal" style="font-size:.8rem;">— gambar/file, bisa banyak (maks 2 MB)</span></label>
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
                            <a href="{{ Storage::url($att->path) }}" target="_blank" class="d-block text-decoration-none">
                                <div class="media">@if($att->isImage())<img src="{{ Storage::url($att->path) }}" alt="">@else<i class="bi bi-file-earmark-text"></i>@endif</div>
                            </a>
                            <div class="cap">{{ $att->name }}</div>
                            <button type="button" class="rm" wire:click="removeAttachment('{{ $att->id }}')" title="Hapus"><i class="bi bi-x"></i></button>
                        </div>
                        @endforeach
                        @endif
                        @foreach($t_files as $i => $file)
                        @php $isImg = str_starts_with((string) $file->getMimeType(), 'image/'); @endphp
                        <div class="ts-thumb">
                            <div class="media">@if($isImg)<img src="{{ $file->temporaryUrl() }}" alt="">@else<i class="bi bi-file-earmark-arrow-up"></i>@endif</div>
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
                        <label class="ts-form-label">Bobot</label>
                        <select wire:model="t_bobot" class="form-select rounded-3">
                            <option value="ringan">Ringan (1)</option>
                            <option value="sedang">Sedang (2)</option>
                            <option value="berat">Berat (3)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="ts-form-label">Deadline Mulai</label>
                        <input type="date" wire:model="t_deadline_mulai" class="form-control rounded-3 @error('t_deadline_mulai') is-invalid @enderror">
                        @error('t_deadline_mulai')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="ts-form-label">Deadline Selesai</label>
                        <input type="date" wire:model="t_deadline_selesai" class="form-control rounded-3 @error('t_deadline_selesai') is-invalid @enderror">
                        @error('t_deadline_selesai')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="px-4 pb-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-danger rounded-pill px-4" wire:click="$set('showTaskModal', false)">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-2"
                    wire:click="saveTask" wire:loading.attr="disabled" wire:target="saveTask">
                    <i class="bi bi-check2-circle" style="display:inline-flex;align-items:center;line-height:1;"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Modal detail task ===== --}}
    @if($showModal && $activeTask)
    @php $locked = $activeTask->isLocked(); $bs = $activeTask->bonusStatus(); @endphp
    <div class="ts-modal-back" wire:click="$set('showModal', false)"></div>
    <div class="ts-modal">
        <div class="ts-modal-card">
            <div class="ts-modal-head">
                <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                <h5 class="fw-bold mb-2" style="max-width: 90%;">{{ $activeTask->nama }}</h5>
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="badge bg-light text-dark rounded-pill">{{ $labelProg[$activeTask->progress] ?? ucfirst($activeTask->progress) }}</span>
                    <span class="badge bg-{{ $badgeBonus[$bs] ?? 'secondary' }} rounded-pill border border-light">{{ $labelBonus[$bs] ?? $bs }}</span>
                    @if($locked)<span class="badge bg-dark bg-opacity-25 rounded-pill"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>@endif
                </div>
            </div>
            <div class="p-4">
                @if($activeTask->deskripsi)<p class="text-muted mb-3" style="font-size:.9rem;">{{ $activeTask->deskripsi }}</p>@endif

                <div class="d-flex align-items-center gap-3 mb-3 flex-wrap" style="font-size:.85rem;">
                    <span><i class="bi bi-calendar-range me-1 text-primary"></i>{{ $activeTask->deadline_mulai?->translatedFormat('d M Y') }} – {{ $activeTask->deadline_selesai?->translatedFormat('d M Y') }}</span>
                    <span class="text-capitalize"><i class="bi bi-bar-chart me-1 text-primary"></i>Bobot: {{ $activeTask->bobot }}</span>
                </div>

                {{-- Lampiran --}}
                @if($activeTask->attachments->count())
                <div class="mb-3">
                    <div class="ts-section-lbl"><i class="bi bi-paperclip me-1"></i>Lampiran</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($activeTask->attachments as $att)
                        @if($att->isImage())
                        <a href="{{ Storage::url($att->path) }}" target="_blank"><img src="{{ Storage::url($att->path) }}" style="width:58px;height:58px;object-fit:cover;border-radius:10px;"></a>
                        @else
                        <a href="{{ Storage::url($att->path) }}" target="_blank" class="border rounded-3 px-2 py-1 d-inline-flex align-items-center gap-1 text-decoration-none" style="font-size:.78rem;"><i class="bi bi-file-earmark"></i>{{ Str::limit($att->name, 18) }}</a>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Diskusi: solo -> inline; grup -> hanya di folder (bukan di sub-card) --}}
                @if($activeIsSolo)
                    @include('livewire.pages.admin.task.partials.discussion', ['activeTask' => $activeTask])
                @endif
            </div>

            {{-- Footer aksi status --}}
            @php $isOwner = $activeTask->user_id === auth()->id(); @endphp
            <div class="px-4 py-3 d-flex align-items-center justify-content-between gap-2 flex-wrap" style="background:#fbfcff; border-top:1px solid #eef0f7;">
                @if(! $isOwner)
                @php
                    $isPemberi = $activeTask->assigned_by === auth()->id();
                    $canManageActive = $activeTask->assigned_by && in_array($activeTask->assigned_by, $manageGiverIds);
                @endphp
                <span class="text-muted d-inline-flex align-items-center gap-2" style="font-size:.83rem;">
                    <i class="bi bi-eye"></i>
                    {{ $isPemberi ? 'Anda pemberi task ini' : ($canManageActive ? 'Anda atasan pemberi — dapat mengelola' : 'Anda memantau task ini') }} — memantau progres &amp; memberi komentar{{ ($canManageActive && $locked) ? ' & membuka kembali revisi' : '' }}.
                </span>
                @if($canManageActive && $locked)
                <button type="button" wire:click="openReopen('{{ $activeTask->id }}')"
                    class="btn btn-warning btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-counterclockwise" style="display:inline-flex;align-items:center;line-height:1;"></i> Buka Kembali
                </button>
                @endif
                @elseif($locked)
                <span class="text-muted d-inline-flex align-items-center gap-2" style="font-size:.83rem;">
                    <i class="bi bi-lock-fill"></i>
                    {{ $activeTask->progress==='selesai' ? 'Task sudah selesai — status terkunci.' : 'Melewati deadline → Tidak Selesai. Status terkunci.' }}
                </span>
                @else
                <span class="text-muted" style="font-size:.8rem;"><i class="bi bi-info-circle me-1"></i>Perbarui status task Anda</span>
                <div class="d-flex gap-2">
                    @if($activeTask->progress !== 'dikerjakan')
                    <button type="button" wire:click="mulaiKerjakan('{{ $activeTask->id }}')" class="btn btn-outline-info btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1"><i class="bi bi-play-circle"></i> Mulai Kerjakan</button>
                    @endif
                    @if($activeTask->progress === 'dikerjakan')
                    <button type="button" data-id="{{ $activeTask->id }}" class="btn btn-success btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 ts-selesai-btn"><i class="bi bi-check2-circle"></i> Tandai Selesai</button>
                    @else
                    <button type="button" disabled class="btn btn-success btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 opacity-50" style="cursor:not-allowed;" title="Klik 'Mulai Kerjakan' dulu"><i class="bi bi-lock-fill"></i> Tandai Selesai</button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ===== Modal Buka Kembali (revisi) — pemberi task ===== --}}
    @if($showReopenModal && $reopenTask)
    <div class="ts-modal-back" wire:click="$set('showReopenModal', false)"></div>
    <div class="ts-modal">
        <div class="ts-modal-card">
            <div class="ts-modal-head">
                <button type="button" class="btn-close" wire:click="$set('showReopenModal', false)"></button>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-counterclockwise" style="display:inline-flex;align-items:center;line-height:1;font-size:1.15rem;"></i>
                    <h5 class="fw-bold mb-0">Buka Kembali Task</h5>
                </div>
            </div>
            <div class="p-4">
                <p class="text-muted mb-3" style="font-size:.9rem;">
                    <b class="text-dark">{{ $reopenTask->nama }}</b> akan diaktifkan kembali menjadi
                    <span class="badge bg-info-subtle text-info border border-info rounded-pill">Dikerjakan</span>
                    agar bawahan bisa mengerjakan revisi. Alasan di bawah dikirim sebagai komentar &amp; notifikasi.
                </p>
                <div class="mb-3">
                    <label class="ts-form-label">Alasan revisi <span class="text-danger">*</span></label>
                    <textarea wire:model="reopen_alasan" rows="2" class="form-control rounded-3 @error('reopen_alasan') is-invalid @enderror" placeholder="Mis. Ada bug pada fitur login / revisi tanda tangan surat"></textarea>
                    @error('reopen_alasan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    @if($reopenTask->category && $reopenTask->category->labels->count())
                    <div class="col-md-6">
                        <label class="ts-form-label">Label baru <span class="text-muted fw-normal" style="font-size:.8rem;">— opsional</span></label>
                        <select wire:model="reopen_label_id" class="form-select rounded-3">
                            <option value="">— Tanpa label —</option>
                            @foreach($reopenTask->category->labels as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label class="ts-form-label">Deadline baru <span class="text-danger">*</span></label>
                        <input type="date" wire:model="reopen_deadline" class="form-control rounded-3 @error('reopen_deadline') is-invalid @enderror">
                        @error('reopen_deadline')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="px-4 pb-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-danger rounded-pill px-4" wire:click="$set('showReopenModal', false)">Batal</button>
                <button type="button" class="btn btn-warning rounded-pill px-4 d-inline-flex align-items-center gap-2" wire:click="bukaKembali">
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
        <div class="ts-modal-card">
            <div class="ts-modal-head">
                <button type="button" class="btn-close" wire:click="$set('showGroupChat', false)"></button>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-chat-dots" style="display:inline-flex;align-items:center;line-height:1;font-size:1.15rem;"></i>
                    <h5 class="fw-bold mb-0">Diskusi Grup</h5>
                </div>
                <small class="d-block mt-1" style="opacity:.85;">{{ $activeTask->nama }}</small>
            </div>
            <div class="p-4">
                @include('livewire.pages.admin.task.partials.discussion', ['activeTask' => $activeTask, 'mentionMembers' => $chatMembers])
            </div>
        </div>
    </div>
    @endif

    @include('livewire.layout.sweetalert')

    @push('scripts')
    <script>
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
