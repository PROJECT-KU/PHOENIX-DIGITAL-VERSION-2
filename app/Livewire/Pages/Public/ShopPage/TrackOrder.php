<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Order;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TrackOrder extends Component
{
    public $orderNumber = '';

    public $phone = '';

    public ?Order $order = null;

    public bool $searched = false;

    /**
     * Lacak pesanan berdasarkan nomor order + nomor HP (tanpa login).
     * Read-only — tidak menyentuh logika checkout/pembayaran.
     */
    public function track()
    {
        $this->validate([
            'orderNumber' => 'required|string|max:60',
            'phone' => 'required|string|max:30',
        ], [], [
            'orderNumber' => 'nomor order',
            'phone' => 'nomor HP',
        ]);

        $key = 'track-order:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('orderNumber', 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.');

            return;
        }
        RateLimiter::hit($key, 60);

        $this->order = null;
        $this->searched = true;

        $found = Order::where('order_number', trim($this->orderNumber))->with(['customer', 'items'])->first();

        if ($found && $found->customer && $this->localPhone($found->customer->no_hp) !== ''
            && $this->localPhone($found->customer->no_hp) === $this->localPhone($this->phone)) {
            $this->order = $found;
        }
    }

    /** Samakan format nomor: buang non-digit, awalan 62/0, sisakan bagian lokal. */
    private function localPhone($v): string
    {
        $d = preg_replace('/\D/', '', (string) $v);
        $d = preg_replace('/^62/', '', $d);

        return preg_replace('/^0/', '', $d);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.shop-page.track-order');
    }
}
