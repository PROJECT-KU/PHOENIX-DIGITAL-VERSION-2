<?php

namespace App\Livewire\Pages\Admin\Message;

use App\Models\CustomerMessage;
use App\Models\CustomerMessageTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class CustomerMessageDetail extends Component
{
    public CustomerMessage $message;

    public $status;

    public $priority;

    public $kategori;

    /** Id petugas pemegang tiket ('' = belum ditugaskan). */
    public $petugas;

    // ===== Kotak balasan =====
    public string $balasanIsi = '';

    public string $balasanKanal = 'whatsapp';

    public bool $balasanSelesai = true;

    // ===== Catatan internal =====
    public string $catatanIsi = '';

    // ===== Kelola template =====
    public bool $kelolaTemplate = false;

    public string $templateNama = '';

    public string $templateIsi = '';

    public function mount(CustomerMessage $message)
    {
        $this->message = $message;
        $this->status = $message->status;
        $this->priority = $message->priority;
        $this->kategori = $message->kategori ?? '';
        $this->petugas = (string) ($message->assigned_to ?? '');

        // markAsRead mengubah pesan dari "belum dibaca" -> "dibaca". Bila memang
        // baru saja beralih, beritahu sidebar agar badge helpdesk langsung
        // berkurang tanpa perlu refresh.
        $belumDibaca = is_null($this->message->read_at);
        $this->message->markAsRead();

        if ($belumDibaca) {
            $this->message->catat('dibaca');
            $this->dispatch('sidebar-badge-updated');
        }
    }

    /**
     * Mengubah tiket butuh izin sendiri.
     *
     * Sebelumnya izin "lihat" sudah cukup untuk mengubah status tiket orang
     * lain — padahal membaca dan menangani itu dua pekerjaan berbeda.
     */
    protected function bolehUbah(): bool
    {
        if (auth()->user()?->hasPermission('edit_customer_message')) {
            return true;
        }

        $this->dispatch('toast-error', message: 'Anda tidak memiliki izin menangani pesan pelanggan.');

        return false;
    }

    protected function kembalikanNilai(): void
    {
        $this->message->refresh();
        $this->status = $this->message->status;
        $this->priority = $this->message->priority;
        $this->kategori = $this->message->kategori ?? '';
        $this->petugas = (string) ($this->message->assigned_to ?? '');
    }

    // ===== Tindak lanjut =====

    public function updatedStatus($value): void
    {
        // Nilai dari klien tidak pernah dipercaya: kolomnya ENUM di MySQL,
        // satu nilai asing = galat 500 di server.
        if (! $this->bolehUbah() || ! array_key_exists($value, CustomerMessageList::STATUS)) {
            $this->kembalikanNilai();

            return;
        }

        $lama = CustomerMessageList::STATUS[$this->message->status] ?? $this->message->status;
        $this->message->update(['status' => $value]);
        $this->message->catat('status', $lama.' → '.CustomerMessageList::STATUS[$value]);
        $this->dispatch('toast-success', message: 'Status berhasil diperbarui!');
        $this->dispatch('sidebar-badge-updated');
    }

    public function updatedPriority($value): void
    {
        if (! $this->bolehUbah() || ! array_key_exists($value, CustomerMessageList::PRIORITAS)) {
            $this->kembalikanNilai();

            return;
        }

        $lama = CustomerMessageList::PRIORITAS[$this->message->priority] ?? $this->message->priority;
        $this->message->update(['priority' => $value]);
        $this->message->catat('prioritas', $lama.' → '.CustomerMessageList::PRIORITAS[$value]);
        $this->dispatch('toast-success', message: 'Prioritas berhasil diperbarui!');
    }

    public function updatedKategori($value): void
    {
        $daftar = config('helpdesk.kategori');

        if (! $this->bolehUbah() || ($value !== '' && ! array_key_exists($value, $daftar))) {
            $this->kembalikanNilai();

            return;
        }

        $this->message->update(['kategori' => $value ?: null]);
        $this->message->catat('kategori', $value ? $daftar[$value] : 'Tanpa topik');
        $this->dispatch('toast-success', message: 'Topik tiket diperbarui!');
    }

    public function updatedPetugas($value): void
    {
        $sah = $value === '' || CustomerMessage::petugasTersedia()->contains('id', (int) $value);

        if (! $this->bolehUbah() || ! $sah) {
            $this->kembalikanNilai();

            return;
        }

        $this->message->update(['assigned_to' => $value ?: null]);
        $this->message->catat('tugas', $value
            ? 'Dipegang '.($this->message->fresh()->petugas?->name ?? 'petugas')
            : 'Penugasan dilepas');
        $this->dispatch('toast-success', message: 'Penugasan diperbarui!');
    }

    // ===== Balasan & catatan =====

    public function pakaiTemplate($id): void
    {
        $template = CustomerMessageTemplate::find($id);

        if ($template) {
            $this->balasanIsi = $template->untuk($this->message);
        }
    }

    public function simpanBalasan(): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $this->validate([
            'balasanIsi' => ['required', 'string', 'min:3', 'max:5000'],
            'balasanKanal' => ['required', 'string', 'in:'.implode(',', array_keys(config('helpdesk.kanal')))],
        ], [], ['balasanIsi' => 'isi balasan', 'balasanKanal' => 'kanal']);

        $this->message->catat('balasan', $this->balasanIsi, $this->balasanKanal);

        // replied_at menandai balasan PERTAMA — itu yang dipakai menghitung
        // waktu tanggap; balasan berikutnya tetap tercatat di linimasa.
        $isian = $this->message->sudahDibalas() ? [] : ['replied_at' => now(), 'replied_by' => auth()->id()];

        if ($this->balasanSelesai) {
            $isian['status'] = 'resolved';
        }

        if ($isian) {
            $this->message->update($isian);
        }

        $this->balasanIsi = '';
        $this->kembalikanNilai();
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('toast-success', message: 'Balasan tercatat di linimasa tiket.');
    }

    public function simpanCatatan(): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $this->validate([
            'catatanIsi' => ['required', 'string', 'min:3', 'max:2000'],
        ], [], ['catatanIsi' => 'catatan']);

        $this->message->catat('catatan', $this->catatanIsi);
        $this->catatanIsi = '';
        $this->dispatch('toast-success', message: 'Catatan internal tersimpan.');
    }

    // ===== Template =====

    public function simpanTemplate(): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $this->validate([
            'templateNama' => ['required', 'string', 'max:60'],
            'templateIsi' => ['required', 'string', 'max:2000'],
        ], [], ['templateNama' => 'nama template', 'templateIsi' => 'isi template']);

        CustomerMessageTemplate::create([
            'nama' => $this->templateNama,
            'isi' => $this->templateIsi,
            'urutan' => (int) CustomerMessageTemplate::max('urutan') + 1,
        ]);

        $this->reset(['templateNama', 'templateIsi']);
        $this->dispatch('toast-success', message: 'Template balasan ditambahkan.');
    }

    public function hapusTemplate($id): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        CustomerMessageTemplate::whereKey($id)->delete();
        $this->dispatch('toast-success', message: 'Template dihapus.');
    }

    // ===== Arsip & spam =====

    public function arsipkan()
    {
        if (! auth()->user()?->hasPermission('delete_customer_message')) {
            $this->dispatch('toast-error', message: 'Anda tidak memiliki izin mengarsipkan pesan pelanggan.');

            return null;
        }

        $this->message->catat('arsip');
        $this->message->delete();
        $this->dispatch('sidebar-badge-updated');
        session()->flash('success', 'Tiket '.$this->message->ticket.' dipindahkan ke arsip.');

        return $this->redirectRoute('admin.customer-message.index', navigate: true);
    }

    public function tandaiSpam()
    {
        if (! auth()->user()?->hasPermission('delete_customer_message')) {
            $this->dispatch('toast-error', message: 'Anda tidak memiliki izin menandai spam.');

            return null;
        }

        $this->message->catat('spam');
        $this->message->update(['is_spam' => true, 'status' => 'closed']);
        $this->message->delete();
        $this->dispatch('sidebar-badge-updated');
        session()->flash('success', 'Tiket '.$this->message->ticket.' ditandai spam dan diarsipkan.');

        return $this->redirectRoute('admin.customer-message.index', navigate: true);
    }

    // ===== Unduhan satu tiket =====

    public function unduhPdf()
    {
        abort_unless(auth()->user()?->hasPermission('view_customer_message'), 403);

        $pdf = Pdf::loadView('exports.pesan-pelanggan-tiket-pdf', [
            'pesan' => $this->message->load(['petugas', 'logs']),
        ])->setPaper('a4');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'tiket-'.$this->message->ticket.'.pdf'
        );
    }

    public function render()
    {
        // Tetangga tiket mengikuti urutan daftar (terbaru dulu): "berikutnya"
        // berarti yang lebih lama. Tanpa ini, menangani 20 tiket berarti 40
        // kali bolak-balik lewat tombol Kembali.
        $berikut = CustomerMessage::bukanSpam()->where('created_at', '<', $this->message->created_at)
            ->latest()->first(['id', 'ticket']);
        $sebelum = CustomerMessage::bukanSpam()->where('created_at', '>', $this->message->created_at)
            ->oldest()->first(['id', 'ticket']);

        $pelanggan = $this->message->pelangganTerdaftar();

        return view('livewire.pages.admin.message.customer-message-detail', [
            'logs' => $this->message->logs()->get(),
            'templates' => CustomerMessageTemplate::urut()->get(),
            'daftarPetugas' => CustomerMessage::petugasTersedia(),
            'pelanggan' => $pelanggan,
            'pesanan' => $pelanggan?->orders()->latest()->limit(3)->get() ?? collect(),
            'pesanLain' => $this->message->pesanLain(),
            'sebelum' => $sebelum,
            'berikut' => $berikut,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
