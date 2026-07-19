<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Promo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Flash Sale Akhir Tahun (Berlaku untuk semua produk)
        $flashSale = Promo::create([
            'id' => Str::uuid(),
            'nama_promo' => 'Flash Sale Spesial Akhir Tahun 2025',
            'deskripsi' => 'Diskon besar-besaran untuk menyambut tahun baru!',
            'tipe_promo' => 'flash_sale',
            'tipe_diskon' => 'persen',
            'diskon_member_persen' => 20.00,
            'diskon_non_member_persen' => 11.00,
            'untuk_member' => 'semua',
            'untuk_pembeli_pertama' => false,
            'min_pembelian' => 0,
            'mulai_promo' => now(),
            'selesai_promo' => now()->addDays(7), // 7 hari dari sekarang
            'is_active' => true,
            'prioritas' => 100,
            'can_stack_with_other' => true,
            'can_stack_with_referral' => true,
            'can_stack_with_points' => true,
            'show_on_homepage' => true,
            'badge_text' => 'FLASH SALE',
        ]);

        // Attach ke semua produk
        $allProducts = Product::all();
        $flashSale->products()->attach($allProducts->pluck('id'));

        // 2. Kode Promo Spesial "AKHIRTAHUN2025" (Member only)
        $kodePromo1 = Promo::create([
            'id' => Str::uuid(),
            'kode_promo' => 'AKHIRTAHUN2025',
            'nama_promo' => 'Promo Akhir Tahun untuk Member',
            'deskripsi' => 'Dapatkan potongan tambahan Rp 10.000 khusus member!',
            'tipe_promo' => 'kode_promo',
            'tipe_diskon' => 'nominal',
            'diskon_member_nominal' => 10000,
            'diskon_non_member_nominal' => 0,
            'untuk_member' => 'member_only',
            'untuk_pembeli_pertama' => false,
            'min_pembelian' => 50000,
            'mulai_promo' => now(),
            'selesai_promo' => now()->addDays(30),
            'is_active' => true,
            'prioritas' => 90,
            'can_stack_with_other' => true,
            'can_stack_with_referral' => true,
            'can_stack_with_points' => true,
            'show_on_homepage' => false,
        ]);

        $kodePromo1->products()->attach($allProducts->pluck('id'));

        // 3. Kode Promo "WELCOME2025" (Pembeli pertama)
        $kodePromo2 = Promo::create([
            'id' => Str::uuid(),
            'kode_promo' => 'WELCOME2025',
            'nama_promo' => 'Promo Welcome Pembeli Baru',
            'deskripsi' => 'Diskon 15% untuk pembelian pertama Anda!',
            'tipe_promo' => 'kode_promo',
            'tipe_diskon' => 'persen',
            'diskon_member_persen' => 15.00,
            'diskon_non_member_persen' => 15.00,
            'untuk_member' => 'semua',
            'untuk_pembeli_pertama' => true,
            'min_pembelian' => 30000,
            'mulai_promo' => now(),
            'selesai_promo' => now()->addDays(60),
            'is_active' => true,
            'prioritas' => 80,
            'can_stack_with_other' => true,
            'can_stack_with_referral' => true,
            'can_stack_with_points' => false, // Tidak bisa stack dengan points
            'show_on_homepage' => false,
        ]);

        $kodePromo2->products()->attach($allProducts->pluck('id'));

        // 4. Flash Sale Khusus Member (Hanya untuk member)
        $memberFlashSale = Promo::create([
            'id' => Str::uuid(),
            'nama_promo' => 'Member Exclusive Flash Sale',
            'deskripsi' => 'Flash sale eksklusif untuk member setia kami!',
            'tipe_promo' => 'flash_sale',
            'tipe_diskon' => 'persen',
            'diskon_member_persen' => 25.00,
            'diskon_non_member_persen' => 0,
            'untuk_member' => 'member_only',
            'untuk_pembeli_pertama' => false,
            'min_pembelian' => 0,
            'mulai_promo' => now()->addDays(8),
            'selesai_promo' => now()->addDays(10),
            'is_active' => true,
            'prioritas' => 95,
            'can_stack_with_other' => true,
            'can_stack_with_referral' => false,
            'can_stack_with_points' => true,
            'show_on_homepage' => true,
            'badge_text' => 'MEMBER ONLY',
        ]);

        $memberFlashSale->products()->attach($allProducts->pluck('id'));

        // 5. Kode Promo Nominal "HEMAT5K" (Semua bisa pakai)
        $kodePromo3 = Promo::create([
            'id' => Str::uuid(),
            'kode_promo' => 'HEMAT5K',
            'nama_promo' => 'Potongan Langsung Rp 5.000',
            'deskripsi' => 'Dapatkan potongan langsung Rp 5.000 untuk semua pembelian!',
            'tipe_promo' => 'kode_promo',
            'tipe_diskon' => 'nominal',
            'diskon_member_nominal' => 5000,
            'diskon_non_member_nominal' => 5000,
            'untuk_member' => 'semua',
            'untuk_pembeli_pertama' => false,
            'min_pembelian' => 20000,
            'mulai_promo' => now(),
            'selesai_promo' => now()->addDays(45),
            'is_active' => true,
            'prioritas' => 70,
            'can_stack_with_other' => true,
            'can_stack_with_referral' => true,
            'can_stack_with_points' => true,
            'show_on_homepage' => false,
        ]);

        $kodePromo3->products()->attach($allProducts->pluck('id'));

        $this->command->info('✅ Promo seeder completed successfully!');
    }
}
