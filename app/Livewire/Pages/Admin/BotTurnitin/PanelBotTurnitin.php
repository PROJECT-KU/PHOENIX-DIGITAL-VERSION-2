<?php

namespace App\Livewire\Pages\Admin\BotTurnitin;

use App\Models\OrderUpload;
use App\Support\BotTurnitin;
use Livewire\Component;

/**
 * Panel Bot Turnitin di dashboard admin.
 *
 * Menampilkan kabar bot (aktif / tidak), kartu KUOTA HABIS, dan kartu
 * PERLU DIKERJAKAN MANUAL — dua keadaan di mana bot berhenti dan menunggu
 * tangan admin. Terpisah dari Dashboard supaya pollingnya tidak me-render
 * ulang seluruh dashboard.
 */
class PanelBotTurnitin extends Component
{
    /** Token polos — hanya ada sesaat setelah dibuat, tidak pernah disimpan. */
    public ?string $tokenBaru = null;

    /**
     * Tab daftar yang sedang dibuka: perlu | berjalan | selesai.
     *
     * Dipilih otomatis saat panel pertama dibuka, lalu DIPEGANG: wire:poll
     * merender panel ini tiap 30 detik, dan mengembalikannya ke tab bawaan
     * berarti tab yang sedang dibaca admin tertutup sendiri tiap setengah
     * menit.
     */
    public string $tab = 'perlu';

    public function mount(): void
    {
        // Yang menunggu tangan admin dibuka lebih dulu — itu satu-satunya
        // daftar di kartu ini yang menuntut tindakan. Kalau kosong, yang
        // ditampilkan pekerjaan yang sedang berjalan.
        if (! $this->bolehLihat() || ! BotTurnitin::skemaSiap()) {
            return;
        }

        $this->tab = BotTurnitin::perluAdmin()->isNotEmpty() ? 'perlu' : 'berjalan';
    }

    public function pilihTab(string $tab): void
    {
        $this->tab = in_array($tab, ['perlu', 'berjalan', 'selesai'], true) ? $tab : 'perlu';
    }

    private function bolehLihat(): bool
    {
        return (bool) auth()->user()?->hasPermission('view_pemesanantoko');
    }

    private function bolehAtur(): bool
    {
        return (bool) auth()->user()?->hasPermission('edit_pemesanantoko');
    }

    public function buatToken(): void
    {
        abort_unless($this->bolehAtur() && BotTurnitin::skemaSiap(), 403);

        $this->tokenBaru = BotTurnitin::buatToken();
    }

    public function tutupToken(): void
    {
        $this->tokenBaru = null;
    }

    public function alihkanJeda(): void
    {
        abort_unless($this->bolehLihat() && BotTurnitin::skemaSiap(), 403);

        BotTurnitin::setJeda(! BotTurnitin::dijeda());
        $this->dispatch('swal-success', message: BotTurnitin::dijeda() ? 'Bot dijeda.' : 'Bot dilanjutkan.');
    }

    public function kuotaSudahDiisi(): void
    {
        abort_unless($this->bolehLihat() && BotTurnitin::skemaSiap(), 403);

        BotTurnitin::kuotaSudahDiisi();
        $this->dispatch('swal-success', message: 'Bot akan mengambil antrean lagi.');
    }

    public function ambilAlih(string $uploadId): void
    {
        abort_unless($this->bolehLihat() && BotTurnitin::skemaSiap(), 403);

        if (! BotTurnitin::ambilAlih(OrderUpload::findOrFail($uploadId))) {
            $this->dispatch('swal-error', message: 'Laporan plagiasinya sudah ada — yang kurang tinggal dilengkapi dari halaman pesanan. Barisnya sengaja tetap di daftar ini sampai lengkap.');

            return;
        }

        $this->dispatch('swal-success', message: 'Ditandai dikerjakan manual. Bot tidak akan menyentuhnya lagi.');
    }

    public function cobaLagi(string $uploadId): void
    {
        abort_unless($this->bolehLihat() && BotTurnitin::skemaSiap(), 403);

        if (BotTurnitin::cobaLagi(OrderUpload::findOrFail($uploadId))) {
            $this->dispatch('swal-success', message: 'Dikembalikan ke antrean bot.');

            return;
        }

        $this->dispatch('swal-error', message: 'Unggahan ini sudah pernah terkirim ke submitin — kerjakan manual supaya kuota tidak terpakai dua kali.');
    }

    public function render()
    {
        if (! $this->bolehLihat()) {
            return view('livewire.pages.admin.bot-turnitin.panel-bot-turnitin', ['tampil' => false]);
        }

        if (! BotTurnitin::skemaSiap()) {
            return view('livewire.pages.admin.bot-turnitin.panel-bot-turnitin', [
                'tampil' => true,
                'skemaSiap' => false,
                'bolehAtur' => $this->bolehAtur(),
            ]);
        }

        // Dihitung SEKALI lalu dipakai bersama: perluAdmin() dan terbengkalai()
        // membaca baris yang sama, dan memanggil keduanya berarti dua kueri
        // untuk satu daftar yang identik.
        $perluAdmin = BotTurnitin::perluAdmin();

        return view('livewire.pages.admin.bot-turnitin.panel-bot-turnitin', [
            'tampil' => true,
            'skemaSiap' => true,
            'bolehAtur' => $this->bolehAtur(),
            'terbengkalai' => BotTurnitin::terbengkalai($perluAdmin),
            'dipasang' => BotTurnitin::sudahDipasang(),
            'aktif' => BotTurnitin::botAktif(),
            'detak' => BotTurnitin::detakTerakhir(),
            'masalah' => BotTurnitin::masalahBot(),
            'dijeda' => BotTurnitin::dijeda(),
            'kuotaHabis' => BotTurnitin::kuotaHabis(),
            'perluAdmin' => $perluAdmin,
            'berjalan' => BotTurnitin::sedangDikerjakan(),
            'selesaiTerbaru' => BotTurnitin::selesaiTerbaru(),
            'selesaiHariIni' => BotTurnitin::selesaiHariIni(),
            'antrean' => OrderUpload::where('jenis', 'plagiasi')->where('status', 'menunggu')->whereNull('bot_status')->count(),
            'urlSkrip' => asset('bot/phoenix-turnitin.user.js'),
        ]);
    }
}
