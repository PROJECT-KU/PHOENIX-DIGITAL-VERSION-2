<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Unggah bukti bayar untuk pesanan DRAFT bermetode transfer / QRIS statis.
 *
 * Pasangan dari "Simpan ke Draft" di OrderForm. Alurnya: admin menyusun
 * pesanan lalu memarkirnya sebagai draft tanpa bukti; ketika pelanggan sudah
 * membayar, draft itu dibuka di sini, buktinya diunggah, dan barulah pesanan
 * menjadi `pending` serta masuk ke detail untuk diproses.
 *
 * Dibuat sebagai layar tersendiri — bukan ditempel di OrderDetail — dengan
 * alasan yang sama seperti QrisPayment: pesanan draft BELUM boleh muncul di
 * detail, karena di sana admin akan mengira pesanan sudah siap dikerjakan.
 */
class BuktiPembayaran extends Component
{
    use WithFileUploads;

    public Order $order;

    public $bukti;

    /**
     * Sekadar MENGGANTI berkas, bukan mengaktifkan pesanan draft.
     *
     * Membedakan keduanya penting: yang pertama tidak boleh menyentuh status.
     */
    public bool $gantiSaja = false;

    public function mount(Order $order): void
    {
        // Layar ini melayani pembayaran yang buktinya diunggah MANUAL saja.
        // QRIS dinamis punya layarnya sendiri (QrisPayment) karena dikonfirmasi
        // penyedia, bukan lewat berkas.
        abort_unless(
            in_array($order->payment_method, ['transfer', 'qris_statis'], true),
            404
        );

        $this->order = $order;

        // Draft → mengunggah bukti sekaligus mengaktifkan pesanan.
        // Selain draft → admin sedang memperbaiki berkas yang salah unggah.
        $this->gantiSaja = $order->status !== 'draft';
    }

    public function simpan()
    {
        $this->validate(
            ['bukti' => 'required|image|max:4096'],
            [
                'bukti.required' => 'Bukti pembayaran wajib diunggah.',
                'bukti.image' => 'Berkas harus berupa gambar.',
                'bukti.max' => 'Ukuran maksimal 4 MB.',
            ],
            ['bukti' => 'bukti pembayaran']
        );

        // Disk PRIVAT, sama dengan jalur pembuatan pesanan biasa: bukti bayar
        // memuat data rekening pelanggan dan tidak boleh bisa dibuka lewat URL.
        $jalur = $this->bukti->store('bukti_pembayaran', 'local');

        $lama = $this->order->bukti_pembayaran;

        // Lewat Eloquent, BUKAN query builder: OrderObserver yang mencatat modal
        // per item bergantung pada event `updated`.
        //
        // Status HANYA diubah bila pesanannya masih draft. Saat admin sekadar
        // memperbaiki berkas yang salah unggah, tahap pembayarannya tidak boleh
        // ikut bergeser.
        $this->order->update(array_filter([
            'bukti_pembayaran' => $jalur,
            'status' => $this->gantiSaja ? null : 'pending',
        ], fn ($nilai) => $nilai !== null));

        // Berkas lama dihapus SETELAH yang baru tersimpan, supaya kegagalan di
        // tengah tidak meninggalkan pesanan tanpa bukti sama sekali.
        if ($lama && $lama !== $jalur && \Illuminate\Support\Facades\Storage::disk('local')->exists($lama)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($lama);
        }

        session()->flash('successCreated', $this->gantiSaja
            ? 'Bukti pembayaran berhasil diganti.'
            : 'Bukti pembayaran tersimpan. Pesanan kini aktif dan siap diproses.');

        return redirect()->route('admin.pesanantoko.detail', $this->order);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.order.bukti-pembayaran');
    }
}
