{{-- Data lengkap satu pendaftaran, untuk dibuka di Excel.

     Dibuat satu lembar bersambung, bukan beberapa lembar terpisah: yang
     mengerjakannya biasanya menyaring dan menyalin ke berkas lain, dan itu
     lebih mudah kalau semuanya ada di satu tempat. --}}
@php
    $tagihan = $pendaftaran['tagihan'] ?? [];
    $peserta = $pendaftaran['peserta'] ?? [];
    $pembayaran = $pendaftaran['pembayaran'] ?? [];
    $pembatalan = $pendaftaran['pembatalan'] ?? null;

    $sudahIsi = collect($riwayat)->keyBy(fn ($satu) => mb_strtolower(trim($satu['nama_peserta'] ?? '')));

    $tanggal = fn ($nilai, $bentuk = 'd/m/Y') => $nilai
        ? \Carbon\Carbon::parse($nilai)->translatedFormat($bentuk)
        : '';
@endphp

<table>
    <tr><th colspan="8" style="font-size:14px">DATA PENDAFTARAN OPEN TRIP — {{ $pendaftaran['kode'] }}</th></tr>
    <tr><td colspan="8">Diunduh {{ now()->translatedFormat('d F Y, H:i') }} WIB oleh {{ auth()->user()->name ?? '-' }}</td></tr>
    <tr><td colspan="8"></td></tr>

    <tr><th colspan="8">PEMESAN</th></tr>
    <tr><td>Kode</td><td colspan="7">{{ $pendaftaran['kode'] }}</td></tr>
    <tr><td>Nama</td><td colspan="7">{{ $pendaftaran['nama'] }}</td></tr>
    <tr><td>WhatsApp</td><td colspan="7">{{ $pendaftaran['whatsapp'] }}</td></tr>
    <tr><td>Email</td><td colspan="7">{{ $pendaftaran['email'] }}</td></tr>
    <tr><td>Mendaftar</td><td colspan="7">{{ $tanggal($pendaftaran['dibuat_pada'] ?? null, 'd/m/Y H:i') }}</td></tr>
    <tr><td>Status</td><td colspan="7">{{ $pendaftaran['status_label'] }}</td></tr>
    <tr><td>Catatan pemesan</td><td colspan="7">{{ $pendaftaran['catatan'] }}</td></tr>
    <tr><td colspan="8"></td></tr>

    <tr><th colspan="8">PERJALANAN</th></tr>
    <tr><td>Paket</td><td colspan="7">{{ data_get($pendaftaran, 'paket.nama') }}</td></tr>
    <tr><td>Tanggal berangkat</td><td colspan="7">{{ $tanggal($pendaftaran['tanggal_berangkat'] ?? null, 'l, d F Y') }}</td></tr>
    <tr><td>Jumlah peserta</td><td colspan="7">{{ $pendaftaran['jumlah_peserta'] }}</td></tr>
    <tr><td>Titik jemput</td><td colspan="7">{{ $pendaftaran['titik_jemput'] }}</td></tr>
    <tr><td colspan="8"></td></tr>

    @if ($tagihan)
        <tr><th colspan="8">TAGIHAN</th></tr>
        <tr><td>Total</td><td colspan="7">{{ $tagihan['total'] }}</td></tr>
        <tr><td>Sudah dibayar</td><td colspan="7">{{ $tagihan['sudah'] }}</td></tr>
        <tr><td>Sisa</td><td colspan="7">{{ $tagihan['sisa'] }}</td></tr>
        <tr><td>Lunas</td><td colspan="7">{{ ($tagihan['lunas'] ?? false) ? 'Ya' : 'Belum' }}</td></tr>
        <tr><td colspan="8"></td></tr>
    @endif

    {{-- ============ PESERTA & RIWAYAT KESEHATAN ============
         Satu baris satu peserta, kolomnya tetap — bentuk yang bisa disaring
         dan dihitung, bukan dibaca seperti cerita. --}}
    <tr><th colspan="8">PESERTA &amp; RIWAYAT KESEHATAN</th></tr>
    <tr>
        <th>Nama</th>
        <th>Titik jemput</th>
        <th>Kesehatan diisi</th>
        <th>Tingkat perhatian</th>
        <th>Usia</th>
        <th>Jenis kelamin</th>
        <th>Gol. darah</th>
        <th>Tinggi (cm)</th>
    </tr>
    @foreach ($peserta as $satu)
        @php $k = $sudahIsi[mb_strtolower(trim($satu['nama'] ?? ''))] ?? null; @endphp
        <tr>
            <td>{{ $satu['nama'] }}</td>
            <td>{{ $satu['titik_jemput'] }}</td>
            <td>{{ $k ? 'Sudah' : 'Belum' }}</td>
            <td>{{ $k['tingkat_perhatian'] ?? '' }}</td>
            <td>{{ $k['usia'] ?? '' }}</td>
            <td>{{ $k['jenis_kelamin'] ?? '' }}</td>
            <td>{{ $k['golongan_darah'] ?? '' }}</td>
            <td>{{ $k['tinggi_badan'] ?? '' }}</td>
        </tr>
    @endforeach
    <tr><td colspan="8"></td></tr>

    <tr><th colspan="8">RINCIAN KESEHATAN</th></tr>
    <tr>
        <th>Nama</th>
        <th>Kondisi khusus</th>
        <th>Riwayat penyakit</th>
        <th>Alergi</th>
        <th>Obat rutin</th>
        <th>Pantangan makanan</th>
        <th>Kemampuan renang</th>
        <th>Kontak darurat</th>
    </tr>
    @forelse ($riwayat as $satu)
        <tr>
            <td>{{ $satu['nama_peserta'] ?? '' }}</td>
            <td>{{ implode(', ', $satu['kondisi_khusus'] ?? []) }}</td>
            <td>{{ $satu['riwayat_penyakit'] ?? '' }}</td>
            <td>{{ $satu['alergi'] ?? '' }}</td>
            <td>{{ $satu['obat_rutin'] ?? '' }}</td>
            <td>{{ $satu['pantangan_makanan'] ?? '' }}</td>
            <td>{{ $satu['kemampuan_renang'] ?? '' }}</td>
            <td>{{ data_get($satu, 'kontak_darurat.nama') }}
                ({{ data_get($satu, 'kontak_darurat.hubungan') }})
                {{ data_get($satu, 'kontak_darurat.hp') }}</td>
        </tr>
    @empty
        <tr><td colspan="8">Belum ada peserta yang mengisi riwayat kesehatan.</td></tr>
    @endforelse
    <tr><td colspan="8"></td></tr>

    <tr><th colspan="8">BUKTI PEMBAYARAN</th></tr>
    <tr>
        <th>Tanggal transfer</th>
        <th>Jenis</th>
        <th>Nominal</th>
        <th>Bank</th>
        <th>Atas nama</th>
        <th>Status</th>
        <th>Catatan admin</th>
        <th>Dikirim</th>
    </tr>
    @forelse ($pembayaran as $bayar)
        <tr>
            <td>{{ $tanggal($bayar['tanggal_transfer'] ?? null) }}</td>
            <td>{{ $bayar['jenis_label'] }}</td>
            <td>{{ $bayar['nominal'] }}</td>
            <td>{{ $bayar['bank_pengirim'] }}</td>
            <td>{{ $bayar['atas_nama_pengirim'] }}</td>
            <td>{{ $bayar['status_label'] }}</td>
            <td>{{ $bayar['catatan_admin'] }}</td>
            <td>{{ $tanggal($bayar['dibuat_pada'] ?? null, 'd/m/Y H:i') }}</td>
        </tr>
    @empty
        <tr><td colspan="8">Belum ada bukti pembayaran.</td></tr>
    @endforelse

    @if ($pembatalan)
        <tr><td colspan="8"></td></tr>
        <tr><th colspan="8">PENGAJUAN PEMBATALAN</th></tr>
        <tr><td>Pemohon</td><td colspan="7">{{ $pembatalan['nama_pemohon'] }}</td></tr>
        <tr><td>Alasan</td><td colspan="7">{{ $pembatalan['alasan_label'] }}</td></tr>
        <tr><td>Penjelasan</td><td colspan="7">{{ $pembatalan['penjelasan'] }}</td></tr>
        <tr><td>Peserta dibatalkan</td><td colspan="7">{{ $pembatalan['jumlah_dibatalkan'] }}</td></tr>
        <tr><td>Rekening pengembalian</td><td colspan="7">{{ $pembatalan['rekening'] }}</td></tr>
        <tr><td>Status</td><td colspan="7">{{ $pembatalan['status'] }}</td></tr>
    @endif
</table>
