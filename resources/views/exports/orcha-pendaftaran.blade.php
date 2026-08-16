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

@php
    // PhpSpreadsheet membaca gaya menempel dari HTML, jadi warnanya ditulis di
    // sini. Tanpa ini lembarnya terbuka sebagai teks polos tanpa batas antar
    // bagian, dan yang membacanya harus menghitung baris sendiri.
    $judulUtama = 'background-color:#0F2D4A;color:#FFFFFF;font-size:15px;font-weight:bold;padding:8px;';
    $judulBagian = 'background-color:#1D6FA5;color:#FFFFFF;font-weight:bold;letter-spacing:1px;padding:6px;';
    $kepalaKolom = 'background-color:#EEF6FB;color:#0F2D4A;font-weight:bold;border:1px solid #CFE4F2;padding:5px;';
    $labelBaris = 'background-color:#F8FBFD;color:#64748B;font-weight:bold;border:1px solid #E9EFF5;padding:5px;';
    $isiBaris = 'border:1px solid #E9EFF5;padding:5px;';
    $isiAngka = 'border:1px solid #E9EFF5;padding:5px;text-align:right;';
    $awas = 'background-color:#FFF5F5;color:#B91C1C;font-weight:bold;border:1px solid #E9EFF5;padding:5px;';
@endphp

