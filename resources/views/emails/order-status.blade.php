<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading }}</title>
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

                    {{-- Status badge --}}
                    <tr>
                        <td style="padding:30px 28px 8px;text-align:center;">
                            <div style="display:inline-block;width:64px;height:64px;line-height:64px;border-radius:50%;background:{{ $badgeBg }};color:{{ $badgeColor }};font-size:30px;">{{ $icon }}</div>
                            <h1 style="font-size:20px;margin:16px 0 6px;color:#2a1c10;">{{ $heading }}</h1>
                            <p style="font-size:14px;line-height:1.6;color:#7a6449;margin:0;">{{ $intro }}</p>
                        </td>
                    </tr>

                    {{-- Ringkasan pesanan --}}
                    <tr>
                        <td style="padding:20px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fbf5ee;border:1px solid #efe2d2;border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div style="font-size:12px;color:#9a8a79;text-transform:uppercase;letter-spacing:.06em;">Nomor Pesanan</div>
                                        <div style="font-size:16px;font-weight:800;color:#f0531e;margin-top:2px;">{{ $order->order_number }}</div>
                                        <hr style="border:none;border-top:1px dashed #e7d8c6;margin:14px 0;">
                                        @foreach ($order->items as $item)
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:8px;">
                                                <tr>
                                                    <td style="font-size:13px;color:#2a1c10;">{{ $item->product_name }}
                                                        <span style="color:#9a8a79;">· {{ $item->getDurationLabel() }} ×{{ $item->quantity }}</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endforeach
                                        <hr style="border:none;border-top:1px dashed #e7d8c6;margin:12px 0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size:14px;font-weight:800;color:#2a1c10;">Total</td>
                                                <td align="right" style="font-size:16px;font-weight:800;color:#f0531e;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- CTA --}}
                    @if (!empty($ctaUrl))
                        <tr>
                            <td style="padding:6px 28px 26px;text-align:center;">
                                <a href="{{ $ctaUrl }}" style="display:inline-block;background:linear-gradient(135deg,#fba919,#f0531e);color:#fff;text-decoration:none;font-weight:800;font-size:15px;padding:13px 30px;border-radius:12px;">{{ $ctaText }}</a>
                            </td>
                        </tr>
                    @endif

                    {{-- Peringatan penipuan --}}
                    <tr>
                        <td style="padding:0 28px 24px;">
                            <div style="background:#fff5f0;border:1px solid #f6c6ad;border-left:4px solid #f0531e;border-radius:10px;padding:12px 14px;font-size:12px;line-height:1.5;color:#7a3d1a;">
                                <b>Hati-hati penipuan!</b> Pastikan pembayaran hanya atas nama <b>Phoenix Digital Warehouse</b>. Selain itu dipastikan penipuan.
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
