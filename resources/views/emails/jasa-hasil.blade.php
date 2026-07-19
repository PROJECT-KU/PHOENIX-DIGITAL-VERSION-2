<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hasil Pengerjaan Siap</title>
</head>
<body style="margin:0;padding:0;background:#f4f1ec;font-family:Arial,Helvetica,sans-serif;color:#2a1c10;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f1ec;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.06);">
                    {{-- Header --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#fba919,#f0531e);padding:26px 28px;text-align:center;">
                            <div style="font-size:22px;font-weight:800;color:#ffffff;letter-spacing:.3px;">Phoenix Digital</div>
                            <div style="font-size:11px;letter-spacing:3px;color:#fff;opacity:.9;text-transform:uppercase;margin-top:2px;">Warehouse</div>
                        </td>
                    </tr>

                    {{-- Badge --}}
                    <tr>
                        <td style="padding:30px 28px 8px;text-align:center;">
                            <div style="display:inline-block;width:64px;height:64px;line-height:64px;border-radius:50%;background:#ecfdf5;color:#10b981;font-size:30px;">✓</div>
                            <h1 style="font-size:20px;margin:16px 0 6px;color:#2a1c10;">Hasil Pengecekan Sudah Siap!</h1>
                            <p style="font-size:14px;line-height:1.6;color:#7a6449;margin:0;">
                                Halo{{ $order->customer && $order->customer->nama ? ' '.$order->customer->nama : '' }},
                                {{ $upload->hasil_docx_path ? 'pengerjaan dokumen Anda' : 'hasil pengecekan dokumen Anda' }}
                                sudah selesai dan siap diunduh.
                            </p>
                        </td>
                    </tr>

                    {{-- Ringkasan hasil --}}
                    <tr>
                        <td style="padding:20px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fbf5ee;border:1px solid #efe2d2;border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div style="font-size:12px;color:#9a8a79;text-transform:uppercase;letter-spacing:.06em;">Nomor Pesanan</div>
                                        <div style="font-size:16px;font-weight:800;color:#f0531e;margin-top:2px;">{{ $order->order_number }}</div>
                                        <hr style="border:none;border-top:1px dashed #e7d8c6;margin:14px 0;">
                                        <div style="font-size:12px;color:#9a8a79;text-transform:uppercase;letter-spacing:.06em;">Dokumen{{ $upload->jenisLabel() ? ' — '.$upload->jenisLabel() : '' }}</div>
                                        <div style="font-size:14px;font-weight:700;color:#2a1c10;margin-top:2px;word-break:break-all;">{{ $upload->nama_asli }}</div>

                                        @if (! is_null($upload->persentase) || ! is_null($upload->persentase_ai))
                                        <hr style="border:none;border-top:1px dashed #e7d8c6;margin:14px 0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            @if (! is_null($upload->persentase))
                                            <tr>
                                                <td style="font-size:13px;color:#7a6449;padding:3px 0;">Tingkat Kemiripan</td>
                                                <td align="right" style="padding:3px 0;"><span style="display:inline-block;background:#eef2ff;color:#4338ca;font-weight:800;font-size:15px;padding:5px 14px;border-radius:99px;">{{ $upload->persentase }}%</span></td>
                                            </tr>
                                            @endif
                                            @if (! is_null($upload->persentase_ai))
                                            <tr>
                                                <td style="font-size:13px;color:#7a6449;padding:3px 0;">Terdeteksi AI</td>
                                                <td align="right" style="padding:3px 0;"><span style="display:inline-block;background:#fdf4ff;color:#a21caf;font-weight:800;font-size:15px;padding:5px 14px;border-radius:99px;">{{ $upload->persentase_ai }}%</span></td>
                                            </tr>
                                            @endif
                                        </table>
                                        @endif

                                        {{-- Berkas apa saja yang bisa diunduh customer --}}
                                        @php
                                            $berkas = [];
                                            if ($upload->hasil_docx_path) $berkas[] = 'Dokumen hasil (DOCX)';
                                            if ($upload->hasil_path) $berkas[] = 'Laporan cek plagiasi';
                                            if ($upload->hasil_ai_path) $berkas[] = 'Laporan cek AI';
                                        @endphp
                                        @if (count($berkas))
                                        <hr style="border:none;border-top:1px dashed #e7d8c6;margin:14px 0;">
                                        <div style="font-size:12px;color:#9a8a79;text-transform:uppercase;letter-spacing:.06em;">Berkas Tersedia</div>
                                        @foreach ($berkas as $b)
                                        <div style="font-size:13px;color:#2a1c10;margin-top:5px;">&#10003; {{ $b }}</div>
                                        @endforeach
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- CTA --}}
                    <tr>
                        <td style="padding:6px 28px 8px;text-align:center;">
                            <a href="{{ $link }}" style="display:inline-block;background:linear-gradient(135deg,#fba919,#f0531e);color:#fff;text-decoration:none;font-weight:800;font-size:15px;padding:13px 34px;border-radius:12px;">⬇ Buka &amp; Unduh Hasil</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 22px;text-align:center;">
                            <p style="font-size:12px;color:#9a8a79;margin:10px 0 0;line-height:1.5;">
                                Atau salin link ini:<br>
                                <a href="{{ $link }}" style="color:#f0531e;word-break:break-all;">{{ $link }}</a>
                            </p>
                        </td>
                    </tr>

                    {{-- Catatan aman --}}
                    <tr>
                        <td style="padding:0 28px 24px;">
                            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:10px;padding:12px 14px;font-size:12px;line-height:1.5;color:#15803d;">
                                <b>Aman &amp; rahasia.</b> Link ini bersifat pribadi — simpan dan jangan bagikan ke orang lain. Dokumen Anda tidak disebarluaskan.
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#2a1c10;padding:20px 28px;text-align:center;">
                            <div style="font-size:12px;color:#d8c7b4;">Butuh bantuan? WhatsApp <a href="https://wa.me/6289505967995" style="color:#fba919;text-decoration:none;">0895-0596-7995</a></div>
                            <div style="font-size:11px;color:#8a7660;margin-top:6px;">© {{ date('Y') }} Phoenix Digital. Semua hak dilindungi.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
