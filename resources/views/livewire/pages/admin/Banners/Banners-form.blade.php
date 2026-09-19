<div>
    @php
        $edit = $mode !== 'create';
        $gambarBaru = $gambar && is_object($gambar) && ! $errors->has('gambar');
        $urlBaru = $gambarBaru ? \App\Support\PratinjauUnggahan::url($gambar) : null;
        $urlLama = $existingImage ? asset('storage/img/banners/'.$existingImage) : null;
        $urlTampil = $urlBaru ?: $urlLama;
        $statusOpsi = [
            'active' => ['Aktif', 'bi-broadcast', '#16a34a', 'Tampil di beranda sesuai jadwal di bawah'],
            'non-active' => ['Non-aktif', 'bi-eye-slash', '#64748b', 'Disembunyikan, apa pun jadwalnya'],
        ];
    @endphp

    <form wire:submit.prevent="save" class="bn-form-tata">
        {{-- ================== KOLOM UTAMA ================== --}}
        <div class="bn-form-utama">
            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bn-form-kepala">
                        <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-card-heading"></i></span>
                        <div>
                            <b>Informasi Banner</b>
                            <span>Judul & deskripsi tampil sebagai teks besar di beranda, di samping gambar.</span>
                        </div>
                    </div>

                    <div class="bn-medan dsb-medan">
                        <label class="dsb-label" for="bn-judul">Judul Banner <span class="text-danger">*</span></label>
                        <input id="bn-judul" type="text" wire:model.defer="judul" class="dsb-isian" placeholder="Contoh: Promo Diskon 50%">
                        <small class="bn-bantu">Tampil sebagai judul besar di beranda — singkat & menjual, idealnya di bawah 60 karakter.</small>
                        @error('judul') <small class="bn-galat">{{ $message }}</small> @enderror
                    </div>

                    <div class="bn-medan dsb-medan">
                        <label class="dsb-label" for="bn-desk">Deskripsi</label>
                        <textarea id="bn-desk" wire:model.defer="deskripsi" rows="4" class="dsb-isian bn-desk-isian" placeholder="Kalimat ajakan di bawah judul (opsional)"></textarea>
                        <small class="bn-bantu">Tampil sebagai paragraf di bawah judul di beranda. Satu–dua kalimat cukup.</small>
                        @error('deskripsi') <small class="bn-galat">{{ $message }}</small> @enderror
                    </div>

                    <div class="bn-medan dsb-medan">
                        <span class="dsb-label">Status <span class="text-danger">*</span></span>
                        <div class="bn-status-pilih" role="radiogroup" aria-label="Status banner">
                            @foreach ($statusOpsi as $nilai => [$label, $ikon, $warna, $ket])
                                <label class="bn-status-opsi {{ $status === $nilai ? 'is-pilih' : '' }}" style="--c: {{ $warna }}">
                                    <input type="radio" wire:model.live="status" value="{{ $nilai }}">
                                    <span><i class="bi {{ $ikon }}"></i></span>
                                    <span><b>{{ $label }}</b><small>{{ $ket }}</small></span>
                                </label>
                            @endforeach
                        </div>
                        @error('status') <small class="bn-galat">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>

            {{-- Jadwal tayang (opsional). Kosong = tanpa batas waktu. --}}
            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bn-form-kepala">
                        <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-calendar-range"></i></span>
                        <div>
                            <b>Jadwal Tayang <span class="text-muted fw-normal">(opsional)</span></b>
                            <span>Banner muncul & hilang sendiri sesuai jadwal. Kosongkan untuk tayang terus.</span>
                        </div>
                    </div>

                    <div class="bn-jadwal-isian">
                        <div class="dsb-medan">
                            <label class="dsb-label" for="bn-mulai">Mulai tayang</label>
                            <input id="bn-mulai" type="datetime-local" wire:model.live="mulai_tayang" class="dsb-isian">
                            <small class="bn-bantu">Kosong = langsung tayang begitu Aktif.</small>
                            @error('mulai_tayang') <small class="bn-galat">{{ $message }}</small> @enderror
                        </div>
                        <div class="dsb-medan">
                            <label class="dsb-label" for="bn-selesai">Selesai tayang</label>
                            <input id="bn-selesai" type="datetime-local" wire:model.live="selesai_tayang" class="dsb-isian">
                            <small class="bn-bantu">Kosong = tayang seterusnya.</small>
                            @error('selesai_tayang') <small class="bn-galat">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="bn-cepat">
                        <span>Isi cepat:</span>
                        <button type="button" wire:click="aturJadwal('sekarang')">Mulai sekarang</button>
                        <button type="button" wire:click="aturJadwal('7hari')">7 hari</button>
                        <button type="button" wire:click="aturJadwal('30hari')">30 hari</button>
                        <button type="button" wire:click="aturJadwal('akhirbulan')">Sampai akhir bulan</button>
                        <button type="button" wire:click="aturJadwal('kosong')">Tanpa batas</button>
                    </div>

                    <div class="bn-catatan">
                        <i class="bi bi-info-circle"></i>
                        <span>Jadwal hanya berlaku bila status <b>Aktif</b>. Status <b>Non-aktif</b> tetap menyembunyikan banner, apa pun jadwalnya.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== KOLOM SAMPING ================== --}}
        <aside class="bn-form-samping">
            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bn-form-kepala">
                        <span class="dsb-ikon is-kecil" style="--c: #ea580c"><i class="bi bi-image"></i></span>
                        <div>
                            <b>Gambar Banner @if (! $edit)<span class="text-danger">*</span>@endif</b>
                            <span>Persegi (1:1), mis. 1254×1254 · JPG/PNG maks. 5 MB</span>
                        </div>
                    </div>

                    {{-- Persegi: sama dengan potongan banner di beranda. --}}
                    <div id="bnZona" class="bn-unggah {{ $urlTampil ? 'is-isi' : '' }} {{ $errors->has('gambar') ? 'is-galat' : '' }}">
                        <input type="file" id="gambarInput" wire:model="gambar" accept="image/png, image/jpeg, image/jpg" aria-label="Pilih gambar banner">
                        @if ($urlTampil)
                            <img src="{{ $urlTampil }}" alt="Pratinjau banner">
                            <span class="bn-unggah-ganti"><i class="bi bi-arrow-repeat"></i>{{ $gambarBaru ? 'Gambar baru · klik untuk ganti' : 'Klik untuk ganti gambar' }}</span>
                        @else
                            <span class="bn-unggah-ikon"><i class="bi bi-cloud-arrow-up-fill"></i></span>
                            <b>Klik atau seret gambar ke sini</b>
                            <small>Otomatis diperkecil ke WebP saat disimpan</small>
                        @endif
                    </div>
                    <div class="bn-unggah-muat" wire:loading.flex wire:target="gambar">
                        <span class="dsb-putar is-kecil"></span> Mengunggah gambar…
                    </div>
                    @error('gambar') <small class="bn-galat">{{ $message }}</small> @enderror
                    @if ($edit)
                        <small class="bn-bantu">Kosongkan bila tidak ingin mengganti gambar.</small>
                    @endif
                </div>
            </div>

            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <span class="bn-kicker">{{ $edit ? 'Simpan perubahan' : 'Simpan banner' }}</span>
                    <button type="submit" class="dsb-tombol is-utama bn-simpan" wire:loading.attr="disabled" wire:target="save,gambar">
                        <span wire:loading.remove wire:target="save" class="bn-isi-tombol"><i class="bi bi-check2-circle"></i><span>{{ $edit ? 'Simpan Perubahan' : 'Simpan Banner' }}</span></span>
                        <span wire:loading.inline-flex wire:target="save" class="bn-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyimpan…</span></span>
                    </button>
                    <ul class="bn-tips">
                        <li>Taruh teks penting di tengah — tepi gambar bisa terpotong di layar kecil.</li>
                        <li>Status di daftar menunjukkan keadaan <b>nyata</b> di beranda (Tayang / Terjadwal / Berakhir).</li>
                    </ul>
                </div>
            </div>
        </aside>
    </form>

    <script>
        // Validasi gambar sebelum diunggah. Dipasang SEKALI di dokumen (capture):
        // versi lama memakai DOMContentLoaded, yang tidak berjalan saat halaman
        // dibuka lewat wire:navigate — pengecekannya diam-diam tidak aktif.
        if (!window.__bannerUnggahTerpasang) {
            window.__bannerUnggahTerpasang = true;
            const peringatan = (judul, teks) => {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true,
                    icon: 'error', title: judul, text: teks, background: 'rgba(255, 255, 255, 0.95)',
                    customClass: { popup: 'swal-glossy-toast', title: 'swal-toast-title' },
                });
            };
            document.addEventListener('change', function(e) {
                if (!e.target || e.target.id !== 'gambarInput') return;
                const file = e.target.files[0];
                if (!file) return;
                const tolak = (judul, teks) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.target.value = '';
                    peringatan(judul, teks);
                };
                if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                    tolak('Format tidak didukung', 'Gunakan gambar JPG atau PNG.');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    tolak('Ukuran terlalu besar', 'Maksimal ukuran gambar adalah 5 MB.');
                }
            }, true);
        }
    </script>
</div>