<table>
    <tr><th colspan="8" style="{{ $judulUtama }}">DATA PENDAFTARAN OPEN TRIP — {{ $pendaftaran['kode'] }}</th></tr>
    <tr><td colspan="8" style="color:#94A3B8;">Diunduh {{ now()->translatedFormat('d F Y, H:i') }} WIB oleh {{ auth()->user()->name ?? '-' }} · rahasia internal</td></tr>
    <tr><td colspan="8"></td></tr>

    <tr><th colspan="8" style="{{ $judulBagian }}">PEMESAN</th></tr>
    <tr><td style="{{ $labelBaris }}">Kode</td><td colspan="7">{{ $pendaftaran['kode'] }}</td></tr>
    <tr><td style="{{ $labelBaris }}">Nama</td><td colspan="7" style="{{ $isiBaris }}">{{ $pendaftaran['nama'] }}</td></tr>
    <tr><td style="{{ $labelBaris }}">WhatsApp</td><td colspan="7" style="{{ $isiBaris }}">{{ $pendaftaran['whatsapp'] }}</td></tr>
    <tr><td style="{{ $labelBaris }}">Email</td><td colspan="7" style="{{ $isiBaris }}">{{ $pendaftaran['email'] }}</td></tr>
    <tr><td style="{{ $labelBaris }}">Mendaftar</td><td colspan="7" style="{{ $isiBaris }}">{{ $tanggal($pendaftaran['dibuat_pada'] ?? null, 'd/m/Y H:i') }}</td></tr>
    <tr><td style="{{ $labelBaris }}">Status</td><td colspan="7" style="{{ $isiBaris }}">{{ $pendaftaran['status_label'] }}</td></tr>
    <tr><td style="{{ $labelBaris }}">Catatan pemesan</td><td colspan="7" style="{{ $isiBaris }}">{{ $pendaftaran['catatan'] }}</td></tr>
    <tr><td colspan="8"></td></tr>

    <tr><th colspan="8" style="{{ $judulBagian }}">PERJALANAN</th></tr>
    <tr><td style="{{ $labelBaris }}">Paket</td><td colspan="7" style="{{ $isiBaris }}">{{ data_get($pendaftaran, 'paket.nama') }}</td></tr>
    <tr><td style="{{ $labelBaris }}">Tanggal berangkat</td><td colspan="7" style="{{ $isiBaris }}">{{ $tanggal($pendaftaran['tanggal_berangkat'] ?? null, 'l, d F Y') }}</td></tr>
    <tr><td style="{{ $labelBaris }}">Jumlah peserta</td><td colspan="7" style="{{ $isiBaris }}">{{ $pendaftaran['jumlah_peserta'] }}</td></tr>
    <tr><td style="{{ $labelBaris }}">Titik jemput</td><td colspan="7" style="{{ $isiBaris }}">{{ $pendaftaran['titik_jemput'] }}</td></tr>
    <tr><td colspan="8"></td></tr>

    @if ($tagihan)
        <tr><th colspan="8" style="{{ $judulBagian }}">TAGIHAN</th></tr>
        <tr><td style="{{ $labelBaris }}">Total</td><td colspan="7" style="{{ $isiBaris }}">{{ $tagihan['total'] }}</td></tr>
        <tr><td style="{{ $labelBaris }}">Sudah dibayar</td><td colspan="7" style="{{ $isiBaris }}">{{ $tagihan['sudah'] }}</td></tr>
        <tr><td style="{{ $labelBaris }}">Sisa</td><td colspan="7" style="{{ $isiBaris }}">{{ $tagihan['sisa'] }}</td></tr>
        <tr><td style="{{ $labelBaris }}">Lunas</td><td colspan="7" style="{{ $isiBaris }}">{{ ($tagihan['lunas'] ?? false) ? 'Ya' : 'Belum' }}</td></tr>
        <tr><td colspan="8"></td></tr>
    @endif

    {{-- ============ PESERTA & RIWAYAT KESEHATAN ============
         Satu baris satu peserta, kolomnya tetap — bentuk yang bisa disaring
         dan dihitung, bukan dibaca seperti cerita. --}}
    <tr><th colspan="8" style="{{ $judulBagian }}">PESERTA &amp; RIWAYAT KESEHATAN</th></tr>
    <tr>
        <th style="{{ $kepalaKolom }}">Nama</th>
        <th style="{{ $kepalaKolom }}">Titik jemput</th>
        <th style="{{ $kepalaKolom }}">Kesehatan diisi</th>
        <th style="{{ $kepalaKolom }}">Tingkat perhatian</th>
        <th style="{{ $kepalaKolom }}">Usia</th>
        <th style="{{ $kepalaKolom }}">Jenis kelamin</th>
        <th style="{{ $kepalaKolom }}">Gol. darah</th>
        <th style="{{ $kepalaKolom }}">Tinggi (cm)</th>
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

    <tr><th colspan="8" style="{{ $judulBagian }}">RINCIAN KESEHATAN</th></tr>
    <tr>
        <th style="{{ $kepalaKolom }}">Nama</th>
        <th style="{{ $kepalaKolom }}">Kondisi khusus</th>
        <th style="{{ $kepalaKolom }}">Riwayat penyakit</th>
        <th style="{{ $kepalaKolom }}">Alergi</th>
        <th style="{{ $kepalaKolom }}">Obat rutin</th>
        <th style="{{ $kepalaKolom }}">Pantangan makanan</th>
        <th style="{{ $kepalaKolom }}">Kemampuan renang</th>
        <th style="{{ $kepalaKolom }}">Kontak darurat</th>
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

    <tr><th colspan="8" style="{{ $judulBagian }}">BUKTI PEMBAYARAN</th></tr>
    <tr>
        <th style="{{ $kepalaKolom }}">Tanggal transfer</th>
        <th style="{{ $kepalaKolom }}">Jenis</th>
        <th style="{{ $kepalaKolom }}">Nominal</th>
        <th style="{{ $kepalaKolom }}">Bank</th>
        <th style="{{ $kepalaKolom }}">Atas nama</th>
        <th style="{{ $kepalaKolom }}">Status</th>
        <th style="{{ $kepalaKolom }}">Catatan admin</th>
        <th style="{{ $kepalaKolom }}">Dikirim</th>
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
        <tr><th colspan="8" style="{{ $judulBagian }}">PENGAJUAN PEMBATALAN</th></tr>
        <tr><td style="{{ $labelBaris }}">Pemohon</td><td colspan="7" style="{{ $isiBaris }}">{{ $pembatalan['nama_pemohon'] }}</td></tr>
        <tr><td style="{{ $labelBaris }}">Alasan</td><td colspan="7" style="{{ $isiBaris }}">{{ $pembatalan['alasan_label'] }}</td></tr>
        <tr><td style="{{ $labelBaris }}">Penjelasan</td><td colspan="7" style="{{ $isiBaris }}">{{ $pembatalan['penjelasan'] }}</td></tr>
        <tr><td style="{{ $labelBaris }}">Peserta dibatalkan</td><td colspan="7" style="{{ $isiBaris }}">{{ $pembatalan['jumlah_dibatalkan'] }}</td></tr>
        <tr><td style="{{ $labelBaris }}">Rekening pengembalian</td><td colspan="7" style="{{ $isiBaris }}">{{ $pembatalan['rekening'] }}</td></tr>
        <tr><td style="{{ $labelBaris }}">Status</td><td colspan="7" style="{{ $isiBaris }}">{{ $pembatalan['status'] }}</td></tr>
    @endif
</table>
