{{-- Kartu riwayat kesehatan satu peserta.

     Dipakai bersama oleh popup di daftar pendaftaran dan halaman detail
     pelanggan, supaya keduanya tidak lama-lama berbeda bentuk.

     Bacaan pertama yang harus langsung terlihat: SIAPA yang perlu perhatian
     khusus. Karena itu peserta bercatatan khusus diberi pita merah di sisi
     kiri kartunya, bukan sekadar lencana kecil di pojok — saat rombongannya
     dua belas orang, pojok kartu tidak terbaca.

     Butuh: $peserta (array satu riwayat).
--}}
@php
    $khusus = $peserta['ada_catatan_khusus'] ?? false;

    $inisial = collect(explode(' ', trim($peserta['nama_peserta'] ?? '?')))
        ->filter()
        ->take(2)
        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('');

    // Baris yang kosong tidak ditampilkan sama sekali: kartu penuh tulisan
    // "—" membuat yang benar-benar terisi jadi sulit ditemukan.
    $medis = array_filter([
        'Riwayat penyakit' => $peserta['riwayat_penyakit'] ?? null,
        'Alergi' => $peserta['alergi'] ?? null,
        'Obat rutin' => $peserta['obat_rutin'] ?? null,
        'Riwayat operasi' => $peserta['riwayat_operasi'] ?? null,
        'Pantangan makanan' => $peserta['pantangan_makanan'] ?? null,
        'Pantangan kegiatan' => $peserta['pantangan_kegiatan'] ?? null,
    ]);

    $umum = array_filter([
        'Tinggi badan' => ($peserta['tinggi_badan'] ?? null) ? $peserta['tinggi_badan'] . ' cm' : null,
        'Berat badan' => ($peserta['berat_badan'] ?? null) ? $peserta['berat_badan'] . ' kg' : null,
        'Kemampuan renang' => $peserta['kemampuan_renang'] ?? null,
        'Asuransi / BPJS' => $peserta['asuransi'] ?? null,
    ]);
@endphp

<div class="orcha-kesehatan {{ $khusus ? 'orcha-kesehatan-awas' : '' }}">
    <div class="d-flex align-items-start gap-3">
        <div class="orcha-inisial {{ $khusus ? 'orcha-inisial-awas' : '' }}">{{ $inisial ?: '?' }}</div>

        <div class="flex-grow-1">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <div class="fw-bold" style="font-size:1.02rem">{{ $peserta['nama_peserta'] ?? '—' }}</div>
                    <div class="text-muted" style="font-size:.8rem">
                        {{ collect([
                            ($peserta['usia'] ?? null) ? $peserta['usia'] . ' tahun' : null,
                            $peserta['jenis_kelamin'] ?? null,
                            ($peserta['golongan_darah'] ?? null) ? 'Gol. darah ' . $peserta['golongan_darah'] : null,
                        ])->filter()->implode(' · ') ?: 'Data dasar belum lengkap' }}
                    </div>
                </div>

                @if ($khusus)
                    <span class="orcha-lencana-awas">
                        <i class="bi bi-exclamation-triangle-fill"></i> Perlu perhatian
                    </span>
                @else
                    <span class="orcha-lencana-aman">
                        <i class="bi bi-check-circle-fill"></i> Tanpa catatan khusus
                    </span>
                @endif
            </div>

            @if (! empty($peserta['kondisi_khusus']))
                <div class="mt-2 d-flex flex-wrap gap-1">
                    @foreach ($peserta['kondisi_khusus'] as $kondisi)
                        <span class="orcha-cip-kondisi">{{ $kondisi }}</span>
                    @endforeach
                </div>
            @endif

            @if ($medis)
                <div class="orcha-kotak-medis mt-3">
                    @foreach ($medis as $label => $nilai)
                        <div class="orcha-baris-medis">
                            <span class="orcha-label-kecil">{{ $label }}</span>
                            <span class="fw-semibold text-dark">{{ $nilai }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($umum)
                <div class="row g-2 mt-1">
                    @foreach ($umum as $label => $nilai)
                        <div class="col-6 col-lg-3">
                            <div class="orcha-label-kecil">{{ $label }}</div>
                            <div class="fw-semibold" style="font-size:.86rem">{{ $nilai }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Kontak darurat diberi kotaknya sendiri: ini satu-satunya baris
                 yang dicari orang saat sedang panik di lapangan. --}}
            <div class="orcha-kotak-darurat mt-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <i class="bi bi-telephone-fill"></i>
                    <span class="orcha-label-kecil">Kontak darurat</span>
                    <span class="fw-bold">{{ data_get($peserta, 'kontak_darurat.nama') ?: '—' }}</span>
                    @if (data_get($peserta, 'kontak_darurat.hubungan'))
                        <span class="text-muted" style="font-size:.8rem">({{ $peserta['kontak_darurat']['hubungan'] }})</span>
                    @endif
                    @if (data_get($peserta, 'kontak_darurat.hp'))
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $peserta['kontak_darurat']['hp'])) }}"
                            target="_blank" rel="noopener" class="orcha-tautan-wa ms-auto">
                            <i class="bi bi-whatsapp"></i> {{ $peserta['kontak_darurat']['hp'] }}
                        </a>
                    @endif
                </div>
            </div>

            @if (data_get($peserta, 'catatan_tambahan'))
                <div class="mt-2" style="font-size:.85rem">
                    <span class="orcha-label-kecil">Catatan peserta</span>
                    <div class="text-dark">{{ $peserta['catatan_tambahan'] }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
