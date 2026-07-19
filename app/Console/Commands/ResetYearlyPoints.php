<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class ResetYearlyPoints extends Command
{
    protected $signature = 'points:reset-yearly';

    protected $description = 'Reset poin member ke 0 setiap awal tahun (poin kadaluarsa akhir tahun kalender).';

    public function handle(): int
    {
        $year = now()->year;

        $affected = Customer::query()->update([
            'point' => 0,
            'point_balance' => 0,
            'points_year' => $year,
        ]);

        $this->info("Poin member direset untuk tahun {$year}: {$affected} pelanggan.");

        return self::SUCCESS;
    }
}
