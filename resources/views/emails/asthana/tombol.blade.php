{{-- Tombol surel ($url, $label). Tabel supaya Outlook ikut membulatkan & mewarnai. --}}
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:6px auto 22px;">
    <tr>
        <td align="center" bgcolor="#2455e6" style="border-radius:999px; background:#2455e6;">
            <a href="{{ $url }}" style="display:inline-block; padding:13px 28px; font-size:14px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:999px;">{{ $label }} &rarr;</a>
        </td>
    </tr>
</table>
