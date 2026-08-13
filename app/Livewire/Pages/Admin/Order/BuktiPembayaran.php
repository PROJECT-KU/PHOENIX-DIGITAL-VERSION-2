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

    public function mount(Order $order): void
    {
        // Hanya draft pembayaran manual yang punya urusan di layar ini.
        // QRIS dinamis punya layarnya sendiri (QrisPayment), dan pesanan yang
        // sudah lewat draft tidak boleh diseret mundur ke sini.
        abort_unless(
            $order->status === 'draft'
            && in_array($order->payment_method, ['transfer', 'qris_statis'], true),
            404
        );

        $this->order = $order;
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

        // Lewat Eloquent, BUKAN query builder: OrderObserver yang mencatat modal
        // per item bergantung pada event `updated`.
        $this->order->update([
            'bukti_pembayaran' => $jalur,
            'status' => 'pending',
        ]);

        session()->flash('successCreated', 'Bukti pembayaran tersimpan. Pesanan kini aktif dan siap diproses.');

        return redirect()->route('admin.pesanantoko.detail', $this->order);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.order.bukti-pembayaran');
    }
}
