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
                <a href="{{ route('admin.customer-message.index') }}" wire:navigate class="dsb-tombol">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
            </div>
        </header>

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
                            @if ($message->read_at)
                                <span class="pp-tanda is-rendah"><i class="bi bi-envelope-open"></i>Dibaca {{ $message->read_at->locale('id')->diffForHumans() }}</span>
                            @else
                                <span class="pp-tanda is-baru"><i class="bi bi-envelope-exclamation"></i>Belum dibaca</span>
                            @endif
                            <span class="pp-tanda is-rendah"><i class="bi bi-hourglass-split"></i>Menunggu {{ $message->menungguJam() }} jam</span>
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
                            <p class="pp-catatan-kecil">Balasan dikirim dari aplikasi WhatsApp atau surel Anda sendiri — isinya tidak tersimpan di sini.</p>
                        </div>
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
                            <select class="dsb-isian" wire:model.live="status">
                                @foreach (\App\Livewire\Pages\Admin\Message\CustomerMessageList::STATUS as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($status === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="pp-medan">
                            <span>Prioritas</span>
                            <select class="dsb-isian" wire:model.live="priority">
                                @foreach (\App\Livewire\Pages\Admin\Message\CustomerMessageList::PRIORITAS as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($priority === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <p class="pp-catatan-kecil">Perubahan langsung tersimpan; tidak ada tombol simpan.</p>
                    </div>
                </section>

                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <span class="pp-detail-label">Rincian tiket</span>
                        <div class="pp-info is-tunggal">
                            <div><small>Nomor tiket</small><b>{{ $message->ticket }}</b></div>
                            <div><small>Masuk</small><b>{{ $message->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</b></div>
                            <div><small>Dibaca</small><b>{{ $message->read_at?->locale('id')->translatedFormat('d M Y, H:i') ?: 'Belum dibaca' }}</b></div>
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
                    customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', title: 'swal-glossy-title' },
                    buttonsStyling: false,
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
                    const salin = e.target.closest('.pp-salin');
                    if (!salin || typeof Swal === 'undefined') return;
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
