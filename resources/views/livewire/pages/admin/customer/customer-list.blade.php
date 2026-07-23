
@section('title')
Data Pelanggan || lemon
@stop
<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Manajemen Data Pelanggan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pelanggan']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>

                            <input wire:model.live.debounce.300ms="searchCustomer" type="text" class="form-control"
                                placeholder="ketik nama, no hp atau email pelanggan">

                            @if ($searchCustomer)
                            <span wire:click="$set('searchCustomer', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .customer-glossy-tabs {
                display: flex;
                width: 100%;
                gap: .5rem;
                padding: .5rem;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.55);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.6);
                box-shadow: 0 8px 24px rgba(108, 99, 255, 0.12);
                overflow-x: auto;
            }

            .customer-glossy-tab {
                flex: 1;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .6rem;
                border: none;
                background: transparent;
                color: #6b7280;
                font-weight: 600;
                font-size: 1.05rem;
                line-height: 1;
                padding: .95rem 1.5rem;
                border-radius: 999px;
                cursor: pointer;
                transition: all .25s ease;
                text-transform: capitalize;
                white-space: nowrap;
            }

            .customer-glossy-tab i {
                font-size: 1.25rem;
                line-height: 1;
                display: inline-flex;
                align-items: center;
            }

            .customer-glossy-tab:hover:not(.active) {
                color: #4e46e5;
                background: rgba(108, 99, 255, 0.10);
            }

            .customer-glossy-tab.active {
                color: #fff;
                background: linear-gradient(135deg, #6c63ff, #4e46e5);
                box-shadow: 0 6px 16px rgba(78, 70, 229, 0.45);
                transform: translateY(-1px);
            }

            .customer-glossy-tab .tab-count {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 1.75rem;
                height: 1.75rem;
                padding: 0 .55rem;
                font-size: .82rem;
                font-weight: 800;
                line-height: 1;
                border-radius: 999px;
                color: #fff;
                background: linear-gradient(135deg, #7c73ff, #4e46e5);
                border: 1px solid rgba(255, 255, 255, 0.45);
                box-shadow: 0 4px 10px rgba(78, 70, 229, 0.40), inset 0 1px 1px rgba(255, 255, 255, 0.45);
                transition: all .25s ease;
            }

            .customer-glossy-tab:hover:not(.active) .tab-count {
                transform: scale(1.08);
            }

            .customer-glossy-tab.active .tab-count {
                color: #4e46e5;
                background: linear-gradient(135deg, #ffffff, #eef0ff);
                border-color: rgba(255, 255, 255, 0.9);
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18), inset 0 1px 1px rgba(255, 255, 255, 0.9);
            }

            @media (max-width: 575.98px) {
                .customer-glossy-tab {
                    flex: 0 0 auto;
                    justify-content: center;
                    padding: .6rem .9rem;
                    font-size: .9rem;
                }
            }
        </style>

        @php
        $allCustomerCount = \App\Models\Customer::count();
        $memberCustomerCount = \App\Models\Customer::where('status_member', 'active')->count();
        @endphp

        <div class="mt-3 mb-3">
            <div class="customer-glossy-tabs">
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'all') active @endif"
                    wire:click="setTab('all')">
                    <i class="bi bi-people-fill"></i>
                    <span>Data Pelanggan</span>
                    <span class="tab-count">{{ $allCustomerCount }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'member') active @endif"
                    wire:click="setTab('member')">
                    <i class="bi bi-patch-check-fill"></i>
                    <span>Data Member</span>
                    <span class="tab-count">{{ $memberCustomerCount }}</span>
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Nama Pelanggan</th>
                                <th>Email Pelanggan</th>
                                <th>Nomor Handphone</th>
                                <th>Status Member</th>
                                <th>Jumlah Poin</th>
                                <th>Kode Referral</th>
                                @if (auth()->user()->hasAnyPermission(['edit_customer', 'delete_customer']))
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $customer)
                            <tr class="text-center">
                                <td>{{ $customer->nama }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->no_hp }}</td>
                                <td>
                                    <span
                                        class="badge {{ $customer->status_member === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($customer->status_member) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $customer->point }}</span> poin
                                    <small class="d-block text-muted">Rp {{ number_format($customer->point * 500, 0, ',', '.') }}</small>
                                    @if ($customer->point > 0)
                                        <small class="d-block text-danger">
                                            <i class="bi bi-clock-history"></i> Exp {{ $customer->pointsExpireLabel('d M Y') }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $customer->kode_ref }}</td>
                                <td>
                                    <div class="d-inline-flex flex-nowrap align-items-center justify-content-center gap-1">
                                    <button type="button" class="btn btn-success btn-sm cust-wa-btn"
                                        title="Kirim WhatsApp"
                                        data-nama="{{ $customer->nama }}"
                                        data-hp="{{ $customer->no_hp }}"
                                        data-member="{{ $customer->status_member }}"
                                        data-point="{{ (int) $customer->point }}"
                                        data-point-exp="{{ $customer->pointsExpireLabel() }}"
                                        data-ref="{{ $customer->kode_ref }}">
                                        <i class="bi bi-whatsapp"></i>
                                    </button>
                                    @if (auth()->user()->hasPermission('edit_customer'))
                                    <a wire:navigate href="{{ route('admin.customer.edit', $customer) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_customer'))
                                    <button type="button"
                                        wire:click="$dispatch('will-delete-customer-data', {{ $customer }})"
                                        class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Pelanggan
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Data pelanggan belum tersedia untuk ditampilkan saat ini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $customers->links('vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->

    {{-- ====== Data promo aktif (dibaca oleh JS untuk pesan WA) ====== --}}
    <div id="custActivePromos" class="d-none">
        @foreach ($activePromos as $promo)
        <span data-nama="{{ $promo->nama_promo }}" data-kode="{{ $promo->kode_promo }}"
            data-desc="{{ $promo->deskripsi }}" data-tipe="{{ $promo->tipe_diskon }}"
            data-dmp="{{ (int) $promo->diskon_member_persen }}" data-dmn="{{ (int) $promo->diskon_member_nominal }}"
            data-dnmp="{{ (int) $promo->diskon_non_member_persen }}"
            data-dnmn="{{ (int) $promo->diskon_non_member_nominal }}" data-untuk="{{ $promo->untuk_member }}"
            data-valid="{{ optional($promo->selesai_promo)->translatedFormat('d M Y') }}"></span>
        @endforeach
    </div>

    <style>
        .cust-wa-summary {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            justify-content: center;
            margin-bottom: .5rem;
        }

        .cust-wa-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .28rem .65rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
        }

        .cust-wa-head {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            font-weight: 700;
            margin: .7rem 0 .35rem;
            text-align: left;
        }

        .cust-wa-list {
            display: flex;
            flex-direction: column;
            gap: .4rem;
            text-align: left;
            max-height: 230px;
            overflow-y: auto;
        }

        .cust-wa-item {
            display: block;
            width: 100%;
            text-align: left;
            border: 1px solid #e6e8f2;
            background: #fff;
            border-radius: 12px;
            padding: .65rem .85rem;
            font-weight: 600;
            color: #1e293b;
            font-size: .9rem;
            transition: all .15s ease;
        }

        .cust-wa-item:hover {
            border-color: #22c55e;
            background: linear-gradient(135deg, rgba(34, 197, 94, .10), rgba(22, 163, 74, .04));
            transform: translateY(-1px);
        }

        .cust-wa-item .cw-sub {
            display: block;
            font-size: .76rem;
            font-weight: 500;
            color: #94a3b8;
            margin-top: 2px;
        }

        .cust-wa-all {
            border-color: #22c55e !important;
            background: linear-gradient(135deg, rgba(34, 197, 94, .14), rgba(22, 163, 74, .06)) !important;
            color: #059669 !important;
        }

        .cust-wa-all .cw-sub {
            color: #16a34a;
        }

        .cust-wa-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: .4rem;
        }

        .cust-wa-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            border: 1px solid #e6e8f2;
            border-radius: 12px;
            background: #fff;
            padding: .6rem .4rem;
            font-weight: 600;
            font-size: .8rem;
            color: #1e293b;
            transition: all .15s ease;
        }

        .cust-wa-info:hover {
            border-color: #6c63ff;
            background: rgba(108, 99, 255, .06);
            transform: translateY(-1px);
        }

        .cust-wa-info i.bi {
            font-size: 1.15rem;
            color: #6c63ff;
        }

        .cw-empty {
            color: #94a3b8;
            font-size: .85rem;
            padding: .5rem;
            text-align: center;
        }
    </style>

    @push('scripts')
    <script>
        (function () {
            if (window.__custWaBound) return;
            window.__custWaBound = true;

            var STORE = 'Phoenix Digital Warehouse';

            function rp(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }

            function normPhone(p) {
                p = String(p || '').replace(/[^0-9]/g, '');
                if (p.charAt(0) === '0') p = '62' + p.slice(1);
                else if (p.charAt(0) === '8') p = '62' + p;
                return p;
            }

            function openWa(phone, msg) {
                // Pakai endpoint api.whatsapp.com (emoji tampil konsisten di WA Web/Desktop)
                var no = normPhone(phone);
                window.open('https://api.whatsapp.com/send?phone=' + no + '&text=' + encodeURIComponent(msg), '_blank');
            }

            function getPromos() {
                var src = document.getElementById('custActivePromos');
                if (!src) return [];
                return Array.prototype.map.call(src.querySelectorAll('span'), function (s) {
                    return {
                        nama: s.dataset.nama, kode: s.dataset.kode, desc: s.dataset.desc || '',
                        tipe: s.dataset.tipe, dmp: +s.dataset.dmp || 0, dmn: +s.dataset.dmn || 0,
                        dnmp: +s.dataset.dnmp || 0, dnmn: +s.dataset.dnmn || 0,
                        untuk: s.dataset.untuk || 'all', valid: s.dataset.valid || ''
                    };
                });
            }

            function discText(p, isMember) {
                if (p.tipe === 'persen') { var v = isMember ? p.dmp : p.dnmp; return v ? (v + '%') : ''; }
                if (p.tipe === 'nominal') { var n = isMember ? p.dmn : p.dnmn; return n ? rp(n) : ''; }
                return '';
            }

            function applies(p, isMember) {
                if (p.untuk === 'member_only' && !isMember) return false;
                if (p.untuk === 'non_member_only' && isMember) return false;
                return true;
            }

            function msgPromo(c, p) {
                var d = discText(p, c.member === 'active');
                var m = 'Halo ' + c.nama + '! 🎉\n\nAda promo spesial buat kamu di *' + STORE + '*:\n\n';
                m += '🎁 *' + p.nama + '*\n';
                if (p.desc) m += p.desc + '\n';
                if (d) m += '💸 Diskon: *' + d + '*\n';
                if (p.kode) m += '🔑 Kode: *' + p.kode + '*\n';
                if (p.valid) m += '⏳ Berlaku sampai ' + p.valid + '\n';
                m += '\nYuk manfaatkan sebelum kehabisan! 🛒';
                return m;
            }

            // Info akun digabung dalam 1 pesan: status member + poin + referral
            function msgInfo(c) {
                var isM = c.member === 'active';
                var status = isM ? 'Member Aktif ✅' : 'Belum Member';
                var m = 'Halo ' + c.nama + '! 👋\n\nInfo akun kamu di *' + STORE + '*:\n\n';
                m += '🏅 Status: *' + status + '*\n';
                m += '💎 Poin: *' + c.point + ' poin* (senilai ' + rp(c.point * 500) + ')\n';
                if (c.point > 0 && c.pointExp) m += '⏳ Poin berlaku sampai *' + c.pointExp + '* (kadaluarsa setelah tanggal tsb)\n';
                if (c.ref) m += '🔗 Kode Referral: *' + c.ref + '*\n';
                m += '\n';
                if (c.ref) m += 'Ajak teman belanja pakai kode referral kamu — tiap transaksi pertama mereka, kamu dapat *2 poin*! 🎁\n';
                m += 'Poin bisa ditukar jadi potongan belanja. Terima kasih sudah berbelanja! 🙏';
                return m;
            }

            // Semua promo aktif dalam 1 pesan (untuk broadcast beberapa promo sekaligus)
            function msgAllPromos(c, promos) {
                var isM = c.member === 'active';
                var nums = ['1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣',
                    '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟'];
                var m = 'Halo ' + c.nama + '! 🎉\n\nAda beberapa promo aktif di *' + STORE + '* buat kamu:\n\n';
                promos.forEach(function (p, i) {
                    var d = discText(p, isM);
                    m += (nums[i] || '•') + ' *' + p.nama + '*';
                    var parts = [];
                    if (d) parts.push('diskon ' + d);
                    if (p.kode) parts.push('kode ' + p.kode);
                    if (p.valid) parts.push('s/d ' + p.valid);
                    if (parts.length) m += '\n   ' + parts.join(' · ');
                    m += '\n\n';
                });
                m += 'Yuk manfaatkan sebelum kehabisan! 🛒';
                return m;
            }

            function chip(txt, bg, col) {
                var c = document.createElement('span');
                c.className = 'cust-wa-chip';
                c.style.background = bg;
                c.style.color = col;
                c.textContent = txt;
                return c;
            }

            function infoBtn(icon, label, composer, cust) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'cust-wa-info';
                var ic = document.createElement('i');
                ic.className = 'bi ' + icon;
                var sp = document.createElement('span');
                sp.textContent = label;
                b.appendChild(ic);
                b.appendChild(sp);
                b.addEventListener('click', function () { openWa(cust.hp, composer(cust)); Swal.close(); });
                return b;
            }

            function openPicker(cust) {
                if (typeof Swal === 'undefined') return;
                var isMember = cust.member === 'active';
                var wrap = document.createElement('div');

                var summary = document.createElement('div');
                summary.className = 'cust-wa-summary';
                summary.appendChild(chip(isMember ? '✔ Member Aktif' : 'Non-Member',
                    isMember ? 'rgba(16,185,129,.14)' : 'rgba(148,163,184,.18)', isMember ? '#059669' : '#64748b'));
                summary.appendChild(chip('💎 ' + cust.point + ' poin', 'rgba(245,158,11,.14)', '#b45309'));
                if (cust.ref) summary.appendChild(chip('🔗 ' + cust.ref, 'rgba(108,99,255,.12)', '#4e46e5'));
                wrap.appendChild(summary);

                var ph = document.createElement('div');
                ph.className = 'cust-wa-head';
                ph.textContent = 'Kirim Promo Aktif';
                wrap.appendChild(ph);

                var plist = document.createElement('div');
                plist.className = 'cust-wa-list';
                var promos = getPromos().filter(function (p) { return applies(p, isMember); });
                if (!promos.length) {
                    var e = document.createElement('div');
                    e.className = 'cw-empty';
                    e.textContent = 'Tidak ada promo aktif saat ini';
                    plist.appendChild(e);
                } else {
                    if (promos.length > 1) {
                        var allBtn = document.createElement('button');
                        allBtn.type = 'button';
                        allBtn.className = 'cust-wa-item cust-wa-all';
                        allBtn.textContent = '📢 Kirim Semua Promo (' + promos.length + ')';
                        var allSub = document.createElement('span');
                        allSub.className = 'cw-sub';
                        allSub.textContent = 'Satu pesan berisi seluruh promo aktif';
                        allBtn.appendChild(allSub);
                        allBtn.addEventListener('click', function () { openWa(cust.hp, msgAllPromos(cust, promos)); Swal.close(); });
                        plist.appendChild(allBtn);
                    }
                    promos.forEach(function (p) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'cust-wa-item';
                        b.textContent = p.nama;
                        var d = discText(p, isMember);
                        var sub = [];
                        if (p.kode) sub.push('Kode: ' + p.kode);
                        if (d) sub.push('Diskon ' + d);
                        if (p.valid) sub.push('s/d ' + p.valid);
                        if (sub.length) {
                            var s = document.createElement('span');
                            s.className = 'cw-sub';
                            s.textContent = sub.join(' · ');
                            b.appendChild(s);
                        }
                        b.addEventListener('click', function () { openWa(cust.hp, msgPromo(cust, p)); Swal.close(); });
                        plist.appendChild(b);
                    });
                }
                wrap.appendChild(plist);

                var ih = document.createElement('div');
                ih.className = 'cust-wa-head';
                ih.textContent = 'Kirim Info Akun';
                wrap.appendChild(ih);

                var ib = document.createElement('button');
                ib.type = 'button';
                ib.className = 'cust-wa-item';
                var ibIc = document.createElement('i');
                ibIc.className = 'bi bi-person-badge me-1';
                var ibTx = document.createElement('span');
                ibTx.textContent = 'Poin, Status Member & Referral';
                ib.appendChild(ibIc);
                ib.appendChild(ibTx);
                var ibSub = document.createElement('span');
                ibSub.className = 'cw-sub';
                ibSub.textContent = 'Kirim ringkasan akun pelanggan dalam 1 pesan';
                ib.appendChild(ibSub);
                ib.addEventListener('click', function () { openWa(cust.hp, msgInfo(cust)); Swal.close(); });
                wrap.appendChild(ib);

                Swal.fire({
                    title: 'Kirim WhatsApp ke ' + cust.nama,
                    html: wrap,
                    background: 'rgba(255,255,255,0.92)',
                    backdrop: 'rgba(139,92,246,0.15)',
                    customClass: { popup: 'swal-glossy-popup', title: 'swal-glossy-title' },
                    buttonsStyling: false,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: 500,
                    padding: '1.25rem'
                });
            }

            document.body.addEventListener('click', function (ev) {
                var btn = ev.target.closest('.cust-wa-btn');
                if (!btn) return;
                ev.preventDefault();
                openPicker({
                    nama: btn.dataset.nama || 'Pelanggan',
                    hp: btn.dataset.hp || '',
                    member: btn.dataset.member || '',
                    point: parseInt(btn.dataset.point || '0', 10),
                    pointExp: btn.dataset.pointExp || '',
                    ref: btn.dataset.ref || ''
                });
            });
        })();
    </script>
    @endpush
</div>