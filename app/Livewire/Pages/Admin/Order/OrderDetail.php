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
        $this->reset('hasilFile', 'persentaseInput');
        $this->persenTerbacaOtomatis = false;
    }

    public function tutupUploadHasil(): void
    {
        $this->uploadAktifId = null;
        $this->reset('hasilFile', 'persentaseInput');
        $this->persenTerbacaOtomatis = false;
    }

    /** Saat admin memilih file hasil PDF, coba baca persen kemiripan untuk pra-isi. */
    public function updatedHasilFile(): void
    {
        $this->persenTerbacaOtomatis = false;

        if (! $this->hasilFile) {
            return;
        }

        $ext = strtolower((string) $this->hasilFile->getClientOriginalExtension());
        if ($ext === 'pdf') {
            $persen = PlagiarismReader::persenDariPdf($this->hasilFile->getRealPath());
            if (! is_null($persen)) {
                $this->persentaseInput = $persen;
                $this->persenTerbacaOtomatis = true;
            }
        }
    }

    /** Simpan file hasil + persen (dikonfirmasi admin) → status selesai. */
    public function simpanHasil(SyncCashFlowAction $syncCashFlow): void
    {
        if (! $this->uploadAktifId) {
            return;
        }

        $this->validate([
            'hasilFile' => ['required', 'file', 'mimes:pdf,docx', 'max:20480'],
            'persentaseInput' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], [
            'hasilFile.required' => 'Pilih file hasil dulu.',
            'hasilFile.mimes' => 'Format hasil harus PDF atau DOCX.',
            'hasilFile.max' => 'Ukuran file maksimal 20 MB.',
            'persentaseInput.integer' => 'Persen harus angka 0–100.',
        ]);

        $up = $this->pengecekan($this->uploadAktifId);

        // Hapus hasil lama bila mengganti.
        if ($up->hasil_path && Storage::disk('local')->exists($up->hasil_path)) {
            Storage::disk('local')->delete($up->hasil_path);
        }

        $path = $this->hasilFile->store('order-uploads/'.$this->order->id.'/hasil', 'local');

        $up->update([
            'hasil_path' => $path,
            'hasil_nama' => $this->hasilFile->getClientOriginalName(),
            'hasil_ukuran' => $this->hasilFile->getSize(),
            'hasil_mime' => $this->hasilFile->getMimeType(),
            'persentase' => $this->persentaseInput === '' || is_null($this->persentaseInput) ? null : (int) $this->persentaseInput,
            'status' => 'selesai',
            'selesai_at' => now(),
        ]);

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
