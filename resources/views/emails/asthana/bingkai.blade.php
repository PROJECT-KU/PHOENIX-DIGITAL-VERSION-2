{{--
    Bingkai surel situs Asthana (tanda terima & kabar status lamaran).
    Hanya tabel + gaya sebaris: klien surel (Gmail, Outlook) membuang <style>.
    Logo disisipkan sebagai lampiran sebaris (CID) supaya tampil tanpa
    "tampilkan gambar" dan juga dari server lokal; $message tidak ada saat
    surel dirender untuk pratinjau, jadi jatuh ke alamat asset().
--}}
@php
    $logoSurel = isset($message)
        ? $message->embed(public_path('images/asthana/logo-surel.png'))
        : asset('images/asthana/logo-surel.png');
    $situsSurel = config('asthana.situs_url');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>@yield('judul') — PT. Asthana Cipta Mandiri</title>
</head>
<body style="margin:0; padding:0; background:#eef2f9; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; -webkit-font-smoothing:antialiased;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">@yield('pratinjau')</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f9; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:580px; background:#ffffff; border-radius:24px; overflow:hidden; box-shadow:0 18px 40px rgba(15,27,61,.10);">
                    {{-- Kepala --}}
                    <tr>
                        <td align="center" bgcolor="#2455e6"
                            style="background:#2455e6; background-image:linear-gradient(120deg,#2455e6 0%,#0ea5c6 55%,#17a974 100%); padding:34px 24px 30px;">
                            <img src="{{ $logoSurel }}" width="72" height="72" alt="Logo PT. Asthana Cipta Mandiri"
                                style="display:block; width:72px; height:72px; border:0; border-radius:18px; margin:0 auto 14px; box-shadow:0 10px 24px rgba(0,0,0,.18);">
                            <div style="font-size:11px; font-weight:700; letter-spacing:3px; color:#dbe8ff;">PT.</div>
                            <div style="font-size:23px; font-weight:800; color:#ffffff; line-height:1.25;">Asthana Cipta Mandiri</div>
                            <div style="font-size:13px; color:#e3f3ff; margin-top:4px;">Teknologi &amp; Pariwisata Indonesia</div>
                        </td>
                    </tr>
                    {{-- Isi --}}
                    <tr>
                        <td style="padding:32px 32px 8px;">
                            @yield('isi')
                        </td>
                    </tr>
                    {{-- Kaki --}}
                    <tr>
                        <td style="padding:8px 32px 30px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e6eaf3;">
                                <tr>
                                    <td style="padding-top:18px; font-size:12px; line-height:1.7; color:#8a93ad;">
                                        Surel ini dikirim otomatis — Anda tidak perlu membalasnya.<br>
                                        Grup usaha: <strong style="color:#4a5578;">Reka Technology</strong> · <strong style="color:#4a5578;">Orcha Journey</strong>
                                        @if ($situsSurel)
                                            <br><a href="{{ $situsSurel }}" style="color:#2455e6; text-decoration:none; font-weight:600;">{{ preg_replace('#^https?://#', '', $situsSurel) }}</a>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p style="font-size:12px; color:#9aa3bd; margin:18px 0 0;">&copy; {{ date('Y') }} PT. Asthana Cipta Mandiri</p>
            </td>
        </tr>
    </table>
</body>
</html>
