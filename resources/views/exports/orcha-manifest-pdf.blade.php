{{-- Manifes tour leader.

     Dibaca sambil berdiri di titik jemput, sering di layar ponsel. Urutannya
     mengikuti urutan pekerjaan hari itu: menjemput dulu, baru menangani orang.

     Yang sengaja TIDAK masuk: rincian medis panjang, nomor rekening, dan
     riwayat pembayaran. Berkas ini berpindah tangan di lapangan; makin sedikit
     yang tidak dipakai, makin aman kalau tertinggal di kursi bus.

     Dompdf tidak mengenal flexbox, jadi tata letaknya memakai tabel. --}}
@php
    // Berkasnya disalin ke lemon supaya dompdf tidak perlu mengunduh gambar
    // dari server Orcha saat merender — kalau servernya lambat, berkasnya
    // terbit tanpa logo.
    $logo = file_exists(public_path('orcha-logo.png')) ? public_path('orcha-logo.png') : null;

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
        @page { margin: 0 0 56px; }
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
        .kotak-absen { width: 13px; height: 13px; border: 1.4px solid {{ $navy }}; font-size: 0; line-height: 0; }
        .tajuk-orang td { padding: 4px 12px; font-size: 7.5px; letter-spacing: 1px;
            text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #e9eff5; }
        .hitung-naik { padding: 6px 12px 12px; font-size: 9.5px; color: #64748b; }
        .garis-isi { display: inline-block; width: 46px; border-bottom: 1px solid #94a3b8; }
        .awas { background-color: #fff5f5; }
        .cap-awas { color: #b91c1c; font-weight: bold; font-size: 9px; }
        .kotak-awas { background-color: #fff5f5; border-left: 4px solid #dc2626; }
        .kotak-awas td { padding: 11px 18px 12px 20px; }
        .kotak-kuning { background-color: #fffaf0; border-left: 4px solid {{ $emas }}; }
        .kotak-kuning td { padding: 10px 18px 10px 20px; }
        /* Dompdf mengukur bottom dari tepi DALAM margin halaman, jadi offsetnya
           dibuat negatif sebesar margin itu supaya pitanya menempel ke tepi
           kertas — bukan mengambang dengan pias putih di bawahnya. */
        .kaki-luar { position: fixed; bottom: -56px; left: 0; right: 0; }
        .kaki-emas { height: 2px; background-color: {{ $emas }}; font-size: 0; line-height: 0; }
        .kaki { background-color: {{ $navy }};
                padding: 8px 30px; font-size: 8px; color: #7fb4d6; }
    </style>
</head>

<body>
    <table width="100%" cellpadding="0" cellspacing="0" class="pita">
        <tr>
            {{-- Logo dibaca dari berkas di lemon, bukan tautan ke server Orcha:
                 dompdf mengunduh gambar jarak jauh saat merender, dan kalau
                 servernya sedang lambat berkasnya terbit tanpa logo. --}}
            @if ($logo)
                <td width="46" valign="middle">
                    <img src="{{ $logo }}" width="38" height="38" alt="">
                </td>
            @endif
            <td valign="middle" style="{{ $logo ? 'padding-left:10px;' : '' }}">
                <div class="merek">ORCHA <span>JOURNEY</span></div>
                <div class="slogan">{{ config('orcha.slogan', 'Teman Setia Perjalanan Anda!') }}</div>
            </td>
            <td align="right" style="color:#7fb4d6;font-size:9px;">
                Manifes Tour Leader<br>
                Dicetak {{ now()->locale('id')->translatedFormat('d M Y, H:i') }} WIB
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
                            ? \Carbon\Carbon::parse($pendaftaran['tanggal_berangkat'])->locale('id')->translatedFormat('l, d F Y')
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
            <div class="bagian">Urutan Jemputan &amp; Absen Naik</div>
            <div class="rule"></div>
            <div style="font-size:9.5px;color:#64748b;padding-top:6px;">
                Centang kotak di kolom kiri begitu peserta naik kendaraan.
            </div>
        </div>

        @php
            $namaPeserta = collect($peserta)->pluck('nama')->filter()->all();

            $kelompok = ! empty($perTitik)
                ? $perTitik
                : ($namaPeserta !== [] ? ['Titik jemput menyusul' => $namaPeserta] : []);
        @endphp

        {{-- Nama pesertanya belum didata satu per satu.

             Dulu keadaan ini menghasilkan satu kotak titik jemput bertuliskan
             "0 orang" dan tidak ada baris apa pun di bawahnya — lembaran yang
             tampak seperti rombongan kosong, padahal jumlahnya tertulis jelas
             di kepala halaman yang sama. Sekarang disebutkan apa adanya, dengan
             ruang untuk mencatat namanya di lapangan. --}}
        @if ($kelompok === [])
            <table width="100%" cellpadding="0" cellspacing="0" class="kotak-kuning" style="margin-top:12px;">
                <tr>
                    <td style="font-size:10px;color:#8a6410;">
                        <strong>Nama peserta belum didata satu per satu.</strong>
                        Pendaftaran ini mencatat {{ $pendaftaran['jumlah_peserta'] ?? '—' }} orang
                        dengan titik jemput
                        &ldquo;{{ $pendaftaran['titik_jemput'] ?: 'belum dipilih' }}&rdquo;.
                        Catat namanya saat rombongan berkumpul, lalu minta pemesan melengkapinya
                        lewat website agar manifes berikutnya utuh.
                    </td>
                </tr>
            </table>

            <div class="hitung-naik" style="padding-top:10px;">
                Sudah naik: <span class="garis-isi">&nbsp;</span>
                dari {{ $pendaftaran['jumlah_peserta'] ?? '—' }} orang
                &nbsp;&middot;&nbsp; Diperiksa oleh: <span class="garis-isi">&nbsp;</span>
            </div>
        @endif

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
                <tr class="tajuk-orang">
                    <td width="6%" align="center">Naik</td>
                    <td width="4%">No</td>
                    <td width="28%">Nama peserta</td>
                    <td width="24%">Kesehatan</td>
                    <td width="24%">Kontak darurat</td>
                    <td width="14%"></td>
                </tr>
                @foreach ($orang as $nama)
                    @php
                        $k = $cari($nama);
                        $awas = ($k['tingkat_perhatian'] ?? '') === 'tinggi';
                    @endphp
                    <tr class="{{ $awas ? 'awas' : '' }}">
                        <td width="6%" align="center">
                            <table cellpadding="0" cellspacing="0" align="center">
                                <tr><td class="kotak-absen">&nbsp;</td></tr>
                            </table>
                        </td>
                        <td width="4%">{{ $loop->iteration }}.</td>
                        <td width="28%"><strong style="color:{{ $navy }};">{{ $nama }}</strong></td>
                        <td width="24%" style="font-size:10px;">
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
                        <td width="14%" align="right">
                            @if ($awas)
                                <span class="cap-awas">PERHATIAN</span>
                            @elseif (($k['tingkat_perhatian'] ?? '') === 'sedang')
                                <span style="font-size:9px;color:#8a6410;">catatan</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>

            <div class="hitung-naik">
                Sudah naik: <span class="garis-isi">&nbsp;</span> dari {{ count($orang) }} orang
                &nbsp;&middot;&nbsp; Diperiksa oleh: <span class="garis-isi">&nbsp;</span>
            </div>
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

    {{-- Pita kaki menempel ke tepi kertas, berpasangan dengan pita kepala:
         berkasnya jadi berbingkai, bukan menggantung. --}}
    <div class="kaki-luar">
        <div class="kaki-emas"></div>

        <table width="100%" cellpadding="0" cellspacing="0" class="kaki">
            <tr>
                @if ($logo)
                    <td width="30" valign="middle">
                        <img src="{{ $logo }}" width="22" height="22" alt="">
                    </td>
                @endif
                <td valign="middle" style="{{ $logo ? 'padding-left:8px;' : '' }}">
                    <span style="color:#fff;font-weight:bold;font-size:9px;letter-spacing:.6px;">
                        ORCHA <span style="color:{{ $emas }};">JOURNEY</span>
                    </span>
                    <div style="font-size:7.5px;padding-top:1px;">
                        Manifes internal &middot; memuat data pribadi peserta &middot; jangan dibagikan ke luar tim
                    </div>
                </td>
                <td align="right" valign="middle">
                    <div style="font-size:6.5px;color:#6f9cbd;letter-spacing:1.4px;text-transform:uppercase;">
                        Kode Pendaftaran
                    </div>
                    <div style="color:{{ $emas }};font-family:Courier,monospace;font-weight:bold;font-size:9px;">
                        {{ $pendaftaran['kode'] }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
