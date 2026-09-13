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

        BotTurnitin::ambilAlih(OrderUpload::findOrFail($uploadId));
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

        return view('livewire.pages.admin.bot-turnitin.panel-bot-turnitin', [
            'tampil' => true,
            'skemaSiap' => true,
            'bolehAtur' => $this->bolehAtur(),
            'dipasang' => BotTurnitin::sudahDipasang(),
            'aktif' => BotTurnitin::botAktif(),
            'detak' => BotTurnitin::detakTerakhir(),
            'masalah' => BotTurnitin::masalahBot(),
            'dijeda' => BotTurnitin::dijeda(),
            'kuotaHabis' => BotTurnitin::kuotaHabis(),
            'perluAdmin' => BotTurnitin::perluAdmin(),
            'berjalan' => BotTurnitin::sedangDikerjakan(),
            'selesaiTerbaru' => BotTurnitin::selesaiTerbaru(),
            'selesaiHariIni' => BotTurnitin::selesaiHariIni(),
            'antrean' => OrderUpload::where('jenis', 'plagiasi')->where('status', 'menunggu')->whereNull('bot_status')->count(),
            'urlSkrip' => asset('bot/phoenix-turnitin.user.js'),
        ]);
    }
}
