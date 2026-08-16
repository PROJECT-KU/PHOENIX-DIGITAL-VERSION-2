{{-- Manifes tour leader.

     Dibaca sambil berdiri di titik jemput, sering di layar ponsel. Urutannya
     mengikuti urutan pekerjaan hari itu: menjemput dulu, baru menangani orang.

     Yang sengaja TIDAK masuk: rincian medis panjang, nomor rekening, dan
     riwayat pembayaran. Berkas ini berpindah tangan di lapangan; makin sedikit
     yang tidak dipakai, makin aman kalau tertinggal di kursi bus.

     Dompdf tidak mengenal flexbox, jadi tata letaknya memakai tabel. --}}
@php
    $navy = '#0f2d4a';
    $ocean = '#1d6fa5';
    $emas = '#ffc74e';

    $peserta = $pendaftaran['peserta'] ?? [];
    $perTitik = $pendaftaran['jemput_per_titik'] ?? [];

    // Riwayat dicari berdasarkan nama, karena itulah satu-satunya penghubung
    // antara daftar peserta dan formulir kesehatan yang diisi masing-masing.
    $kesehatan = collect($riwayat)->keyBy(fn ($satu) => mb_strtolower(trim($satu['nama_peserta'] ?? '')));
    $cari = fn ($nama) => $kesehatan[mb_strtolower(trim($nama ?? ''))] ?? null;

    $perluPerhatian = collect($riwayat)->filter(fn ($s) => ($s['tingkat_perhatian'] ?? '') === 'tinggi');
    $belumIsi = $pendaftaran['peserta_belum_isi'] ?? [];
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Manifes {{ $pendaftaran['kode'] }}</title>
    <style>
        @page { margin: 0 0 44px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #475569; margin: 0; }
        .isi { padding: 0 30px 10px; }
        .pita { background-color: {{ $navy }}; padding: 16px 30px 14px; }
        .merek { font-size: 15px; font-weight: bold; color: #fff; letter-spacing: .5px; }
        .merek span { color: {{ $emas }}; }
        .slogan { font-size: 7px; color: #7fb4d6; letter-spacing: 1.8px; text-transform: uppercase; padding-top: 2px; }
        .garis-emas { height: 3px; background-color: {{ $emas }}; font-size: 0; line-height: 0; }
        .judul { font-size: 19px; font-weight: bold; color: {{ $navy }}; }
        .label { font-size: 8px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
        .nilai { font-size: 11.5px; color: {{ $navy }}; font-weight: bold; }
        .bagian { font-size: 8.5px; letter-spacing: 2px; text-transform: uppercase; color: {{ $ocean }}; font-weight: bold; }
        .rule { height: 2px; width: 30px; background-color: {{ $emas }}; font-size: 0; line-height: 0; margin-top: 4px; }
        .titik { background-color: {{ $navy }}; color: #fff; padding: 6px 12px; font-size: 11px; font-weight: bold; }
        .orang td { padding: 7px 12px; border-bottom: 1px solid #e9eff5; font-size: 11px; }
        .awas { background-color: #fff5f5; }
        .cap-awas { color: #b91c1c; font-weight: bold; font-size: 9px; }
        .kotak-awas { background-color: #fff5f5; border-left: 3px solid #dc2626; padding: 10px 14px; }
        .kotak-kuning { background-color: #fffaf0; border-left: 3px solid {{ $emas }}; padding: 9px 14px; }
        .kaki { position: fixed; bottom: 0; left: 0; right: 0; background-color: {{ $navy }};
                padding: 8px 30px; font-size: 8px; color: #7fb4d6; }
    </style>
</head>

<body>
    <table width="100%" cellpadding="0" cellspacing="0" class="pita">
        <tr>
            <td>
                <div class="merek">ORCHA <span>JOURNEY</span></div>
                <div class="slogan">{{ config('orcha.slogan', 'Teman Setia Perjalanan Anda!') }}</div>
            </td>
            <td align="right" style="color:#7fb4d6;font-size:9px;">
                Manifes Tour Leader<br>
                Dicetak {{ now()->translatedFormat('d M Y, H:i') }} WIB
            </td>
        </tr>
    </table>
    <div class="garis-emas"></div>

    <div class="isi">
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;">
            <tr>
                <td>
                    <div class="judul">{{ data_get($pendaftaran, 'paket.nama') ?: 'Open Trip' }}</div>
                    <div style="font-size:10px;color:#64748b;padding-top:3px;">
                        {{ $pendaftaran['tanggal_berangkat']
                            ? \Carbon\Carbon::parse($pendaftaran['tanggal_berangkat'])->translatedFormat('l, d F Y')
                            : 'Tanggal menyusul' }}
                    </div>
                </td>
                <td align="right">
                    <div class="label">Kode</div>
                    <div class="nilai" style="font-family:Courier,monospace;font-size:13px;">{{ $pendaftaran['kode'] }}</div>
                </td>
            </tr>
        </table>

        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:14px;">
            <tr>
                @foreach ([
                    ['Pemesan', $pendaftaran['nama']],
                    ['WhatsApp pemesan', $pendaftaran['whatsapp']],
                    ['Jumlah peserta', $pendaftaran['jumlah_peserta'] . ' orang'],
                ] as [$label, $nilai])
                    <td width="33%" valign="top">
                        <div class="label">{{ $label }}</div>
                        <div class="nilai">{{ $nilai }}</div>
                    </td>
                @endforeach
            </tr>
        </table>

        {{-- ============ PERLU PERHATIAN ============
             Ditaruh paling atas, sebelum daftar jemputan: kalau ada yang perlu
             diperhatikan, itu harus terbaca sebelum bus berangkat. --}}
        @if ($perluPerhatian->isNotEmpty())
            <table width="100%" cellpadding="0" cellspacing="0" class="kotak-awas" style="margin-top:16px;">
                <tr>
                    <td>
                        <div class="bagian" style="color:#b91c1c;">Perlu Perhatian Khusus</div>
                        @foreach ($perluPerhatian as $satu)
                            <div style="font-size:11px;color:#7f1d1d;padding-top:5px;">
                                <strong>{{ $satu['nama_peserta'] }}</strong>
                                @if ($satu['golongan_darah'] ?? null)
                                    <span style="font-size:9px;">(gol. darah {{ $satu['golongan_darah'] }})</span>
                                @endif
                                — {{ implode('; ', $satu['alasan_perhatian'] ?? []) }}
                            </div>
                        @endforeach
                    </td>
                </tr>
            </table>
        @endif

        @if (! empty($belumIsi))
            <table width="100%" cellpadding="0" cellspacing="0" class="kotak-kuning" style="margin-top:10px;">
                <tr>
                    <td style="font-size:10px;color:#8a6410;">
                        <strong>Belum mengisi riwayat kesehatan:</strong> {{ implode(', ', $belumIsi) }}.
                        Tanyakan langsung saat berkumpul — terutama alergi dan obat rutin.
                    </td>
                </tr>
            </table>
        @endif

        {{-- ============ JEMPUTAN ============
             Dikelompokkan per titik, bukan menurut abjad: yang dikerjakan tour
             leader adalah berhenti di satu titik lalu memanggil nama. --}}
        <div style="margin-top:20px;">
            <div class="bagian">Urutan Jemputan</div>
            <div class="rule"></div>
        </div>

        @php
            $kelompok = ! empty($perTitik)
                ? $perTitik
                : ['Titik jemput menyusul' => collect($peserta)->pluck('nama')->all()];
        @endphp

        @foreach ($kelompok as $titik => $orang)
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:12px;">
                <tr>
                    <td class="titik">
                        {{ $titik ?: 'Belum dipilih' }}
                        <span style="font-weight:normal;font-size:9px;color:#a9c9de;">
                            &middot; {{ count($orang) }} orang
                        </span>
                    </td>
                </tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0" class="orang">
                @foreach ($orang as $nama)
                    @php
                        $k = $cari($nama);
                        $awas = ($k['tingkat_perhatian'] ?? '') === 'tinggi';
                    @endphp
                    <tr class="{{ $awas ? 'awas' : '' }}">
                        <td width="4%">{{ $loop->iteration }}.</td>
                        <td width="30%"><strong style="color:{{ $navy }};">{{ $nama }}</strong></td>
                        <td width="26%" style="font-size:10px;">
                            @if ($k)
                                {{ collect([
                                    ($k['usia'] ?? null) ? $k['usia'] . ' th' : null,
                                    $k['jenis_kelamin'] ?? null,
                                    ($k['golongan_darah'] ?? null) ? 'gol. ' . $k['golongan_darah'] : null,
                                ])->filter()->implode(' · ') }}
                            @else
                                <span style="color:#94a3b8;">data kesehatan belum diisi</span>
                            @endif
                        </td>
                        <td width="24%" style="font-size:10px;">
                            @if (data_get($k, 'kontak_darurat.hp'))
                                {{ data_get($k, 'kontak_darurat.nama') }}
                                {{ data_get($k, 'kontak_darurat.hp') }}
                            @else
                                <span style="color:#94a3b8;">—</span>
                            @endif
                        </td>
                        <td width="16%" align="right">
                            @if ($awas)
                                <span class="cap-awas">PERHATIAN</span>
                            @elseif (($k['tingkat_perhatian'] ?? '') === 'sedang')
                                <span style="font-size:9px;color:#8a6410;">catatan</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        @endforeach

        {{-- Catatan ringan dikumpulkan di bawah supaya daftar jemputannya tetap
             pendek dan cepat dipindai. --}}
        @php
            $catatan = collect($riwayat)->filter(fn ($s) => ! empty($s['alasan_catatan']));
        @endphp

        @if ($catatan->isNotEmpty())
            <div style="margin-top:20px;">
                <div class="bagian">Catatan Lapangan</div>
                <div class="rule"></div>
            </div>
            <table width="100%" cellpadding="0" cellspacing="0" class="orang" style="margin-top:8px;">
                @foreach ($catatan as $satu)
                    <tr>
                        <td width="28%"><strong style="color:{{ $navy }};">{{ $satu['nama_peserta'] }}</strong></td>
                        <td style="font-size:10px;">{{ implode(' · ', $satu['alasan_catatan']) }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if ($pendaftaran['catatan'] ?? null)
            <table width="100%" cellpadding="0" cellspacing="0" class="kotak-kuning" style="margin-top:14px;">
                <tr>
                    <td style="font-size:10px;color:#8a6410;">
                        <strong>Catatan dari pemesan:</strong> {{ $pendaftaran['catatan'] }}
                    </td>
                </tr>
            </table>
        @endif
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" class="kaki">
        <tr>
            <td>Manifes internal &middot; memuat data pribadi peserta &middot; jangan dibagikan ke luar tim</td>
            <td align="right" style="color:{{ $emas }};font-family:Courier,monospace;">{{ $pendaftaran['kode'] }}</td>
        </tr>
    </table>
</body>

</html>
