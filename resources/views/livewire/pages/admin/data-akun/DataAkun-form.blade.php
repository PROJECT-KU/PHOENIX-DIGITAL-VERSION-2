<div>
    <form wire:submit.prevent="save">
        <div class="row g-4">

            <div class="col-md-6">
                <label for="namaAkun" class="form-label text-secondary fw-bold">Nama Akun <span class="text-danger">*</span></label>
                <button type="button" onclick="daNamaPicker(this)"
                    class="form-select text-start da-picker-btn shadow-none @error('nama_akun') is-invalid @enderror" id="namaAkun">
                    @if ($nama_akun)
                    <span class="text-dark">{{ $nama_akun }}</span>
                    @else
                    <span class="text-muted">-- Pilih Nama Akun --</span>
                    @endif
                </button>
                <div class="form-text text-muted">Dari produk. Private: 1 slot; Sharing: 1–10. Nama yang masih aktif tak bisa dipilih.</div>
                @error('nama_akun')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Akun private: kredensial sering baru ada setelah akunnya dibeli,
                 jadi tidak wajib. Akun sharing: tetap wajib. Penanda * & teks
                 bantuannya ikut berubah supaya admin tidak menebak-nebak. --}}
            <div class="col-md-6">
                <label for="username" class="form-label text-secondary fw-bold">
                    Username Akun
                    @if ($wajibKredensial)<span class="text-danger">*</span>@endif
                </label>
                <input type="text" id="username" wire:model.defer="username_akun"
                    class="form-control shadow-none @error('username_akun') is-invalid @enderror"
                    placeholder="{{ $wajibKredensial ? 'Masukkan username' : 'Boleh dikosongkan' }}">
                @error('username_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @unless ($wajibKredensial)
                <small class="text-muted">Akun private — boleh diisi nanti setelah akunnya dibeli.</small>
                @endunless
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label text-secondary fw-bold">
                    Password
                    @if ($wajibKredensial)<span class="text-danger">*</span>@endif
                </label>
                <input type="text" id="password" wire:model.defer="password_akun"
                    class="form-control shadow-none @error('password_akun') is-invalid @enderror"
                    placeholder="{{ $wajibKredensial ? 'Masukkan password' : 'Boleh dikosongkan' }}">
                @error('password_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @unless ($wajibKredensial)
                <small class="text-muted">Kalau diisi, minimal 6 karakter.</small>
                @endunless
            </div>

            <div class="col-md-6">
                <label for="linkLogin" class="form-label text-secondary fw-bold">Link Login Akun <span class="text-danger">*</span></label>
                <input type="url" id="linkLogin" wire:model.defer="link_login_akun"
                    class="form-control shadow-none @error('link_login_akun') is-invalid @enderror"
                    placeholder="https://example.com/login">
                @error('link_login_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 py-1">
                <hr class="text-secondary opacity-25 m-0">
            </div>

            <div class="col-md-4">
                <label for="harga_satuan" class="form-label text-secondary fw-bold">Harga Satuan <span class="text-danger">*</span></label>
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                        style="pointer-events: none; z-index: 5;">
                        Rp
                    </span>
                    <input type="text"
                        inputmode="numeric"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                        wire:model.defer="harga_satuan"
                        class="form-control shadow-none @error('harga_satuan') is-invalid @enderror"
                        placeholder="0"
                        style="background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border-radius: 8px; padding-left: 45px; height: 100%;">
                    @error('harga_satuan')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <label for="pjAkun" class="form-label text-secondary fw-bold">PJ Akun <span class="text-danger">*</span></label>
                <select id="pjAkun" wire:model.defer="pj_akun"
                    class="form-select shadow-none @error('pj_akun') is-invalid @enderror">
                    <option value="">-- Pilih Penanggung Jawab --</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('pj_akun')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label text-secondary fw-bold">Status <span class="text-danger">*</span></label>
                <select id="status" wire:model.defer="status"
                    class="form-select shadow-none @error('status') is-invalid @enderror">
                    <option value="">-- Pilih Status --</option>
                    <option value="active">Active</option>
                    <option value="non-active">Non-Active</option>
                </select>
                @error('status')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="deskripsi" class="form-label text-secondary fw-bold">Deskripsi</label>
                <textarea id="deskripsi" wire:model.defer="deskripsi" rows="3"
                    class="form-control shadow-none @error('deskripsi') is-invalid @enderror"
                    placeholder="Masukkan deskripsi produk"></textarea>
                @error('deskripsi')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-5 pt-4 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center shadow-sm"
                style="height: 52px; border-radius: 8px;">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                <span class="fw-semibold">{{ $this->mode === 'create' ? 'Simpan Data' : 'Update Data' }}</span>
            </button>
        </div>
    </form>

    <style>
        .da-picker-btn {
            cursor: pointer;
        }

        .da-picker-btn::after {
            content: "\F282";
            font-family: "bootstrap-icons";
            float: right;
            color: #94a3b8;
            font-size: .8rem;
        }

        .da-pick-list {
            max-height: 340px;
            overflow-y: auto;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: .4rem;
            padding: .2rem;
        }

        .da-pick-item {
            display: block;
            width: 100%;
            text-align: left;
            border: 1px solid #e6e8f2;
            background: #fff;
            border-radius: 12px;
            padding: .7rem .9rem;
            font-weight: 600;
            color: #1e293b;
            font-size: .92rem;
            transition: all .15s ease;
        }

        .da-pick-item:hover {
            border-color: #6c63ff;
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.04));
            transform: translateY(-1px);
        }

        .da-pick-empty {
            text-align: center;
            color: #94a3b8;
            padding: 1.5rem;
            font-size: .9rem;
        }
    </style>

    @push('scripts')
    <script>
        (function () {
            window.__daNames = @json($availableNames);

            if (window.__daPickerBound) return;
            window.__daPickerBound = true;

            const daGlossy = {
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                buttonsStyling: false,
                showConfirmButton: false,
                showCloseButton: true,
                width: 480,
                padding: '1.25rem',
            };

            window.daNamaPicker = function (btn) {
                if (typeof Swal === 'undefined') return;
                const el = btn.closest('[wire\\:id]');
                if (!el) return;
                const cid = el.getAttribute('wire:id');
                const items = window.__daNames || [];

                const rows = items.length
                    ? items.map(function (nm) {
                        return '<button type="button" class="da-pick-item" data-nama="' + nm + '" data-search="' + nm.toLowerCase() + '">' + nm + '</button>';
                    }).join('')
                    : '<div class="da-pick-empty">Tidak ada slot nama akun yang tersedia.<br>Semua sedang aktif, atau belum ada produk.</div>';

                Swal.fire(Object.assign({
                    title: 'Pilih Nama Akun',
                    html: '<input id="daPickSearch" class="form-control mb-2" placeholder="Ketik untuk mencari...">' +
                        '<div id="daPickList" class="da-pick-list">' + rows + '</div>',
                    didOpen: function () {
                        const search = document.getElementById('daPickSearch');
                        const listEl = document.getElementById('daPickList');
                        if (search) {
                            search.addEventListener('input', function () {
                                const q = search.value.toLowerCase();
                                listEl.querySelectorAll('.da-pick-item').forEach(function (b) {
                                    b.style.display = b.dataset.search.includes(q) ? '' : 'none';
                                });
                            });
                            setTimeout(function () { search.focus(); }, 100);
                        }
                        listEl.querySelectorAll('.da-pick-item').forEach(function (b) {
                            b.addEventListener('click', function () {
                                if (window.Livewire) window.Livewire.find(cid).set('nama_akun', b.dataset.nama);
                                Swal.close();
                            });
                        });
                    }
                }, daGlossy));
            };
        })();
    </script>
    @endpush
</div>