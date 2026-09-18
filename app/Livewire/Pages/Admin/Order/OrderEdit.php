<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\EditPesanan;
use App\Support\ItemPaket;
use App\Support\RiwayatPesanan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Ubah pesanan yang BELUM dibayar: produk, satuan, durasi, jumlah, catatan.
 * Syaratnya di App\Support\EditPesanan. Harga dihitung ulang dari katalog.
 */
class OrderEdit extends Component
{
    #[Locked]
    public string $orderId;

    /** @var array<int, array{product_id: string, duration_type: string, duration_value: int, quantity: int}> */
    public array $baris = [];

    public string $catatan = '';

    public function mount(Order $order): void
    {
        $this->orderId = $order->id;
        $this->catatan = (string) $order->customer_notes;
        $this->baris = $order->items()->orderBy('created_at')->get()->map(fn (OrderItem $it) => [
            'product_id' => (string) $it->product_id,
            'duration_type' => $it->duration_type ?: 'bulan',
            'duration_value' => max(1, (int) $it->duration_value),
            'quantity' => max(1, (int) $it->quantity),
        ])->all();
    }

    protected function pesanan(): Order
    {
        return Order::with('customer', 'items')->findOrFail($this->orderId);
    }

    public function tambahBaris(): void
    {
        $this->baris[] = ['product_id' => '', 'duration_type' => 'bulan', 'duration_value' => 1, 'quantity' => 1];
    }

    public function hapusBaris(int $i): void
    {
        unset($this->baris[$i]);
        $this->baris = array_values($this->baris);
    }

    /** Harga tiap baris & total baru, dari katalog sekarang. */
    protected function hitung(): array
    {
        $produk = Product::whereIn('id', collect($this->baris)->pluck('product_id')->filter())->get()->keyBy('id');
        $rinci = [];
        foreach ($this->baris as $i => $b) {
            $p = $produk->get($b['product_id']);
            $harga = $p ? ItemPaket::hargaNormal($p, $b['duration_type'], max(1, (int) $b['duration_value'])) : 0;
            $rinci[$i] = ['produk' => $p, 'harga' => $harga, 'subtotal' => $harga * max(1, (int) $b['quantity'])];
        }

        return $rinci;
    }

    public function simpan()
    {
        abort_unless(auth()->user()?->hasPermission('edit_pemesanantoko'), 403);

        $this->validate([
            'baris' => 'required|array|min:1',
            'baris.*.product_id' => 'required|exists:products,id',
            'baris.*.duration_type' => 'required|in:bulan,tahun',
            'baris.*.duration_value' => 'required|integer|min:1|max:120',
            'baris.*.quantity' => 'required|integer|min:1|max:100',
            'catatan' => 'nullable|string|max:2000',
        ], [
            'baris.required' => 'Pesanan minimal berisi 1 produk.',
            'baris.*.product_id.required' => 'Pilih produknya.',
        ]);

        $order = $this->pesanan();
        // Diperiksa ulang saat simpan: pesanan bisa saja dibayar selagi form terbuka.
        if ($alasan = EditPesanan::alasanTidakBisa($order)) {
            $this->dispatch('swal-error', message: $alasan);

            return;
        }

        $rinci = $this->hitung();
        if (collect($rinci)->contains(fn ($r) => $r['harga'] <= 0)) {
            $this->addError('baris', 'Ada produk yang belum punya harga untuk durasi itu. Ubah durasinya atau isi harga di katalog.');

            return;
        }

        $subtotal = (int) collect($rinci)->sum('subtotal');
        $totalLama = (int) $order->total;
        $totalBaru = $subtotal + (int) $order->unique_code;

        DB::transaction(function () use ($order, $rinci, $subtotal, $totalBaru) {
            $order->items()->delete();
            foreach ($this->baris as $i => $b) {
                $p = $rinci[$i]['produk'];
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'product_name' => $p->nama_akun,
                    'product_description' => $p->deskripsi ?? null,
                    'product_image' => $p->image ?? null,
                    'duration_type' => $b['duration_type'],
                    'duration_value' => (int) $b['duration_value'],
                    'price' => $rinci[$i]['harga'],
                    'quantity' => (int) $b['quantity'],
                    'subtotal' => $rinci[$i]['subtotal'],
                ]);
            }
            $order->update([
                'subtotal' => $subtotal,
                'total' => $totalBaru,
                'customer_notes' => $this->catatan ?: null,
            ]);
        });

        $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
        RiwayatPesanan::catat($order->id, 'diubah', 'Isi pesanan diubah · total '.$rp($totalLama).' → '.$rp($totalBaru));

        session()->flash('successCreated', 'Pesanan diperbarui. Total sekarang '.$rp($totalBaru).'.');

        return $this->redirectRoute('admin.pesanantoko.detail', $order, navigate: true);
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $order = $this->pesanan();
        $rinci = $this->hitung();
        $subtotal = (int) collect($rinci)->sum('subtotal');

        return view('livewire.pages.admin.order.order-edit', [
            'order' => $order,
            'alasan' => EditPesanan::alasanTidakBisa($order),
            'rinci' => $rinci,
            'produkPilihan' => Product::orderBy('nama_akun')->get(['id', 'nama_akun']),
            'totalBaru' => $subtotal + (int) $order->unique_code,
            // Harga lama berbeda dari harga katalog (mis. dari paket bundling / promo otomatis).
            'hargaBerubah' => $order->items->contains(function ($it) {
                $p = Product::find($it->product_id);

                return $p && ItemPaket::hargaNormal($p, $it->duration_type ?: 'bulan', max(1, (int) $it->duration_value)) !== (int) $it->price;
            }),
        ]);
    }
}
