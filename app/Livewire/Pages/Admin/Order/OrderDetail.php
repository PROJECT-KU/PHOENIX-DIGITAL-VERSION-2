<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Actions\Finance\SyncCashFlowAction;
use App\Mail\JasaHasilMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderUpload;
use App\Support\PlagiarismReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderDetail extends Component
{
    use WithFileUploads;

    public ?Order $order = null;

    public ?OrderItem $orderItem = null;

    // ==== Pengecekan jasa (upload hasil oleh admin) ====
    public ?string $uploadAktifId = null; // pengecekan yang sedang diisi hasilnya

    public $hasilFile;

    public $persentaseInput = null;

    public bool $persenTerbacaOtomatis = false;

    /** Format laporan plagiasi tak dikenali — admin isi persen manual. */
    public bool $persenGagalBaca = false;

    /** Hasil cek AI (PDF) + persentasenya. */
    public $hasilAiFile;

    public $persentaseAiInput = null;

    public bool $persenAiTerbacaOtomatis = false;

    /** Format laporan AI tak dikenali — admin perlu mengisi persen manual. */
    public bool $persenAiGagalBaca = false;

    /** Asal laporan AI yang terbaca: 'turnitin' | 'gptzero' | null. */
    public ?string $sumberAi = null;

    /**
     * Arti angkanya berbeda per sumber — Turnitin = persen teks AI, GPTZero =
     * probabilitas dokumen AI. Ditampilkan apa adanya agar tak menyesatkan.
     */
    public ?string $labelAi = null;

    /** Persen yang ditemukan saat berkas memuat >1 laporan — admin memilih. */
    public array $pilihanAi = [];

    /** Dokumen DOCX hasil parafrase yang dikerjakan tim. */
    public $hasilDocxFile;

    public function mount(Order $order)
    {
        // Auto-update: tandai item yang end_date-nya sudah lewat menjadi 'habis'
        // dan segarkan sisa hari (sisi otomatis dari penentuan "habis").
        $order->load('items');
        foreach ($order->items as $item) {
            if ($item->end_date) {
                $item->updateRemainingDays();
            }
        }

        $this->order = $order->fresh()->load([
            'customer',
            'items.product', 'items.ebooks', 'items.processedBy', 'uploads',
        ]);
    }

    public function updateSubscriptionStatus(string $itemId, string $status): void
    {
        $allowed = ['baru', 'perpanjang', 'pengganti', 'habis'];

        if (! in_array($status, $allowed, true)) {
            return;
        }

        $item = OrderItem::where('order_id', $this->order->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->update(['subscription_status' => $status]);

        $this->order = $this->order->fresh()->load(['customer', 'items.product', 'items.ebooks', 'items.processedBy', 'uploads']);

        $this->dispatch('subscription-status-updated');
    }

    #[On('sent-on-whatsapp')]
    public function updateStatus($id, SyncCashFlowAction $syncCashFlow)
    {
        DB::transaction(function () use ($id, $syncCashFlow) {
            $this->orderItem = OrderItem::where('order_id', $this->order->id)
                ->where('id', $id)
                ->firstOrFail();

            $this->orderItem->update([
                'delivery_status' => 'delivered',
            ]);

            $masihAdaBelumDelivered = $this->order
                ->items()
                ->where('delivery_status', '!=', 'delivered')
                ->exists();

            if (! $masihAdaBelumDelivered) {
                // Saat pesanan selesai (semua item terkirim), catat WAKTU penyelesaian
                // sebagai tanggal bayar bila belum ada — agar tanggal bayar punya jam/waktu.
                $this->order->update([
                    'status' => 'completed',
                    'paid_at' => $this->order->paid_at ?: now(),
                ]);
            }

            $syncCashFlow->execute($this->order, [
                'amount' => $this->order->total,
                'type' => 'income',
                // Tanggal uang masuk = tanggal bayar (saat completed). Fallback ke tanggal dibuat.
                'date' => $this->order->paid_at ?: $this->order->created_at,
                'category' => 'e-commerce',
                'description' => $this->order->deskripsi ?? 'Pembelian akun dari e-commerce',
            ]);
        });

        $this->order->refresh();
        $this->dispatch('close-wa-modal');
    }

    // Tandai bahwa notifikasi "akun habis" sudah dikirim ke pelanggan via WhatsApp.
    // Tidak mengubah status order/delivery, hanya mencatat waktu pemberitahuan.
    #[On('habis-notified')]
    public function markHabisNotified($id)
    {
        $item = OrderItem::where('order_id', $this->order->id)
            ->where('id', $id)
            ->firstOrFail();

        $item->update(['habis_notified_at' => now()]);

        $this->order = $this->order->fresh()->load(['customer', 'items.product', 'items.ebooks', 'items.processedBy', 'uploads']);
        $this->dispatch('close-wa-modal');
    }

    /**
     * Selesaikan bagian JASA dari pesanan.
     *
     * PENTING — tidak mengubah alur produk non-jasa sama sekali:
     *  - Hanya item ber-produk `butuh_file` yang ditandai terkirim di sini.
     *  - Item akun biasa TETAP lewat tombol "Kirim WhatsApp" (updateStatus()).
     *  - Order baru jadi 'completed' bila SELURUH item sudah terkirim, memakai
     *    syarat yang sama dengan alur lama, dan cashflow disinkronkan dengan
     *    SyncCashFlowAction + payload yang sama persis.
     */
    public function selesaikanJasa(SyncCashFlowAction $syncCashFlow): void
    {
        if (! $this->order->butuhUpload()) {
            return;
        }

        DB::transaction(function () use ($syncCashFlow) {
            // 1) Tandai item JASA sebagai terkirim (item non-jasa dilewati).
            foreach ($this->order->items as $item) {
                if ((bool) optional($item->product)->butuh_file && $item->delivery_status !== 'delivered') {
                    $item->update(['delivery_status' => 'delivered']);
                }
            }

            // 2) Syarat penyelesaian SAMA dengan alur lama: tak ada item tersisa.
            $masihAdaBelumDelivered = $this->order
                ->items()
                ->where('delivery_status', '!=', 'delivered')
                ->exists();

            if (! $masihAdaBelumDelivered) {
                $this->order->update([
                    'status' => 'completed',
                    'paid_at' => $this->order->paid_at ?: now(),
                ]);

                $syncCashFlow->execute($this->order, [
                    'amount' => $this->order->total,
                    'type' => 'income',
                    'date' => $this->order->paid_at ?: $this->order->created_at,
                    'category' => 'e-commerce',
                    'description' => $this->order->deskripsi ?? 'Pembelian jasa dari e-commerce',
                ]);
            }
        });

        $this->reloadOrder();
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('order-updated', message: $this->order->status === 'completed'
            ? 'Pesanan jasa selesai. Omset tercatat di cash flow.'
            : 'Bagian jasa selesai. Item akun masih menunggu pengiriman.');
    }

    /** Muat ulang order beserta relasi (dipakai aksi pengecekan). */
    private function reloadOrder(): void
    {
        $this->order = $this->order->fresh()->load(['customer', 'items.product', 'items.ebooks', 'items.processedBy', 'uploads']);
    }

    private function pengecekan(string $uploadId): OrderUpload
    {
        return OrderUpload::where('order_id', $this->order->id)->where('id', $uploadId)->firstOrFail();
    }

    /** Admin mulai memproses satu pengecekan. */
    public function mulaiProses(string $uploadId): void
    {
        $up = $this->pengecekan($uploadId);
        if ($up->status === 'menunggu') {
            $up->update(['status' => 'diproses', 'diproses_at' => now()]);
        }
        $this->reloadOrder();
        $this->dispatch('sidebar-badge-updated'); // badge "menunggu" berkurang
        $this->dispatch('order-updated', message: 'Pengecekan ditandai sedang diproses.');
    }

    /** Buka form unggah hasil untuk satu pengecekan. */
    public function bukaUploadHasil(string $uploadId): void
    {
        $this->uploadAktifId = $uploadId;
        $this->resetFormHasil();

        // Pra-isi dengan persen yang SUDAH tersimpan. Tanpa ini, saat admin
        // menekan "Ganti" hanya untuk salah satu berkas, kolom persen slot
        // lain tampil kosong lalu ikut tersimpan sebagai null — persen yang
        // sudah benar jadi hilang padahal berkasnya tidak diganti.
        $up = $this->pengecekan($uploadId);
        $this->persentaseInput = $up->persentase;
        $this->persentaseAiInput = $up->persentase_ai;
    }

    public function tutupUploadHasil(): void
    {
        $this->uploadAktifId = null;
        $this->resetFormHasil();
    }

    /**
     * Slot unggah hasil mana yang perlu ditampilkan admin.
     *
     * Disesuaikan dengan yang BENAR-BENAR dibeli customer, supaya admin tak
     * disodori kolom yang tak relevan (mis. pesanan cek AI polos dulu tetap
     * menampilkan 3 slot):
     *   - parafrase (per halaman) → ketiganya (DOCX + plagiasi + AI)
     *   - selain itu → plagiasi bila ada layanan/add-on pengecekan kemiripan,
     *     AI bila ada layanan/add-on deteksi AI.
     *
     * Slot yang SUDAH berisi berkas selalu ditampilkan, apa pun aturannya —
     * kalau disembunyikan, hasil yang terlanjur tersimpan jadi tak bisa
     * diunduh maupun diganti.
     *
     * @param  string  $jenis  'plagiasi' | 'ai' | 'docx'
     */
    public function slotTampil(string $jenis): bool
    {
        $up = $this->uploadAktifId ? OrderUpload::find($this->uploadAktifId) : null;

        $sudahBerisi = match ($jenis) {
            'plagiasi' => (bool) optional($up)->hasil_path,
            'ai' => (bool) optional($up)->hasil_ai_path,
            'docx' => (bool) optional($up)->hasil_docx_path,
            default => false,
        };

        if ($sudahBerisi) {
            return true;
        }

        /*
         * Slot mengikuti JENIS unggahan ini — bukan seluruh pesanan. Satu
         * pesanan bisa memuat unggahan AI dan unggahan plagiasi terpisah;
         * masing-masing hanya perlu slot hasilnya sendiri.
         *   parafrase → ketiganya (dokumen hasil + bukti plagiasi + bukti AI)
         *   ai        → slot AI
         *   plagiasi  → slot plagiasi
         */
        $jenisUpload = optional($up)->jenis;

        if ($jenisUpload) {
            return match ($jenisUpload) {
                'parafrase' => true,
                'ai' => $jenis === 'ai',
                'plagiasi', 'pengecekan' => $jenis === 'plagiasi',
                default => $jenis === 'plagiasi',
            };
        }

        // Unggahan lama tanpa jenis: pertahankan perilaku berbasis pesanan.
        if ($this->order->adaParafrase()) {
            return true;
        }

        return match ($jenis) {
            'plagiasi' => $this->order->punyaLayananJasa('pakai_exclude'),
            'ai' => $this->order->punyaLayananJasa('cek_ai'),
            default => false,
        };
    }

    /** Nomor urut slot yang tampil (1,2,3) — mengikuti slot yang benar-benar ada. */
    public function nomorSlot(string $jenis): int
    {
        $urutan = ['plagiasi', 'ai', 'docx'];
        $no = 0;

        foreach ($urutan as $j) {
            if ($this->slotTampil($j)) {
                $no++;
            }
            if ($j === $jenis) {
                return $no;
            }
        }

        return $no;
    }

    private function resetFormHasil(): void
    {
        $this->reset('hasilFile', 'persentaseInput', 'hasilAiFile', 'persentaseAiInput', 'hasilDocxFile');
        $this->persenTerbacaOtomatis = false;
        $this->persenGagalBaca = false;
        $this->persenAiTerbacaOtomatis = false;
        $this->persenAiGagalBaca = false;
        $this->sumberAi = null;
        $this->labelAi = null;
        $this->pilihanAi = [];
    }

    /** Saat admin memilih file hasil PDF, coba baca persen kemiripan untuk pra-isi. */
    public function updatedHasilFile(): void
    {
        $this->persenTerbacaOtomatis = false;
        $this->persenGagalBaca = false;

        if (! $this->hasilFile) {
            return;
        }

        $ext = strtolower((string) $this->hasilFile->getClientOriginalExtension());
        if ($ext === 'pdf') {
            $persen = PlagiarismReader::persenDariPdf($this->hasilFile->getRealPath());
            if (! is_null($persen)) {
                $this->persentaseInput = $persen;
                $this->persenTerbacaOtomatis = true;

                return;
            }
        }

        // Berkas BARU tapi persennya tak terbaca (DOCX / format tak dikenal):
        // kosongkan agar angka milik berkas LAMA tidak ikut terbawa.
        $this->persentaseInput = null;
        $this->persenGagalBaca = true;
    }

    /** Saat admin memilih PDF hasil cek AI, coba baca persen AI untuk pra-isi. */
    public function updatedHasilAiFile(): void
    {
        $this->persenAiTerbacaOtomatis = false;
        $this->persenAiGagalBaca = false;
        $this->sumberAi = null;
        $this->labelAi = null;
        $this->pilihanAi = [];

        if (! $this->hasilAiFile) {
            return;
        }

        if (strtolower((string) $this->hasilAiFile->getClientOriginalExtension()) === 'pdf') {
            $baca = PlagiarismReader::bacaAi($this->hasilAiFile->getRealPath());

            // Berkas memuat beberapa laporan bertumpuk. Pra-isi dengan nilai
            // lapisan teratas (yang terlihat saat PDF dibuka), tapi tampilkan
            // semua temuan agar admin bisa mengoreksi bila perlu.
            if ($baca && ! empty($baca['ambigu'])) {
                $this->persentaseAiInput = $baca['persen'];
                $this->sumberAi = $baca['sumber'];
                $this->labelAi = $baca['label'];
                $this->pilihanAi = $baca['nilai'];

                return;
            }

            if ($baca) {
                $this->persentaseAiInput = $baca['persen'];
                $this->persenAiTerbacaOtomatis = true;
                $this->sumberAi = $baca['sumber'];
                $this->labelAi = $baca['label'];

                return;
            }
        }

        // Gagal dibaca: KOSONGKAN isian. Kalau angka lama dibiarkan, admin
        // mengira itu hasil berkas yang baru diunggah padahal bukan — persen
        // yang salah bisa ikut tampil ke customer.
        $this->persentaseAiInput = null;
        $this->persenAiGagalBaca = true;
    }

    /** Simpan file hasil + persen (dikonfirmasi admin) → status selesai. */
    public function simpanHasil(SyncCashFlowAction $syncCashFlow): void
    {
        if (! $this->uploadAktifId) {
            return;
        }

        $this->validate([
            // File HASIL bisa besar (laporan Turnitin puluhan MB) → batas 100MB.
            'hasilFile' => ['nullable', 'file', 'mimes:pdf,docx', 'max:102400'],
            'hasilAiFile' => ['nullable', 'file', 'mimes:pdf', 'max:102400'],
            'hasilDocxFile' => ['nullable', 'file', 'mimes:docx', 'max:102400'],
            'persentaseInput' => ['nullable', 'integer', 'min:0', 'max:100'],
            'persentaseAiInput' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], [
            'hasilFile.mimes' => 'Hasil cek plagiasi harus PDF atau DOCX.',
            'hasilAiFile.mimes' => 'Hasil cek AI harus PDF.',
            'hasilDocxFile.mimes' => 'Dokumen hasil harus DOCX.',
            'hasilFile.max' => 'Ukuran file maksimal 100 MB.',
            'hasilAiFile.max' => 'Ukuran file maksimal 100 MB.',
            'hasilDocxFile.max' => 'Ukuran file maksimal 100 MB.',
            'persentaseInput.integer' => 'Persen harus angka 0–100.',
            'persentaseAiInput.integer' => 'Persen AI harus angka 0–100.',
        ]);

        $up = $this->pengecekan($this->uploadAktifId);

        // Minimal satu berkas hasil harus ada (baru atau sudah tersimpan).
        $adaBaru = $this->hasilFile || $this->hasilAiFile || $this->hasilDocxFile;
        $adaLama = $up->hasil_path || $up->hasil_ai_path || $up->hasil_docx_path;
        if (! $adaBaru && ! $adaLama) {
            $this->addError('hasilFile', 'Unggah minimal satu berkas hasil.');

            return;
        }

        // Hapus berkas lama yang digantikan (hemat ruang disk).
        if ($this->hasilFile) {
            $this->hapusBerkasLama($up->hasil_path);
        }

        $folder = 'order-uploads/'.$this->order->id.'/hasil';
        $data = [
            'persentase' => $this->persentaseInput === '' || is_null($this->persentaseInput) ? null : (int) $this->persentaseInput,
            'persentase_ai' => $this->persentaseAiInput === '' || is_null($this->persentaseAiInput) ? null : (int) $this->persentaseAiInput,
            'status' => 'selesai',
            'selesai_at' => now(),
        ];

        // 1) Hasil cek plagiasi
        if ($this->hasilFile) {
            $data['hasil_path'] = $this->hasilFile->store($folder, 'local');
            $data['hasil_nama'] = $this->hasilFile->getClientOriginalName();
            $data['hasil_ukuran'] = $this->hasilFile->getSize();
            $data['hasil_mime'] = $this->hasilFile->getMimeType();
        }

        // 2) Hasil cek AI
        if ($this->hasilAiFile) {
            $this->hapusBerkasLama($up->hasil_ai_path);
            $data['hasil_ai_path'] = $this->hasilAiFile->store($folder, 'local');
            $data['hasil_ai_nama'] = $this->hasilAiFile->getClientOriginalName();
            $data['hasil_ai_ukuran'] = $this->hasilAiFile->getSize();
            $data['hasil_ai_mime'] = $this->hasilAiFile->getMimeType();
        }

        // 3) Dokumen hasil parafrase (DOCX)
        if ($this->hasilDocxFile) {
            $this->hapusBerkasLama($up->hasil_docx_path);
            $data['hasil_docx_path'] = $this->hasilDocxFile->store($folder, 'local');
            $data['hasil_docx_nama'] = $this->hasilDocxFile->getClientOriginalName();
            $data['hasil_docx_ukuran'] = $this->hasilDocxFile->getSize();
            $data['hasil_docx_mime'] = $this->hasilDocxFile->getMimeType();
        }

        $up->update($data);

        // Beri tahu customer via email bahwa hasil siap + link unduh.
        $this->kirimEmailHasil($up);

        $this->tutupUploadHasil();
        $this->reloadOrder();

        // Bila seluruh kuota sudah terpakai DAN semua pengecekan selesai,
        // pesanan jasa langsung dituntaskan (item terkirim + omset ke cashflow).
        // Selama kuota masih tersisa, pesanan sengaja dibiarkan aktif agar
        // unggahan berikutnya tetap terpantau admin.
        if ($this->order->jasaTuntas()) {
            $this->selesaikanJasa($syncCashFlow);

            return;
        }

        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('order-updated', message: 'Hasil pengecekan berhasil diunggah.');
    }

    /**
     * Kirim email "hasil siap" + link ke customer. Terisolasi & try/catch:
     * kegagalan email TIDAK boleh membatalkan penyimpanan hasil. Dilewati bila
     * email customer kosong/tidak valid (mis. pelanggan dibuat admin tanpa email).
     */
    private function kirimEmailHasil(OrderUpload $up): void
    {
        try {
            $email = optional($this->order->customer)->email;
            if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            // Pakai mailer 'phoenix' secara eksplisit (halo@phoenixdigital.id).
            // Tanpa ini, email keluar lewat mailer default (akun ACM) sehingga
            // alamat pengirim halo@phoenixdigital.id ditolak server ("553 Sender
            // address rejected: not owned by user").
            Mail::mailer('phoenix')->to($email)->send(new JasaHasilMail($this->order, $up));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Hapus berkas lama di disk privat bila ada. */
    private function hapusBerkasLama(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    /** Batalkan pengecekan (file customer bermasalah) → kuota dikembalikan. */
    public function batalkanPengecekan(string $uploadId): void
    {
        $up = $this->pengecekan($uploadId);
        $up->update(['status' => 'dibatalkan']);
        $this->reloadOrder();
        $this->dispatch('sidebar-badge-updated'); // badge "menunggu" berkurang
        $this->dispatch('order-updated', message: 'Pengecekan dibatalkan. Kuota customer dikembalikan.');
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.order.order-detail');
    }
}
