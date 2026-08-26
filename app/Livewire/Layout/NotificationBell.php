<?php

namespace App\Livewire\Layout;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    /**
     * Segarkan lonceng saat komponen lain menandai notifikasi dibaca
     * (mis. membuka task di Task Saya / Penyelesaian Task) — tanpa refresh halaman.
     */
    #[On('notifs-read')]
    public function refreshBell(): void
    {
        // Body kosong; menerima event sudah memicu Livewire me-render ulang
        // sehingga jumlah unread dihitung ulang di render().
    }

    public function markAsRead($id)
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $notif = $user->notifications()->where('id', $id)->first();
        if ($notif) {
            $notif->markAsRead();
            $url = $notif->data['url'] ?? null;
            $taskId = $notif->data['task_id'] ?? null;
            if ($url) {
                if ($taskId) {
                    $url .= (str_contains($url, '?') ? '&' : '?').'open_task='.$taskId;
                }

                return redirect($url);
            }
        }

        return null;
    }

    public function markAllRead(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }
        // Hanya notifikasi bulan berjalan yang ditandai (yang lama tak lagi tampil).
        $this->bulanIni($user->unreadNotifications())->update(['read_at' => now()]);
    }

    /**
     * Batasi notifikasi ke bulan & tahun kalender SAAT INI (bukan filter periode task).
     * Efeknya: notifikasi bulan lalu otomatis "reset"/hilang begitu masuk bulan baru.
     */
    protected function bulanIni($query)
    {
        return $query->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
    }

    public function render()
    {
        $user = auth()->user();

        /*
         | Pekerjaan Orcha ikut masuk lonceng, tapi TIDAK dicampur dengan
         | notifikasi lemon.
         |
         | Keduanya berbeda sifatnya. Notifikasi lemon adalah kejadian: sudah
         | terjadi, bisa ditandai dibaca, lalu selesai. Bukti yang menunggu
         | dicek adalah PEKERJAAN: ia tidak hilang karena dibaca, hanya karena
         | dikerjakan. Menandainya "dibaca" akan menyembunyikan pekerjaan yang
         | belum dikerjakan — cara paling rapi untuk melupakan uang pelanggan.
         |
         | Maka ia berdiri di bagiannya sendiri, berwarna Orcha, dan tidak ikut
         | tombol "Tandai semua dibaca".
         */
        $punyaOrcha = $user && $user->hasPermission('akses_orcha');

        $bayar = $punyaOrcha
            ? \App\Support\OrchaMenungguDicek::ambil()
            : ['jumlah' => 0, 'nominal' => 0];

        $sewa = $punyaOrcha
            ? \App\Support\OrchaSewaPerhatian::ambil()
            : ['baru' => 0, 'telat' => 0, 'denda' => 0];

        $batal = $punyaOrcha
            ? \App\Support\OrchaPembatalanPerhatian::ambil()
            : ['diajukan' => 0, 'diproses' => 0, 'disetujui' => 0];

        $pesan = $punyaOrcha
            ? \App\Support\OrchaPesanPerhatian::ambil()
            : ['belum_dibaca' => 0, 'baru' => 0, 'lama' => 0];

        $trip = $punyaOrcha
            ? \App\Support\OrchaPendaftaranPerhatian::ambil()
            : ['baru' => 0, 'dihubungi' => 0, 'telat_lunas' => 0];

        /*
         | Tiap urusan jadi barisnya sendiri, tidak digabung jadi satu angka.
         |
         | "3 hal perlu ditindak" tidak memberi tahu apa pun tentang apa yang
         | harus dikerjakan, dan admin tetap harus membuka semuanya untuk tahu.
         | Kalimat yang dipakai untuk bukti transfer, pemesanan baru, dan unit
         | telat memang berbeda-beda — jadi barisnya juga.
         */
        $orcha = collect([
            $trip['baru'] > 0 ? [
                'ikon' => 'bi-clipboard-plus',
                'judul' => $trip['baru'].' pendaftaran open trip baru',
                'isi' => 'Belum disentuh siapa pun. Pemesannya sedang menunggu dijawab.',
                'tautan' => route('admin.orcha.pendaftaran').'?filterStatus=baru',
                'aksi' => 'Hubungi pemesannya',
            ] : null,
            $trip['dihubungi'] > 0 ? [
                'ikon' => 'bi-telephone-outbound',
                'judul' => $trip['dihubungi'].' sudah dihubungi, belum bayar',
                'isi' => 'Sudah dijawab, tetapi belum satu rupiah pun masuk.',
                'tautan' => route('admin.orcha.pendaftaran').'?filterStatus=dihubungi',
                'aksi' => 'Tagih uang mukanya',
            ] : null,
            // Paling mahal dibiarkan: kursinya tertahan atas nama orang yang
            // belum tentu berangkat, dan makin dekat hari-H makin sulit dijual
            // ulang.
            $trip['telat_lunas'] > 0 ? [
                'ikon' => 'bi-hourglass-bottom',
                'judul' => $trip['telat_lunas'].' pendaftaran lewat tenggat pelunasan',
                'isi' => 'Sudah DP tetapi belum lunas, sementara batas pelunasannya sudah terlewat.',
                'tautan' => route('admin.orcha.pendaftaran').'?filterStatus=dp_masuk',
                'aksi' => 'Tagih pelunasannya',
            ] : null,
            $bayar['jumlah'] > 0 ? [
                'ikon' => 'bi-cash-coin',
                'judul' => $bayar['jumlah'].' bukti transfer menunggu dicek',
                'isi' => 'Senilai Rp '.number_format($bayar['nominal'], 0, ',', '.')
                    .'. Pelanggan menunggu pembayarannya diakui.',
                'tautan' => route('admin.orcha.pembayaran'),
                'aksi' => 'Buka Bukti Pembayaran',
            ] : null,
            $sewa['baru'] > 0 ? [
                'ikon' => 'bi-truck',
                'judul' => $sewa['baru'].' pemesanan sewa baru',
                'isi' => 'Belum disentuh siapa pun. Pemesannya sedang menunggu dijawab.',
                'tautan' => route('admin.orcha.penyewaan'),
                'aksi' => 'Buka Sewa Kendaraan',
            ] : null,
            // Ditaruh paling bawah tapi paling mahal dibiarkan: dendanya terus
            // berjalan tanpa pernah ditetapkan, dan unitnya tidak bisa
            // disewakan lagi karena di sistem masih dianggap ada di luar.
            $sewa['telat'] > 0 ? [
                'ikon' => 'bi-alarm',
                'judul' => $sewa['telat'].' unit belum kembali, sudah lewat tenggat',
                'isi' => 'Unitnya masih di luar dan di sistem belum tercatat kembali.',
                'tautan' => route('admin.orcha.penyewaan'),
                'aksi' => 'Catat serah terimanya',
            ] : null,
            // Sisa dari perkara di atas: unitnya sudah aman, uangnya belum.
            $sewa['denda'] > 0 ? [
                'ikon' => 'bi-cash-stack',
                'judul' => $sewa['denda'].' denda belum ditetapkan',
                'isi' => 'Unit sudah kembali dan sistem punya usulan dendanya, '
                    .'tetapi nota yang dikirim ke penyewa masih menyebut Rp 0.',
                'tautan' => route('admin.orcha.penyewaan'),
                'aksi' => 'Tetapkan dendanya',
            ] : null,
            $batal['diajukan'] > 0 ? [
                'ikon' => 'bi-x-circle',
                'judul' => $batal['diajukan'].' pembatalan baru diajukan',
                'isi' => 'Belum disentuh siapa pun. Pemohonnya sedang menunggu dijawab.',
                'tautan' => route('admin.orcha.pembatalan').'?filterStatus=diajukan',
                'aksi' => 'Periksa pengajuannya',
            ] : null,
            $batal['diproses'] > 0 ? [
                'ikon' => 'bi-arrow-repeat',
                'judul' => $batal['diproses'].' pembatalan sedang diproses',
                'isi' => 'Sudah dipegang, tetapi keputusannya belum selesai.',
                'tautan' => route('admin.orcha.pembatalan').'?filterStatus=diproses',
                'aksi' => 'Selesaikan keputusannya',
            ] : null,
            // Keputusannya sudah diambil dan uangnya sudah dinyatakan kembali,
            // tetapi belum berangkat ke mana-mana. Yang menunggu bukan lagi
            // jawaban, melainkan uangnya sendiri.
            $batal['disetujui'] > 0 ? [
                'ikon' => 'bi-send-exclamation',
                'judul' => $batal['disetujui'].' pengembalian dana belum dikirim',
                'isi' => 'Sudah disetujui, tetapi dananya belum ditandai terkirim.',
                'tautan' => route('admin.orcha.pembatalan').'?filterStatus=disetujui',
                'aksi' => 'Kirim dananya',
            ] : null,
            /*
             | Pesan kontak dihitung dari yang BELUM DIBACA, bukan yang belum
             | dibalas. Balasannya dikirim lewat WhatsApp, di luar sistem, jadi
             | Orcha tidak pernah tahu sebuah pesan sudah dijawab atau belum —
             | satu-satunya penutup yang tercatat adalah "sudah dibaca".
             |
             | Kalimatnya menyebut itu apa adanya. Menulis "belum ditindak
             | lanjuti" akan menjanjikan sesuatu yang tidak bisa ditepati
             | angkanya: pesan yang sudah dibalas panjang lebar lewat WhatsApp
             | tetap terhitung selama admin lupa menandainya dibaca.
             */
            $pesan['baru'] > 0 ? [
                'ikon' => 'bi-envelope-exclamation',
                'judul' => $pesan['baru'].' pesan kontak baru masuk',
                'isi' => 'Belum dibuka siapa pun. Yang bertanya sedang menunggu dibalas.',
                'tautan' => route('admin.orcha.pesan').'?belumDibaca=1',
                'aksi' => 'Buka kotak masuk',
            ] : null,
            // Yang menyakiti. Orang yang bertanya sudah menunggu semalaman
            // tanpa satu pun tanda pesannya sampai, dan yang datang berikutnya
            // biasanya bukan pertanyaan lagi.
            $pesan['lama'] > 0 ? [
                'ikon' => 'bi-hourglass-bottom',
                'judul' => $pesan['lama'].' pesan belum dibaca lewat sehari',
                'isi' => 'Masuk lebih dari 24 jam lalu dan belum dibuka sama sekali.',
                'tautan' => route('admin.orcha.pesan').'?belumDibaca=1',
                'aksi' => 'Baca sekarang',
            ] : null,
        ])->filter()->values();

        return view('livewire.layout.notification-bell', [
            'items' => $user ? $this->bulanIni($user->notifications())->latest()->take(15)->get() : collect(),
            'unread' => $user ? $this->bulanIni($user->unreadNotifications())->count() : 0,
            'orcha' => $orcha,
            // Yang belum dibaca dijumlahkan sekali lewat 'belum_dibaca', bukan
            // baru + lama: keduanya pecahan dari angka yang sama, dan
            // menjumlahkannya di sini membuat lencananya bisa meleset diam-diam
            // bila suatu saat pemecahannya berubah.
            'orchaJumlah' => $bayar['jumlah'] + $sewa['baru'] + $sewa['telat'] + $sewa['denda']
                + $batal['diajukan'] + $batal['diproses'] + $batal['disetujui']
                + $pesan['belum_dibaca']
                + $trip['baru'] + $trip['dihubungi'] + $trip['telat_lunas'],
        ]);
    }
}
