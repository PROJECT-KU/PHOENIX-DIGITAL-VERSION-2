<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if (
            in_array($order->status, ['paid', 'processing', 'completed']) &&
            ! $order->points_calculated &&
            ! $order->used_points
        ) {
            $customer = $order->customer;

            if ($customer && $customer->status_member === 'active') {
                $customer->updatePoints();
            }
        }

        if ($order->isDirty('status') &&
           $order->status === 'completed' &&
           $order->referrer_id &&
           $order->referral_code) {

            $referrer = Customer::find($order->referrer_id);

            if ($referrer && $referrer->status_member === 'active') {
                // Cek apakah customer ini sudah pernah direfer sebelumnya
                $alreadyReferred = Order::where('referrer_id', $order->referrer_id)
                    ->where('customer_id', $order->customer_id)
                    ->where('status', 'paid')
                    ->where('id', '!=', $order->id)
                    ->exists();

                // Hanya berikan poin jika ini transaksi pertama dari customer ini
                if (! $alreadyReferred) {
                    $referrer->addReferralPoints(2);

                    Log::info('Referral points granted', [
                        'referrer_id' => $referrer->id,
                        'referrer_name' => $referrer->nama,
                        'order_id' => $order->id,
                        'customer_id' => $order->customer_id,
                        'points_added' => 2,
                    ]);
                }
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
