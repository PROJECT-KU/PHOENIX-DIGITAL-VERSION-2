@extends('emails.asthana.bingkai')

@section('judul', $judul)
@section('pratinjau', $judul.' — '.($posisi ?? 'Lamaran umum'))

@section('isi')
    @php
        $warnaTahap = [
            'diseleksi' => ['#2455e6', '#eaf0ff'],
            'wawancara' => ['#0b8fb0', '#e1f6fb'],
            'diterima' => ['#12875c', '#e2f6ee'],
            'ditolak' => ['#6b7aa6', '#eef1f7'],
        ][$lamaran->status] ?? ['#2455e6', '#eaf0ff'];
    @endphp

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 12px;">
        <tr>
            <td style="padding:6px 14px; border-radius:999px; background:{{ $warnaTahap[1] }}; color:{{ $warnaTahap[0] }}; font-size:12px; font-weight:800; letter-spacing:1px; text-transform:uppercase;">
                {{ $lamaran->status_label }}
            </td>
        </tr>
    </table>
    <p style="margin:0 0 14px; font-size:20px; font-weight:800; color:#0f1b3d;">Halo {{ $lamaran->name }},</p>
    <p style="margin:0 0 18px; font-size:15px; line-height:1.75; color:#4a5578;">{{ $pembuka }}</p>

    @if ($pesan)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
            <tr>
                <td style="padding:16px 18px; border-radius:14px; background:#f3f6ff; border-left:4px solid #2455e6;">
                    <div style="font-size:11px; font-weight:800; letter-spacing:1.5px; color:#6b7aa6; margin-bottom:6px;">PESAN DARI TIM KAMI</div>
                    <div style="font-size:15px; line-height:1.7; color:#0f1b3d; white-space:pre-line;">{{ $pesan }}</div>
                </td>
            </tr>
        </table>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px; border:1px solid #e6eaf3; border-radius:14px;">
        <tr>
            <td style="padding:12px 16px; font-size:13px; color:#6b7aa6; border-bottom:1px solid #e6eaf3;">Posisi</td>
            <td align="right" style="padding:12px 16px; font-size:14px; font-weight:700; color:#0f1b3d; border-bottom:1px solid #e6eaf3;">{{ $posisi ?? 'Lamaran umum' }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px; font-size:13px; color:#6b7aa6;">Nomor lamaran</td>
            <td align="right" style="padding:12px 16px; font-family:'SFMono-Regular',Menlo,Consolas,monospace; font-size:14px; font-weight:800; color:#2455e6;">{{ $lamaran->nomor_lamaran }}</td>
        </tr>
    </table>

    @if ($urlStatus)
        @include('emails.asthana.tombol', ['url' => $urlStatus, 'label' => 'Cek status lamaran'])
    @endif
@endsection
