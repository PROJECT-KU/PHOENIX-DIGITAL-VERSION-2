<?php

namespace App\Livewire\Pages\Admin\Message;

use App\Mail\BalasanTiketMail;
use App\Models\CustomerMessage;
use App\Models\CustomerMessageTemplate;
use App\Notifications\TiketDitugaskan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;

class CustomerMessageDetail extends Component
{
    use WithFileUploads;

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

    /** Kirim balasannya sekalian sebagai surel dari aplikasi. */
    public bool $balasanKirimSurel = false;

    // ===== Catatan internal =====
    public string $catatanIsi = '';

    // ===== Kelola template =====
    public bool $kelolaTemplate = false;

    public string $templateNama = '';

    public string $templateIsi = '';

    /** Id template yang sedang diedit ('' = sedang menambah yang baru). */
    public string $templateId = '';

    // ===== Lampiran =====
    public $berkasBaru;

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
        // Kirim surel dicentang otomatis bila memang ada alamatnya.
        $this->balasanKirimSurel = filled($message->email);

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
        $petugas = $this->message->fresh()->petugas;
        $this->message->catat('tugas', $petugas ? 'Dipegang '.$petugas->name : 'Penugasan dilepas');
        TiketDitugaskan::kirim($this->message, $petugas);
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

        // Surel dikirim DULU: kalau gagal, tiket tidak boleh terlanjur berstatus
        // "sudah dibalas" padahal pelanggan tidak menerima apa pun.
        $lewatSurel = $this->balasanKirimSurel && $this->balasanKanal === 'email';

        if ($lewatSurel) {
            if (blank($this->message->email)) {
                $this->addError('balasanIsi', 'Tiket ini tidak punya alamat surel, jadi balasannya tidak bisa dikirim lewat surel.');

                return;
            }

            try {
                Mail::to($this->message->email)->send(new BalasanTiketMail(
                    $this->message,
                    $this->balasanIsi,
                    $this->message->lampiran()->where('sumber', 'admin')->get()->all(),
                ));
            } catch (\Throwable $e) {
                Log::error('Gagal kirim balasan tiket '.$this->message->ticket.': '.$e->getMessage());
                $this->addError('balasanIsi', 'Surel gagal dikirim: '.$e->getMessage().' Balasannya belum dicatat.');

                return;
            }
        }

        $this->message->catat('balasan', $this->balasanIsi, $this->balasanKanal);

        if ($lewatSurel) {
            $this->message->catat('surel', 'Dikirim ke '.$this->message->email.' dari aplikasi.');
        }

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
        $this->dispatch('toast-success', message: $lewatSurel
            ? 'Balasan terkirim lewat surel dan tercatat di linimasa.'
            : 'Balasan tercatat di linimasa tiket.');
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

    /** Isikan template ke kotak CATATAN internal. */
    public function pakaiTemplateCatatan($id): void
    {
        $template = CustomerMessageTemplate::find($id);

        if ($template) {
            $this->catatanIsi = $template->untuk($this->message);
        }
    }

    public function editTemplate($id): void
    {
        $template = CustomerMessageTemplate::find($id);

        if (! $template) {
            return;
        }

        $this->templateId = (string) $template->id;
        $this->templateNama = $template->nama;
        $this->templateIsi = $template->isi;
        $this->kelolaTemplate = true;
    }

    public function batalEditTemplate(): void
    {
        $this->reset(['templateId', 'templateNama', 'templateIsi']);
        $this->resetErrorBag(['templateNama', 'templateIsi']);
    }

    public function simpanTemplate(): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $this->validate([
            'templateNama' => ['required', 'string', 'max:60'],
            'templateIsi' => ['required', 'string', 'max:2000'],
        ], [], ['templateNama' => 'nama template', 'templateIsi' => 'isi template']);

        $lama = $this->templateId ? CustomerMessageTemplate::find($this->templateId) : null;

