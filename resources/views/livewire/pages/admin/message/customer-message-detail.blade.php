@section('title')
Detail Pesan Pelanggan || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.message.partials.pesan-gaya')

    @php
        [$stLabel, $stLencana, $stWarna] = $message->tampilanStatus();
        [$prLabel, $prKelas, $prWarna] = $message->tampilanPrioritas();
        $warnaAvatar = ['#7c3aed', '#2563eb', '#16a34a', '#d97706', '#db2777', '#0891b2'][crc32((string) $message->name) % 6];
        $balasanWa = 'Halo '.$message->name.', terima kasih sudah menghubungi Phoenix Digital (tiket '.$message->ticket.'). ';
        $bolehUbah = (bool) auth()->user()?->hasPermission('edit_customer_message');
        $bolehHapus = (bool) auth()->user()?->hasPermission('delete_customer_message');
        $kategoriDaftar = config('helpdesk.kategori');
        $kanalDaftar = config('helpdesk.kanal');
        // Tautan ke modul lain hanya ditampilkan kalau memang boleh dibuka,
        // supaya petugas helpdesk tidak mendarat di halaman 403.
        $bolehLihatPelanggan = (bool) auth()->user()?->hasPermission('view_customer');
        $bolehLihatPesanan = (bool) auth()->user()?->hasPermission('view_pemesanantoko');
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Detail Pesan</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-ticket-perforated me-1"></i>{{ $message->ticket }}</span>
                    <span class="d-block">Masuk {{ $message->created_at?->locale('id')->translatedFormat('l, d F Y · H:i') }}</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.customer-message.index') }}" wire:navigate class="dsb-tombol is-lembut">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
                <button type="button" class="dsb-tombol is-lembut" wire:click="unduhPdf" wire:loading.attr="disabled" wire:target="unduhPdf">
                    <span wire:loading.remove wire:target="unduhPdf" class="pp-isi-tombol"><i class="bi bi-file-earmark-pdf"></i><span>Cetak tiket</span></span>
                    <span wire:loading.inline-flex wire:target="unduhPdf" class="pp-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
                @if ($bolehHapus)
                    <button type="button" class="dsb-tombol is-lembut pp-konfirmasi" data-action="tandaiSpam" data-icon="warning"
                        data-title="Tandai tiket ini spam?" data-text="Tiket ditutup dan dipindahkan ke arsip." data-confirm="Ya, spam">
                        <span class="pp-isi-tombol"><i class="bi bi-shield-exclamation"></i><span>Spam</span></span>
                    </button>
                    <button type="button" class="dsb-tombol is-lembut pp-konfirmasi" data-action="arsipkan" data-icon="question"
                        data-title="Arsipkan tiket ini?" data-text="Tiket disimpan di arsip dan masih bisa dikembalikan." data-confirm="Ya, arsipkan">
                        <span class="pp-isi-tombol"><i class="bi bi-archive"></i><span>Arsipkan</span></span>
                    </button>
                @endif
            </div>
        </header>

        {{-- Pindah antar tiket tanpa balik ke daftar dulu. --}}
        @if ($sebelum || $berikut)
            <div class="pp-tetangga" style="margin-bottom: 14px;">
                @if ($sebelum)
                    <a href="{{ route('admin.customer-message.detail', $sebelum->id) }}" wire:navigate class="pp-btn">
                        <i class="bi bi-chevron-left"></i><span>Tiket lebih baru</span>
                    </a>
                @endif
                @if ($berikut)
                    <a href="{{ route('admin.customer-message.detail', $berikut->id) }}" wire:navigate class="pp-btn">
                        <span>Tiket lebih lama</span><i class="bi bi-chevron-right"></i>
                    </a>
                @endif
            </div>
        @endif

        <div class="pp-detail">
            {{-- ================== KOLOM ISI ================== --}}
            <div class="pp-detail-utama">
                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <div class="pp-pengirim">
                            <span class="pp-avatar" style="--av: {{ $warnaAvatar }}; cursor: default;">
                                {{ mb_strtoupper(mb_substr(trim((string) $message->name), 0, 1)) ?: '?' }}
                            </span>
                            <div class="pp-pengirim-teks">
                                <b>{{ $message->name }}</b>
                                <span>{{ $message->email ?: 'Tanpa surel' }}{{ $message->no_telp ? ' · '.$message->no_telp : '' }}</span>
                            </div>
                            <span class="dsb-lencana {{ $stLencana }}">{{ $stLabel }}</span>
                        </div>

                        <div class="pp-penanda" style="margin-top: 14px;">
                            <span class="pp-tanda {{ $prKelas }}"><i class="bi bi-flag-fill"></i>Prioritas {{ $prLabel }}</span>
                            @if ($message->labelKategori())
                                <span class="pp-tanda is-topik"><i class="bi bi-tag-fill"></i>{{ $message->labelKategori() }}</span>
                            @endif
                            @if ($message->petugas)
                                <span class="pp-tanda is-petugas"><i class="bi bi-person-check-fill"></i>{{ $message->petugas->name }}</span>
                            @endif
                            @if ($message->read_at)
                                <span class="pp-tanda is-rendah"><i class="bi bi-envelope-open"></i>Dibaca {{ $message->read_at->locale('id')->diffForHumans() }}</span>
                            @else
                                <span class="pp-tanda is-baru"><i class="bi bi-envelope-exclamation"></i>Belum dibaca</span>
                            @endif
                            @if ($message->sudahDibalas())
                                <span class="pp-tanda is-dibalas"><i class="bi bi-check2-all"></i>Dibalas {{ $message->replied_at->locale('id')->diffForHumans() }}</span>
                            @elseif ($message->lewatBatas())
                                <span class="pp-tanda is-lewat"><i class="bi bi-alarm"></i>Lewat batas {{ $message->batasJam() }} jam</span>
                            @else
                                <span class="pp-tanda is-rendah"><i class="bi bi-hourglass-split"></i>Batas balas {{ $message->batasJam() }} jam</span>
                            @endif
                        </div>

                        <blockquote class="pp-kutip">{{ $message->message }}</blockquote>

                        <div class="pp-balas">
                            <span class="pp-detail-label">Balas pelanggan</span>
                            <div class="pp-balas-tombol">
                                @if ($wa = $message->tautanWa($balasanWa))
                                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="pp-btn is-wa">
                                        <i class="bi bi-whatsapp"></i><span>Balas WhatsApp</span>
                                    </a>
                                @endif
                                @if ($surel = $message->tautanEmail())
                                    <a href="{{ $surel }}" class="pp-btn">
                                        <i class="bi bi-envelope"></i><span>Balas surel</span>
                                    </a>
                                @endif
                                <button type="button" class="pp-btn pp-salin" data-teks="{{ $message->message }}">
                                    <i class="bi bi-clipboard"></i><span>Salin pesan</span>
                                </button>
                            </div>
                            <p class="pp-catatan-kecil">Balasannya dikirim dari aplikasi WhatsApp atau surel Anda sendiri. Tempelkan isinya di kotak bawah supaya tiket ini punya bukti sudah dijawab.</p>
                        </div>
                    </div>
                </section>

                {{-- ================== CATAT BALASAN ================== --}}
                @if ($bolehUbah)
                    <section class="dsb-kartu">
                        <div class="dsb-kartu-isi">
                            <span class="pp-detail-label">Catat balasan</span>

                            @if ($templates->isNotEmpty())
                                <div class="pp-template">
                                    @foreach ($templates as $t)
                                        <button type="button" class="pp-template-btn" wire:click="pakaiTemplate('{{ $t->id }}')" title="Isikan template ini ke kotak balasan">
                                            <i class="bi bi-lightning-charge-fill"></i>{{ $t->nama }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="pp-tulis">
                                <textarea class="dsb-isian" rows="4" wire:model="balasanIsi" placeholder="Tulis atau tempel isi balasan yang dikirim ke pelanggan…"></textarea>
                                @error('balasanIsi')<span class="pp-galat">{{ $message }}</span>@enderror

                                <div class="pp-tulis-baris">
                                    <label class="pp-centang">
                                        <input type="checkbox" wire:model="balasanSelesai">
                                        <span>Sekalian tandai tiket selesai</span>
                                    </label>
                                    <select class="dsb-isian pp-pilih" wire:model="balasanKanal" aria-label="Kanal balasan">
                                        @foreach ($kanalDaftar as $nilai => $label)
                                            <option value="{{ $nilai }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="pp-btn is-utama" wire:click="simpanBalasan" wire:loading.attr="disabled" wire:target="simpanBalasan">
                                        <i class="bi bi-send"></i><span>Simpan balasan</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Kelola template: jarang dipakai, jadi dilipat. --}}
                            <button type="button" class="pp-teknis-pemicu" style="margin-top: 16px;" wire:click="$toggle('kelolaTemplate')" aria-expanded="{{ $kelolaTemplate ? 'true' : 'false' }}">
                                <span class="pp-ringkas-ikon is-rendah" style="background: #faf5ff; color: #7c3aed;"><i class="bi bi-lightning-charge"></i></span>
                                <span>
                                    <b>Kelola template balasan</b>
                                    <small>Dipakai di semua tiket. {nama} dan {tiket} otomatis diganti.</small>
                                </span>
                                <i class="bi {{ $kelolaTemplate ? 'bi-chevron-up' : 'bi-chevron-down' }}"></i>
                            </button>

                            @if ($kelolaTemplate)
                                <div class="pp-template-kelola">
                                    @if ($templates->isNotEmpty())
                                        <div class="pp-template-daftar">
                                            @foreach ($templates as $t)
                                                <div class="pp-template-item" wire:key="tpl-{{ $t->id }}">
                                                    <b>{{ $t->nama }}</b>
                                                    <button type="button" class="pp-btn pp-btn-ikon is-bahaya pp-konfirmasi" data-action="hapusTemplate" data-arg="{{ $t->id }}" data-icon="warning"
                                                        data-title="Hapus template ini?" data-text="{{ $t->nama }} tidak akan muncul lagi di tiket mana pun." data-confirm="Ya, hapus"
                                                        title="Hapus template" aria-label="Hapus template"><i class="bi bi-trash3"></i></button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <label class="pp-medan">
                                        <span>Nama template baru</span>
                                        <input type="text" class="dsb-isian" wire:model="templateNama" placeholder="Misalnya: Minta bukti transfer">
                                    </label>
                                    @error('templateNama')<span class="pp-galat">{{ $message }}</span>@enderror

                                    <label class="pp-medan">
                                        <span>Isi template</span>
                                        <textarea class="dsb-isian" rows="3" wire:model="templateIsi" placeholder="Halo {nama}, …"></textarea>
                                    </label>
                                    @error('templateIsi')<span class="pp-galat">{{ $message }}</span>@enderror

                                    <div>
                                        <button type="button" class="pp-btn" wire:click="simpanTemplate">
                                            <i class="bi bi-plus-lg"></i><span>Tambah template</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- ================== CATATAN INTERNAL ================== --}}
                    <section class="dsb-kartu">
                        <div class="dsb-kartu-isi">
                            <span class="pp-detail-label">Catatan internal</span>
                            <p class="pp-catatan-kecil" style="margin-top: 0 !important;">Hanya terlihat oleh tim — tidak pernah dikirim ke pelanggan.</p>
                            <div class="pp-tulis">
                                <textarea class="dsb-isian" rows="2" wire:model="catatanIsi" placeholder="Misalnya: sudah ditelepon, minta ditunda sampai besok."></textarea>
                                @error('catatanIsi')<span class="pp-galat">{{ $message }}</span>@enderror
                                <div class="pp-tulis-baris">
                                    <button type="button" class="pp-btn" wire:click="simpanCatatan">
                                        <i class="bi bi-sticky"></i><span>Simpan catatan</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- ================== LINIMASA ================== --}}
                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <span class="pp-detail-label">Linimasa tiket</span>
                        <ol class="pp-linimasa">
                            <li class="pp-baris">
                                <span class="pp-baris-ikon" style="--c: #7c3aed;"><i class="bi bi-inbox-fill"></i></span>
                                <div class="pp-baris-kepala">
                                    <b>Pesan masuk</b>
                                    <small>{{ $message->created_at?->locale('id')->translatedFormat('d M Y, H:i') }} · dari {{ $message->name }}</small>
                                </div>
                            </li>
                            @foreach ($logs as $log)
                                @php [$lIkon, $lWarna, $lJudul] = $log->tampilan(); @endphp
                                <li class="pp-baris is-{{ $log->jenis }}" wire:key="log-{{ $log->id }}">
                                    <span class="pp-baris-ikon" style="--c: {{ $lWarna }}"><i class="bi {{ $lIkon }}"></i></span>
                                    <div class="pp-baris-kepala">
                                        <b>{{ $lJudul }}</b>
                                        <small>{{ $log->created_at?->locale('id')->translatedFormat('d M Y, H:i') }} · {{ $log->pelaku() }}</small>
                                    </div>
                                    @if ($log->isi)
                                        <p class="pp-baris-isi">{{ $log->isi }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>

                {{-- Keterangan teknis: jarang dipakai, jadi disembunyikan sampai diminta. --}}
                @if ($message->ip_address || $message->user_agent)
                    <section class="dsb-kartu" x-data="{ buka: false }">
                        <div class="dsb-kartu-isi">
                            <button type="button" class="pp-teknis-pemicu" x-on:click="buka = !buka" :aria-expanded="buka.toString()">
                                <span class="pp-ringkas-ikon is-rendah" style="background: #f1f5f9; color: #64748b;"><i class="bi bi-hdd-network"></i></span>
                                <span>
                                    <b>Keterangan teknis</b>
                                    <small>Alamat IP & peramban pengirim — untuk menelusuri kiriman mencurigakan.</small>
                                </span>
                                <i class="bi" :class="buka ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            </button>
                            <div x-show="buka" x-collapse x-cloak>
                                <div class="pp-info" style="margin-top: 14px;">
                                    <div><small>Alamat IP</small><b>{{ $message->ip_address ?: '—' }}</b></div>
                                    <div><small>Peramban</small><b>{{ \Illuminate\Support\Str::limit($message->user_agent, 120) ?: '—' }}</b></div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>

            {{-- ================== KOLOM SAMPING ================== --}}
            <aside class="pp-detail-samping">
                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <span class="pp-detail-label">Tindak lanjut</span>

                        <label class="pp-medan">
                            <span>Status</span>
                            <select class="dsb-isian" wire:model.live="status" @disabled(! $bolehUbah)>
                                @foreach (\App\Livewire\Pages\Admin\Message\CustomerMessageList::STATUS as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($status === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="pp-medan">
                            <span>Prioritas</span>
                            <select class="dsb-isian" wire:model.live="priority" @disabled(! $bolehUbah)>
                                @foreach (\App\Livewire\Pages\Admin\Message\CustomerMessageList::PRIORITAS as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($priority === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="pp-medan">
                            <span>Topik</span>
                            <select class="dsb-isian" wire:model.live="kategori" @disabled(! $bolehUbah)>
                                <option value="">Belum ditentukan</option>
                                @foreach ($kategoriDaftar as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($kategori === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="pp-medan">
                            <span>Pemegang tiket</span>
                            <select class="dsb-isian" wire:model.live="petugas" @disabled(! $bolehUbah)>
                                <option value="">Belum ditugaskan</option>
                                @foreach ($daftarPetugas as $orang)
                                    <option value="{{ $orang->id }}" @selected((string) $petugas === (string) $orang->id)>{{ $orang->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <p class="pp-catatan-kecil">
                            {{ $bolehUbah ? 'Perubahan langsung tersimpan dan tercatat di linimasa.' : 'Anda hanya bisa melihat tiket ini.' }}
                        </p>
                    </div>
                </section>

                {{-- Kaitan dengan data pelanggan: konteks yang paling dicari saat membalas. --}}
                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <span class="pp-detail-label">Pengirim di data kita</span>
                        <div class="pp-kait">
                            @if ($pelanggan)
                                @if ($bolehLihatPelanggan)
                                    <a href="{{ route('admin.customer.show', $pelanggan->id) }}" wire:navigate class="pp-kait-baris">
                                        <i class="bi bi-person-badge"></i>
                                        <span>
                                            <b>{{ $pelanggan->nama }}</b>
                                            <small>Pelanggan terdaftar · {{ $pelanggan->status_member === 'active' ? 'Member aktif' : 'Non-member' }}</small>
                                        </span>
                                    </a>
                                @else
                                    <div class="pp-kait-baris">
                                        <i class="bi bi-person-badge"></i>
                                        <span>
                                            <b>{{ $pelanggan->nama }}</b>
                                            <small>Pelanggan terdaftar · {{ $pelanggan->status_member === 'active' ? 'Member aktif' : 'Non-member' }}</small>
                                        </span>
                                    </div>
                                @endif
                                @forelse ($pesanan as $order)
                                    @if ($bolehLihatPesanan)
                                        <a href="{{ route('admin.pesanantoko.detail', $order->id) }}" wire:navigate class="pp-kait-baris" wire:key="order-{{ $order->id }}">
                                            <i class="bi bi-bag-check"></i>
                                            <span>
                                                <b>{{ $order->order_number }}</b>
                                                <small>{{ ucfirst($order->status) }} · Rp {{ number_format((float) $order->total, 0, ',', '.') }} · {{ $order->created_at?->locale('id')->translatedFormat('d M Y') }}</small>
                                            </span>
                                        </a>
                                    @else
                                        <div class="pp-kait-baris" wire:key="order-{{ $order->id }}">
                                            <i class="bi bi-bag-check"></i>
                                            <span>
                                                <b>{{ $order->order_number }}</b>
                                                <small>{{ ucfirst($order->status) }} · {{ $order->created_at?->locale('id')->translatedFormat('d M Y') }}</small>
                                            </span>
                                        </div>
                                    @endif
                                @empty
                                    <p class="pp-catatan-kecil">Belum ada pesanan atas nama pelanggan ini.</p>
                                @endforelse
                            @else
                                <p class="pp-catatan-kecil">Surel dan nomornya belum cocok dengan pelanggan mana pun di data kita.</p>
                            @endif
                        </div>
                    </div>
                </section>

                @if ($pesanLain->isNotEmpty())
                    <section class="dsb-kartu">
                        <div class="dsb-kartu-isi">
                            <span class="pp-detail-label">Pesan lain dari orang yang sama</span>
                            <div class="pp-kait">
                                @foreach ($pesanLain as $lain)
                                    <a href="{{ route('admin.customer-message.detail', $lain->id) }}" wire:navigate class="pp-kait-baris" wire:key="lain-{{ $lain->id }}">
                                        <i class="bi bi-chat-left-text"></i>
                                        <span>
                                            <b>{{ $lain->ticket }}</b>
                                            <small>{{ $lain->tampilanStatus()[0] }} · {{ $lain->created_at?->locale('id')->translatedFormat('d M Y') }}</small>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <span class="pp-detail-label">Rincian tiket</span>
                        <div class="pp-info is-tunggal">
                            <div><small>Nomor tiket</small><b>{{ $message->ticket }}</b></div>
                            <div><small>Masuk</small><b>{{ $message->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</b></div>
                            <div><small>Dibaca</small><b>{{ $message->read_at?->locale('id')->translatedFormat('d M Y, H:i') ?: 'Belum dibaca' }}</b></div>
                            <div><small>Dibalas</small><b>{{ $message->replied_at?->locale('id')->translatedFormat('d M Y, H:i') ?: 'Belum dicatat' }}{{ $message->pembalas ? ' · '.$message->pembalas->name : '' }}</b></div>
                            <div><small>Nomor telepon</small><b>{{ $message->no_telp ?: '—' }}</b></div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    @include('livewire.layout.sweetalert')

    @push('scripts')
        <script>
            // Dipasang SEKALI di dokumen: wire:navigate mengganti <body>.
            if (!window.__pesanDetailTerpasang) {
                window.__pesanDetailTerpasang = true;
                const gaya = {
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel', title: 'swal-glossy-title' },
                    buttonsStyling: false,
                };
                const panggil = (el, metode, arg) => {
                    const komponen = el.closest('[wire\\:id]');
                    if (!komponen) return;
                    const hidup = Livewire.find(komponen.getAttribute('wire:id'));
                    if (arg === undefined) hidup.call(metode); else hidup.call(metode, arg);
                };
                // execCommand: peramban lama & konteks tanpa HTTPS tidak punya
                // navigator.clipboard, dan tombol Salin harus tetap bekerja.
                const salinCadangan = (teks, selesai) => {
                    const ta = document.createElement('textarea');
                    ta.value = teks;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.left = '-9999px';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); selesai(); } catch (e) { /* diam: tak ada yang bisa dilakukan */ }
                    document.body.removeChild(ta);
                };
                document.addEventListener('click', (e) => {
                    if (typeof Swal === 'undefined') return;

                    const tombol = e.target.closest('.pp-konfirmasi');
                    if (tombol) {
                        e.preventDefault();
                        Swal.fire({
                            title: tombol.dataset.title || 'Lanjutkan?', text: tombol.dataset.text || '',
                            icon: tombol.dataset.icon || 'question', showCancelButton: true,
                            confirmButtonText: tombol.dataset.confirm || 'Ya', cancelButtonText: 'Batal', ...gaya,
                        }).then((r) => { if (r.isConfirmed) panggil(tombol, tombol.dataset.action, tombol.dataset.arg); });
                        return;
                    }

                    const salin = e.target.closest('.pp-salin');
                    if (!salin) return;
                    e.preventDefault();
                    const teks = salin.dataset.teks || '';
                    const sudah = () => Swal.fire({ title: 'Tersalin', text: 'Isi pesan sudah disalin.', icon: 'success', timer: 1600, showConfirmButton: false, ...gaya });
                    if (navigator.clipboard?.writeText) {
                        navigator.clipboard.writeText(teks).then(sudah).catch(() => salinCadangan(teks, sudah));
                    } else {
                        salinCadangan(teks, sudah);
                    }
                });
            }
        </script>
    @endpush
</div>
