<div wire:poll.30s>
    @if ($tampil)
    <style>
        .bt-panel { display: grid; gap: 14px; margin-bottom: 1.5rem; }
        .bt-bar {
            display: flex; align-items: center; gap: 14px; flex-wrap: wrap; padding: 14px 18px;
            background: rgba(255,255,255,.92); border: 1px solid #eef2f7; border-radius: 18px;
            box-shadow: 0 8px 24px rgba(15,23,42,.05);
        }
        .bt-ikon {
            flex: 0 0 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 14px;
            color: #fff; font-size: 1.25rem; background: linear-gradient(135deg, #8b5cf6, #6366f1);
            box-shadow: 0 10px 20px -10px rgba(99,102,241,.8);
        }
        .bt-ikon i::before { display: block; line-height: 1; }
        .bt-judul { flex: 1 1 220px; min-width: 0; }
        .bt-judul b { display: block; font-size: .98rem; color: #1e293b; }
        .bt-judul small { color: #64748b; font-size: .8rem; }
        .bt-lampu { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: .75rem; font-weight: 700; white-space: nowrap; }
        .bt-lampu::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
        .bt-lampu.on { background: #ecfdf5; color: #059669; }
        .bt-lampu.off { background: #f1f5f9; color: #64748b; }
        .bt-lampu.jeda { background: #fffbeb; color: #b45309; }
        .bt-aksi { display: flex; gap: 8px; flex-wrap: wrap; }
        .bt-btn {
            display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; border: 1px solid #e2e8f0; background: #fff;
            color: #334155; font-size: .8rem; font-weight: 600; padding: 7px 12px; border-radius: 10px; text-decoration: none; cursor: pointer;
        }
        .bt-btn:hover { border-color: #a5b4fc; color: #4338ca; }
        .bt-btn.utama { background: linear-gradient(135deg, #8b5cf6, #6366f1); border-color: transparent; color: #fff; }
        .bt-btn.bahaya { background: #dc2626; border-color: #dc2626; color: #fff; }
        .bt-btn.hijau { background: #059669; border-color: #059669; color: #fff; }

        .bt-kartu { border-radius: 18px; padding: 16px 18px; border: 1px solid; }
        .bt-kartu h6 { display: flex; align-items: center; gap: 8px; font-weight: 800; margin: 0 0 4px; font-size: .98rem; }
        .bt-kartu p { margin: 0 0 10px; font-size: .84rem; }
        .bt-kartu.merah { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .bt-kartu.kuning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .bt-kartu.ungu { background: #f5f3ff; border-color: #ddd6fe; color: #4c1d95; }
        .bt-daftar { display: grid; gap: 8px; }
        .bt-baris {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap; background: #fff; border: 1px solid #fde68a;
            border-radius: 12px; padding: 10px 12px; color: #334155;
        }
        .bt-baris-isi { flex: 1 1 260px; min-width: 0; font-size: .82rem; }
        .bt-baris-isi b { color: #0f172a; }
        .bt-baris-isi span { display: block; color: #64748b; }
        .bt-token { display: flex; gap: 8px; margin-top: 8px; }
        .bt-token input { flex: 1; min-width: 0; font-family: ui-monospace, monospace; font-size: .8rem; border: 1px solid #c4b5fd; border-radius: 10px; padding: 7px 10px; background: #fff; }
    </style>

    <div class="container-fluid">
        <div class="bt-panel">
            @if (! $skemaSiap)
                @if ($bolehAtur)
                <div class="bt-kartu kuning">
                    <h6><i class="bi bi-database-exclamation"></i> Bot Turnitin belum bisa dipakai</h6>
                    <p class="mb-0">Kolom database untuk bot belum ada. Jalankan SQL migrasi <code>2026_09_14_100000_tambah_bot_turnitin_di_order_uploads</code> di server.</p>
                </div>
                @endif
            @else
                {{-- Bilah status --}}
                <div class="bt-bar">
                    <span class="bt-ikon"><i class="bi bi-robot"></i></span>
                    <div class="bt-judul">
                        <b>Bot Turnitin (submitin.id)</b>
                        <small>
                            @if (! $dipasang)
                                Belum dipasang.
                            @elseif ($aktif)
                                Terakhir memberi kabar {{ $detak->locale('id')->diffForHumans() }} · {{ $antrean }} unggahan di antrean
                            @elseif ($detak)
                                Tidak aktif sejak {{ $detak->locale('id')->diffForHumans() }} — buka tab submitin.id di Chrome admin · {{ $antrean }} unggahan di antrean
                            @else
                                Belum pernah terhubung — pasang skripnya di Chrome admin.
                            @endif
                        </small>
                    </div>
                    @if ($dipasang)
                        @if ($dijeda)
                            <span class="bt-lampu jeda">Dijeda</span>
                        @elseif ($aktif)
                            <span class="bt-lampu on">Aktif</span>
                        @else
                            <span class="bt-lampu off">Tidak aktif</span>
                        @endif
                    @endif
                    <div class="bt-aksi">
                        @if ($dipasang)
                            <button type="button" class="bt-btn" wire:click="alihkanJeda">
                                <i class="bi {{ $dijeda ? 'bi-play-fill' : 'bi-pause-fill' }}"></i> {{ $dijeda ? 'Lanjutkan' : 'Jeda' }}
                            </button>
                        @endif
                        @if ($bolehAtur)
                            <a class="bt-btn" href="{{ $urlSkrip }}" target="_blank" rel="noopener"><i class="bi bi-download"></i> Pasang skrip</a>
                            @if ($dipasang)
                                <button type="button" class="bt-btn pcek-konfirmasi" data-action="buatToken"
                                    data-title="Buat token baru?" data-text="Token lama langsung berhenti bekerja. Bot di Chrome admin perlu diisi token yang baru."
                                    data-confirm="Ya, buat baru" data-icon="warning">
                                    <i class="bi bi-key"></i> Token baru
                                </button>
                            @else
                                <button type="button" class="bt-btn utama" wire:click="buatToken">
                                    <i class="bi bi-key"></i> Buat token
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                @if ($tokenBaru)
                <div class="bt-kartu ungu">
                    <h6><i class="bi bi-key-fill"></i> Token bot — salin sekarang</h6>
                    <p>Tempel di panel bot pada tab submitin.id (tombol <b>Pengaturan</b>). Token ini tidak akan ditampilkan lagi. Jangan dibagikan di chat.</p>
                    <div class="bt-token">
                        <input type="text" readonly value="{{ $tokenBaru }}" id="bt-token-input" onclick="this.select()">
                        <button type="button" class="bt-btn utama" onclick="navigator.clipboard.writeText(document.getElementById('bt-token-input').value)"><i class="bi bi-clipboard"></i> Salin</button>
                        <button type="button" class="bt-btn" wire:click="tutupToken">Tutup</button>
                    </div>
                </div>
                @endif

                @if ($masalah && $aktif)
                <div class="bt-kartu kuning">
                    <h6><i class="bi bi-exclamation-circle"></i> Bot berhenti menunggu</h6>
                    <p class="mb-0">{{ $masalah }}</p>
                </div>
                @endif

                {{-- Kuota paket Standard habis --}}
                @if ($kuotaHabis)
                <div class="bt-kartu merah">
                    <h6><i class="bi bi-battery"></i> Kuota paket Standard di submitin.id habis</h6>
                    <p>{{ $kuotaHabis['pesan'] }} Bot berhenti mengambil antrean sejak {{ \Illuminate\Support\Carbon::parse($kuotaHabis['at'])->locale('id')->diffForHumans() }} — {{ $antrean }} unggahan menunggu.</p>
                    <div class="bt-aksi">
                        <a class="bt-btn" href="https://submitin.id/" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Buka submitin.id (Paket Hemat)</a>
                        <button type="button" class="bt-btn hijau" wire:click="kuotaSudahDiisi"><i class="bi bi-check2-circle"></i> Kuota sudah diisi — lanjutkan bot</button>
                    </div>
                </div>
                @endif

                {{-- Perlu dikerjakan manual --}}
                @if ($perluAdmin->isNotEmpty())
                <div class="bt-kartu kuning">
                    <h6><i class="bi bi-person-exclamation"></i> {{ $perluAdmin->count() }} pengecekan perlu dikerjakan admin</h6>
                    <p>Bot tidak bisa menyelesaikannya. Buka pesanannya lalu kerjakan seperti biasa.</p>
                    <div class="bt-daftar">
                        @foreach ($perluAdmin as $up)
                            @php $macet = \App\Support\BotTurnitin::macet($up); @endphp
                            <div class="bt-baris" wire:key="bt-{{ $up->id }}">
                                <div class="bt-baris-isi">
                                    <b>{{ optional($up->order)->order_number }}</b>
                                    · {{ $macet ? 'Bot tidak memberi kabar' : ($up->bot_status === 'perlu_dilengkapi' ? 'Perlu dilengkapi' : 'Bot gagal') }}
                                    · {{ $up->bot_diperbarui_at?->locale('id')->diffForHumans() }}
                                    @if ($up->bot_pesan)
                                        <span>{{ $up->bot_pesan }}</span>
                                    @endif
                                    @if ($up->bot_kode)
                                        <span>Sudah terkirim ke submitin:
                                            <a href="https://submitin.id/status?order={{ urlencode($up->bot_kode) }}" target="_blank" rel="noopener">{{ $up->bot_kode }}</a>
                                            — cek di sana dulu sebelum mengirim ulang.</span>
                                    @endif
                                </div>
                                <div class="bt-aksi">
                                    @if ($up->order)
                                        <a class="bt-btn utama" href="{{ route('admin.pesanantoko.detail', $up->order) }}" wire:navigate><i class="bi bi-box-arrow-in-right"></i> Buka pesanan</a>
                                    @endif
                                    @if (! $up->bot_kode && $up->bot_status === 'gagal')
                                        <button type="button" class="bt-btn" wire:click="cobaLagi('{{ $up->id }}')"><i class="bi bi-arrow-repeat"></i> Coba lagi pakai bot</button>
                                    @endif
                                    @if ($up->bot_status !== 'perlu_dilengkapi')
                                        <button type="button" class="bt-btn" wire:click="ambilAlih('{{ $up->id }}')"><i class="bi bi-person-check"></i> Saya kerjakan manual</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
    @endif
</div>