        if ($lama) {
            $lama->update(['nama' => $this->templateNama, 'isi' => $this->templateIsi]);
        } else {
            CustomerMessageTemplate::create([
                'nama' => $this->templateNama,
                'isi' => $this->templateIsi,
                'urutan' => (int) CustomerMessageTemplate::max('urutan') + 1,
            ]);
        }

        $this->reset(['templateId', 'templateNama', 'templateIsi']);
        $this->dispatch('toast-success', message: $lama ? 'Template diperbarui.' : 'Template balasan ditambahkan.');
    }

    /** Geser urutan template: 'naik' menukar dengan tetangga di atasnya. */
    public function geserTemplate($id, string $arah): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $ini = CustomerMessageTemplate::find($id);

        if (! $ini) {
            return;
        }

        $daftar = CustomerMessageTemplate::urut()->get();
        $posisi = $daftar->search(fn ($t) => $t->is($ini));
        $tujuan = $arah === 'naik' ? $posisi - 1 : $posisi + 1;

        if ($posisi === false || ! isset($daftar[$tujuan])) {
            return;
        }

        // Urutan ditulis ulang dari nol: nilai lama bisa kembar (mis. semuanya 0
        // dari isian migrasi) sehingga tukar-menukar saja tidak selalu bergerak.
        $urutanBaru = $daftar->values()->all();
        [$urutanBaru[$posisi], $urutanBaru[$tujuan]] = [$urutanBaru[$tujuan], $urutanBaru[$posisi]];

        foreach ($urutanBaru as $i => $t) {
            $t->update(['urutan' => $i]);
        }
    }

    public function hapusTemplate($id): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        CustomerMessageTemplate::whereKey($id)->delete();

        if ((string) $id === $this->templateId) {
            $this->batalEditTemplate();
        }

        $this->dispatch('toast-success', message: 'Template dihapus.');
    }

    // ===== Lampiran =====

    public function unggahLampiran(): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $this->validate([
            // 8 MB: cukup untuk tangkapan layar/PDF, tidak cukup untuk video.
            'berkasBaru' => ['required', 'file', 'max:8192', 'mimes:jpg,jpeg,png,webp,pdf,docx,xlsx'],
        ], [], ['berkasBaru' => 'berkas']);

        // Disk PRIVAT: lampiran tiket sering berisi tangkapan layar mutasi bank.
        $path = $this->berkasBaru->store('helpdesk/'.$this->message->getKey(), 'local');

        $this->message->lampiran()->create([
            'sumber' => 'admin',
            'nama_asli' => $this->berkasBaru->getClientOriginalName(),
            'path' => $path,
            'mime' => $this->berkasBaru->getMimeType(),
            'ukuran' => $this->berkasBaru->getSize(),
            'user_id' => auth()->id(),
        ]);

        $this->message->catat('lampiran', $this->berkasBaru->getClientOriginalName());
        $this->reset('berkasBaru');
        $this->dispatch('toast-success', message: 'Lampiran ditambahkan ke tiket.');
    }

    public function hapusLampiran($id): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $lampiran = $this->message->lampiran()->whereKey($id)->first();

        // Lampiran PELANGGAN tidak boleh dihapus admin: itu bukti yang ia kirim.
        if (! $lampiran || ! $lampiran->dariAdmin()) {
            $this->dispatch('toast-error', message: 'Lampiran dari pelanggan tidak bisa dihapus.');

            return;
        }

        $lampiran->hapusBerkas();
        $lampiran->delete();
        $this->dispatch('toast-success', message: 'Lampiran dihapus.');
    }

    // ===== Gabung tiket ganda =====

    public function gabungkanTiket($id): void
    {
        if (! $this->bolehUbah()) {
            return;
        }

        $lain = CustomerMessage::find($id);

        if (! $lain || ! $this->message->gabungkan($lain)) {
            $this->dispatch('toast-error', message: 'Tiket itu tidak bisa digabungkan.');

            return;
        }

        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('toast-success', message: 'Tiket '.$lain->ticket.' digabungkan ke tiket ini.');
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
            'pesan' => $this->message->load(['petugas', 'logs', 'lampiran']),
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
            'lampiran' => $this->message->lampiran()->get(),
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
