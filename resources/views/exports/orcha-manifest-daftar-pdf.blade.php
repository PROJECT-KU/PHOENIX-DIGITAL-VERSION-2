{{-- Manifes rombongan — gabungan banyak pendaftaran.

     Open trip dibentuk dari banyak pendaftaran terpisah yang berangkat di hari
     yang sama. Tour leader tidak membawa dua belas lembar; ia membawa satu.

     Karena itu pengelompokannya BUKAN per pendaftaran, melainkan per titik
     jemput: yang dikerjakan di lapangan adalah berhenti di satu titik lalu
     memanggil nama, tidak peduli siapa yang mendaftarkan siapa. --}}
@php
    $logo = file_exists(public_path('orcha-logo.png')) ? public_path('orcha-logo.png') : null;

    $navy = '#0f2d4a';
    $ocean = '#1d6fa5';
    $emas = '#ffc74e';

    // Semua riwayat kesehatan digabung, dicari berdasarkan nama peserta.
    $kesehatanSemua = collect($kesehatan)
        ->flatten(1)
        ->keyBy(fn ($satu) => mb_strtolower(trim($satu['nama_peserta'] ?? '')));

    $cari = fn ($nama) => $kesehatanSemua[mb_strtolower(trim($nama ?? ''))] ?? null;

    // Peserta dikumpulkan per titik jemput, lintas pendaftaran.
    $perTitik = [];
    $totalPeserta = 0;

    foreach ($daftar as $satu) {
        foreach ($satu['peserta'] ?? [] as $orang) {
            $titik = trim($orang['titik_jemput'] ?? '') ?: 'Titik jemput belum dipilih';
            $perTitik[$titik][] = [
                'nama' => $orang['nama'] ?? '—',
                'kode' => $satu['kode'],
                'pemesan' => $satu['nama'],
                'wa' => $satu['whatsapp'],
            ];
            $totalPeserta++;
        }
    }

    ksort($perTitik);

    $perluPerhatian = $kesehatanSemua->filter(fn ($s) => ($s['tingkat_perhatian'] ?? '') === 'tinggi');

    $paket = collect($daftar)->pluck('paket.nama')->filter()->unique();
    $tanggal = collect($daftar)->pluck('tanggal_berangkat')->filter()->unique();
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Manifes Rombongan</title>
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
        .awas { background-color: #fff5f5; }
        .cap-awas { color: #b91c1c; font-weight: bold; font-size: 9px; }
        .kotak-awas { background-color: #fff5f5; border-left: 4px solid #dc2626; }
        .kotak-awas td { padding: 11px 18px 12px 20px; }
        .kaki-luar { position: fixed; bottom: -56px; left: 0; right: 0; }
        .kaki-emas { height: 2px; background-color: {{ $emas }}; font-size: 0; line-height: 0; }
        .kaki { background-color: {{ $navy }}; padding: 8px 30px; font-size: 8px; color: #7fb4d6; }
    </style>
</head>

<body>
    <table width="100%" cellpadding="0" cellspacing="0" class="pita">
        <tr>
            @if ($logo)
                <td width="46" valign="middle"><img src="{{ $logo }}" width="38" height="38" alt=""></td>
            @endif
            <td valign="middle" style="{{ $logo ? 'padding-left:10px;' : '' }}">
                <div class="merek">ORCHA <span>JOURNEY</span></div>
                <div class="slogan">{{ config('orcha.slogan', 'Teman Setia Perjalanan Anda!') }}</div>
            </td>
            <td align="right" style="color:#7fb4d6;font-size:9px;">
                Manifes Rombongan<br>
                Dicetak {{ now()->translatedFormat('d M Y, H:i') }} WIB
            </td>
        </tr>
    </table>
    <div class="garis-emas"></div>

    <div class="isi">
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;">
            <tr>
                <td>
                    <div class="judul">{{ $paket->count() === 1 ? $paket->first() : 'Gabungan '.$paket->count().' paket' }}</div>
                    <div style="font-size:10px;color:#64748b;padding-top:3px;">
                        {{ $tanggal->count() === 1
                            ? \Carbon\Carbon::parse($tanggal->first())->translatedFormat('l, d F Y')
                            : $tanggal->count().' tanggal keberangkatan' }}
                    </div>
                </td>
                <td align="right">
                    <div class="label">Jumlah</div>
                    <div class="nilai">{{ count($daftar) }} pendaftaran · {{ $totalPeserta }} peserta</div>
                </td>
            </tr>
        </table>

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

        <div style="margin-top:20px;">
            <div class="bagian">Urutan Jemputan</div>
            <div class="rule"></div>
        </div>

        @foreach ($perTitik as $titik => $orang)
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:12px;">
                <tr>
                    <td class="titik">
                        {{ $titik }}
                        <span style="font-weight:normal;font-size:9px;color:#a9c9de;">
                            &middot; {{ count($orang) }} orang
                        </span>
                    </td>
                </tr>
            </table>

            <table width="100%" cellpadding="0" cellspacing="0" class="orang">
                @foreach ($orang as $satu)
                    @php
                        $k = $cari($satu['nama']);
                        $awas = ($k['tingkat_perhatian'] ?? '') === 'tinggi';
                    @endphp
                    <tr class="{{ $awas ? 'awas' : '' }}">
                        <td width="4%">{{ $loop->iteration }}.</td>
                        <td width="26%"><strong style="color:{{ $navy }};">{{ $satu['nama'] }}</strong></td>
                        <td width="22%" style="font-size:10px;">
                            @if ($k)
                                {{ collect([
                                    ($k['usia'] ?? null) ? $k['usia'].' th' : null,
                                    $k['jenis_kelamin'] ?? null,
                                    ($k['golongan_darah'] ?? null) ? 'gol. '.$k['golongan_darah'] : null,
                                ])->filter()->implode(' · ') }}
                            @else
                                <span style="color:#94a3b8;">kesehatan belum diisi</span>
                            @endif
                        </td>
                        <td width="22%" style="font-size:10px;">
                            @if (data_get($k, 'kontak_darurat.hp'))
                                {{ data_get($k, 'kontak_darurat.nama') }} {{ data_get($k, 'kontak_darurat.hp') }}
                            @else
                                <span style="color:#94a3b8;">—</span>
                            @endif
                        </td>
                        {{-- Kode pendaftarannya tetap disebut: kalau ada yang tidak
                             muncul, tour leader tahu harus menelepon siapa. --}}
                        <td width="18%" style="font-size:9px;color:#64748b;">
                            {{ $satu['kode'] }}<br>{{ $satu['wa'] }}
                        </td>
                        <td width="8%" align="right">
                            @if ($awas)
                                <span class="cap-awas">PERHATIAN</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        @endforeach
    </div>

    <div class="kaki-luar">
        <div class="kaki-emas"></div>
        <table width="100%" cellpadding="0" cellspacing="0" class="kaki">
            <tr>
                @if ($logo)
                    <td width="30" valign="middle"><img src="{{ $logo }}" width="22" height="22" alt=""></td>
                @endif
                <td valign="middle" style="{{ $logo ? 'padding-left:8px;' : '' }}">
                    <span style="color:#fff;font-weight:bold;font-size:9px;letter-spacing:.6px;">
                        ORCHA <span style="color:{{ $emas }};">JOURNEY</span>
                    </span>
                    <div style="font-size:7.5px;padding-top:1px;">
                        Manifes internal &middot; memuat data pribadi peserta &middot; jangan dibagikan ke luar tim
                    </div>
                </td>
                <td align="right" valign="middle" style="font-size:8px;">
                    {{ count($daftar) }} pendaftaran &middot; {{ $totalPeserta }} peserta
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
