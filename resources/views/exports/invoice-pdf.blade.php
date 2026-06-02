<!DOCTYPE html>
<html>

<head>
    <title>Invoice</title>
    <style>
        @page {
            margin: 0px;
            box-sizing: border-box;
            padding: 0px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0px;
            padding: 0px;
        }

        /* Layout untuk konten agar tidak tertutup header/footer gambar */
        .content {
            margin-top: 100px;
            margin-left: 40px;
            margin-right: 40px;
            margin-bottom: 50px;
        }

        .header-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-wrapper td {
            border: none;
            padding: 10px;
            vertical-align: middle;
        }

        .header-title {
            font-size: 64px;
            font-weight: bold;
            color: #000;
            width: 60%;
            text-align: left;
        }

        .header-logo {
            width: 150px;
            text-align: right;
        }

        .header-image {
            position: fixed;
            top: -170px;
            left: 0;
            width: 100%;
            height: auto;
            transform: rotate(180deg);
            z-index: -1;
        }

        .footer-image {
            position: fixed;
            bottom: -130;
            left: 0;
            width: 100%;
            height: auto;
            z-index: -1;
        }

        .contact {
            background-color: orange;
            position: fixed;
            bottom: 0;
            left: 0;
            padding: 12px 50px;
            z-index: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table-contact {
            width: 30%;
            position: fixed;
            bottom: 48px;
            left: 0;
            z-index: 1;
        }

        th {
            border-top: 1px solid orange;
            border-bottom: 1px solid orange;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        td {
            border: none;
            padding: 4px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        /* Helper Classes */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .invoice-meta {
            width: 100%;
            margin-bottom: 20px;
        }

        .invoice-meta td {
            border: none;
            padding: 2px;
        }
    </style>
</head>

<body>
    {{-- Gambar Header Full Width --}}
    <img src="{{ public_path('assets/img/footer-invoice.png') }}" class="header-image">

    <div class="content">
        <table class="header-wrapper">
            <tr>
                <td class="header-title">INVOICE</td>
                <td class="header-logo">
                    <img src="{{ public_path('assets/img/logophoenix2.png')}}" style="object-fit: cover; height:auto; width:100%;">
                </td>
            </tr>
        </table>
        {{-- Judul & Alamat --}}
        <table class=" invoice-meta">
            <tr>
                <td width="60%">
                    <strong>Kepada:</strong><br>
                    Pimpinan Rumah Scopus<br>
                    Bangunsari, Jl. Bangunsari, Bangun Kerto,<br>
                    Turi, Sleman Regency, DIY 55551<br>
                    081226883280
                </td>
                <td width="40%" class="text-right" style="vertical-align: top;">
                    <table style="border-collapse:collapse;">
                        <tr>
                            <td style="border: 1px solid #000;text-align:center;">{{$invoiceNumber}}</td>
                            <td style="border: 1px solid#000;text-align:center;">{{$date}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Tabel Item --}}
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="30%">Deskripsi</th>
                    <th width="15%">Periode</th>
                    <th width="20%">Jumlah Peserta</th>
                    <th width="15%">Harga Satuan</th>
                    <th width="15%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $item->nama_camp }}</strong> <br>
                        <small>Batch #{{ $item->batch_camp }}</small>
                    </td>
                    <td class="text-center">1 Bulan</td> {{-- Atau hitung diff date --}}
                    <td class="text-center">{{ $item->total_peserta }}</td>
                    <td class="text-center">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-center">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                </tr>
                @endforeach

                {{-- Grand Total --}}
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr style="margin-top: 100px;">
                    <td colspan="5" class="text-right text-bold" style="font-size: 16px; padding:10px;">Total Biaya</td>
                    <td class="text-center text-bold" style="background-color: yellow; font-size:16px; padding:10px;">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
        <hr style="color: orange;">

        {{-- Informasi Pembayaran & TTD --}}
        <div style="margin-top: 30px;">
            <table style="border: none;">
                <tr>
                    <td style="border: none; width: 60%;">
                        <strong>Instruksi Pembayaran:</strong><br>
                        Pembayaran dapat dilakukan melalui transfer bank:<br>
                        <strong>Bank BRI</strong><br>
                        No. Rek: 3584-0103-4310-539<br>
                        A.n: Biwi Faiza Tu Zulaikhah YS
                    </td>
                    <td style="border: none; width: 40%; text-align: center;">
                        <p>Hormat Kami,</p>

                        <div style="position: relative; height: 100px; width: 200px; margin: 0 auto;">
                            <img src="{{ public_path('assets/img/stempel.png') }}"
                                style="position: absolute; top: 0; left: 10px; width: 100px; opacity: 0.8; z-index:100;">

                            <img src="{{ public_path('assets/img/ttd.png') }}"
                                style="position: absolute; top: 10px; right: 20px; width: 120px;">
                        </div>

                        <br>
                        <strong>Biwi Fa'iza Tu Zulaikhah</strong><br>
                        <small>Finance Dept.</small>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div>
        <table class="table-contact">
            <tr class="contact">
                <td class="text-right">
                    <img src="{{ public_path('assets/img/wa.png') }}" style="width: 20px; height:auto;">
                </td>
                <td style="text-align: left; vertical-align: middle;">
                    Contact center: +6289505967995
                </td>
            </tr>
        </table>
    </div>

    {{-- Gambar Footer Full Width --}}
    <img src="{{ public_path('assets/img/footer-invoice.png') }}" class="footer-image">

</body>

</html>