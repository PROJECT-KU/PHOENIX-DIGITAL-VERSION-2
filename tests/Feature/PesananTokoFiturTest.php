<?php

use App\Livewire\Pages\Admin\Order\OrderDetail;
use App\Livewire\Pages\Admin\Order\OrderForm;
use App\Livewire\Pages\Admin\Order\OrderList;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderRiwayat;
use App\Models\Product;
use App\Support\PengingatPerpanjangan;
use App\Support\RiwayatPesanan;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Pesanan Toko: Segera Habis + pengingat WA, saringan di URL, saringan
 * lanjutan, urutan, ekspor Excel, riwayat pesanan, perpanjangan satu klik.
 */
beforeEach(function () {
    RiwayatPesanan::lupakan();
});

function tokoAdmin(array $izin = ['view_pemesanantoko', 'edit_pemesanantoko', 'create_pemesanantoko']): \App\Models\User
{
    $peran = \App\Models\Role::create(['name' => 'uji-'.Str::random(6), 'description' => 'uji']);
    foreach ($izin as $nama) {
        $p = \App\Models\Permission::firstOrCreate(['name' => $nama], ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']);
        $peran->permissions()->attach($p->id);
    }

    return \App\Models\User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);
}

function tokoPesanan(array $isian = [], array $item = [], ?Product $produk = null): Order
{
    $order = Order::create(array_merge([
        'id' => Str::uuid(),
        'order_number' => 'INV-UJI-'.Str::upper(Str::random(6)),
        'customer_id' => Customer::create([
            'nama' => 'Sari', 'no_hp' => '0812'.random_int(10000000, 99999999), 'email' => uniqid().'@contoh.test',
        ])->id,
        'subtotal' => 50000, 'total' => 50000, 'unique_code' => 0,
        'status' => 'completed', 'payment_method' => 'transfer', 'expired_at' => now()->addDay(),
    ], $isian));

    $produk ??= Product::create(['nama_akun' => 'Canva Pro']);
    OrderItem::create(array_merge([
        'order_id' => $order->id, 'product_id' => $produk->id, 'product_name' => $produk->nama_akun,
        'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 50000, 'quantity' => 1, 'subtotal' => 50000,
    ], $item));

    return $order->fresh('items', 'customer');
}

it('tab Segera Habis hanya memuat akun yang berakhir dalam 7 hari pada pesanan yang dibayar', function () {
    $this->actingAs(tokoAdmin());
    tokoPesanan([], ['product_name' => 'Akun Tiga Hari', 'end_date' => today()->addDays(3)]);
    tokoPesanan([], ['product_name' => 'Akun Sepuluh Hari', 'end_date' => today()->addDays(10)]);
    tokoPesanan(['status' => 'cancelled'], ['product_name' => 'Akun Batal', 'end_date' => today()->addDays(2)]);
    tokoPesanan([], ['product_name' => 'Akun Ditandai Habis', 'end_date' => today()->addDays(2), 'subscription_status' => 'habis']);

    Livewire::test(OrderList::class)
        ->call('setTab', 'segera')
        ->assertSee('Akun Tiga Hari')
        ->assertSee('3 hari lagi')
        ->assertSee('api.whatsapp.com/send', false)
        ->assertDontSee('Akun Sepuluh Hari')
        ->assertDontSee('Akun Batal')
        ->assertDontSee('Akun Ditandai Habis');
});

it('pesan pengingat WA berisi sisa hari dan tautan beli produk yang sama', function () {
    $order = tokoPesanan([], ['end_date' => today()->addDay()]);
    $item = $order->items->first();

    $url = urldecode(PengingatPerpanjangan::tautanWa($item, 'segera'));

    expect($url)->toStartWith('https://api.whatsapp.com/send?phone=62')
        ->toContain('*BESOK*')
        ->toContain(route('shop.detail-product', $item->product_id))
        ->toContain($order->order_number);
});

it('menandai dihubungi (satu & massal) mengisi kolom yang benar dan tercatat di riwayat', function () {
    $this->actingAs(tokoAdmin());
    $a = tokoPesanan([], ['end_date' => today()->addDays(2)])->items->first();
    $b = tokoPesanan([], ['end_date' => today()->addDays(4)])->items->first();
    $habis = tokoPesanan([], ['end_date' => today()->subDays(3)])->items->first();

    $t = Livewire::test(OrderList::class)->call('setTab', 'segera');
    $t->call('tandaiDihubungi', (string) $a->id);
    expect($a->fresh()->ingat_perpanjang_at)->not->toBeNull()
        ->and($b->fresh()->ingat_perpanjang_at)->toBeNull();

    $t->set('terpilih', [(string) $b->id])->call('tandaiDihubungi');
    expect($b->fresh()->ingat_perpanjang_at)->not->toBeNull();

    $t->call('setTab', 'habis')->set('terpilih', [(string) $habis->id])->call('tandaiDihubungi');
    expect($habis->fresh()->habis_notified_at)->not->toBeNull()
        ->and(OrderRiwayat::where('order_id', $a->order_id)->where('aksi', 'wa')->exists())->toBeTrue();
});

it('menandai dihubungi ditolak tanpa izin ubah pesanan', function () {
    $this->actingAs(tokoAdmin(['view_pemesanantoko']));
    $item = tokoPesanan([], ['end_date' => today()->addDays(2)])->items->first();

    Livewire::test(OrderList::class)->call('setTab', 'segera')
        ->call('tandaiDihubungi', (string) $item->id)
        ->assertForbidden();
});

it('saringan dibaca dari alamat halaman, jadi tetap ada setelah kembali dari detail', function () {
    $this->actingAs(tokoAdmin());
    tokoPesanan(['order_number' => 'INV-CARI-111']);
    tokoPesanan(['order_number' => 'INV-LAIN-222']);

    Livewire::withQueryParams(['cari' => 'CARI-111'])
        ->test(OrderList::class)
        ->assertSet('search', 'CARI-111')
        ->assertSee('INV-CARI-111')
        ->assertDontSee('INV-LAIN-222');
});

it('saringan lanjutan: metode bayar, jenis, produk, dan rentang tanggal', function () {
    $this->actingAs(tokoAdmin());
    $jasa = Product::create(['nama_akun' => 'Cek Plagiasi', 'butuh_file' => true]);
    tokoPesanan(['order_number' => 'INV-QRIS-1', 'payment_method' => 'qris_dinamis']);
    tokoPesanan(['order_number' => 'INV-JASA-1'], [], $jasa);
    $lama = tokoPesanan(['order_number' => 'INV-LAMA-1']);
    $lama->forceFill(['created_at' => now()->subMonths(2)])->saveQuietly();

    Livewire::test(OrderList::class)->set('metode', 'qris_dinamis')
        ->assertSee('INV-QRIS-1')->assertDontSee('INV-JASA-1');

    Livewire::test(OrderList::class)->set('jenis', 'jasa')
        ->assertSee('INV-JASA-1')->assertDontSee('INV-QRIS-1');

    Livewire::test(OrderList::class)->set('jenis', 'akun')
        ->assertSee('INV-QRIS-1')->assertDontSee('INV-JASA-1');

    Livewire::test(OrderList::class)->set('produk', (string) $jasa->id)
        ->assertSee('INV-JASA-1')->assertDontSee('INV-QRIS-1');

    Livewire::test(OrderList::class)->set('tglDari', now()->subDays(5)->toDateString())
        ->assertSee('INV-QRIS-1')->assertDontSee('INV-LAMA-1');
});

it('urutan total terbesar menaruh pesanan termahal di atas; nilai urut karangan dikembalikan', function () {
    $this->actingAs(tokoAdmin());
    tokoPesanan(['order_number' => 'INV-MURAH', 'total' => 10000]);
    tokoPesanan(['order_number' => 'INV-MAHAL', 'total' => 900000]);

    $html = Livewire::test(OrderList::class)->set('urut', 'total_tinggi')->html();
    expect(strpos($html, 'INV-MAHAL'))->toBeLessThan(strpos($html, 'INV-MURAH'));

    Livewire::test(OrderList::class)->set('urut', 'sembarang')->assertSet('urut', 'terbaru')
        ->set('perHalaman', 999)->assertSet('perHalaman', 10);
});

it('ekspor Excel mengikuti tab, dan hanya untuk pengelola pesanan', function () {
    $this->freezeTime();
    Excel::fake();
    tokoPesanan(['order_number' => 'INV-EKSPOR-1']);

    $this->actingAs(tokoAdmin());
    Livewire::test(OrderList::class)->call('unduhExcel');
    Excel::assertDownloaded('Pesanan-Toko_all_'.now()->format('Ymd-His').'.xlsx', function (\App\Exports\PesananTokoExport $e) {
        return str_contains($e->view()->render(), 'INV-EKSPOR-1');
    });

    $this->actingAs(tokoAdmin(['view_pemesanantoko']));
    Livewire::test(OrderList::class)->call('unduhExcel')->assertForbidden();
});

it('riwayat mencatat pesanan dibuat, status berubah, dan akun dikirim beserta pelakunya', function () {
    $admin = tokoAdmin();
    $this->actingAs($admin);
    $order = tokoPesanan(['status' => 'paid']);

    $order->update(['status' => 'processing']);
    $order->items->first()->update(['delivery_status' => 'delivered']);

    $teks = RiwayatPesanan::untuk($order->fresh())->pluck('teks')->all();
    expect($teks)->toContain('Status Dibayar → Diproses')
        ->toContain('Akun Canva Pro dikirim ke pelanggan')
        ->and(OrderRiwayat::where('order_id', $order->id)->where('aksi', 'status')->first()->user_id)->toBe($admin->id);
});

it('pesanan lama tanpa jejak tetap punya riwayat dari data pesanan', function () {
    $order = tokoPesanan(['paid_at' => now()->subDay()]);
    OrderRiwayat::where('order_id', $order->id)->delete();

    $r = RiwayatPesanan::untuk($order->fresh());

    expect($r->pluck('teks')->all())->toContain('Pesanan dibuat')->toContain('Pembayaran diterima')
        ->and($r->every(fn ($x) => $x['dari_data']))->toBeTrue();
});

it('detail pesanan membuka jendela riwayat', function () {
    $this->actingAs(tokoAdmin());
    $order = tokoPesanan();

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->assertDontSee('Riwayat Pesanan')
        ->set('lihatRiwayat', true)
        ->assertSee('Riwayat Pesanan')
        ->assertSee('Dibuat admin');
});

it('perpanjangan satu klik mengisi pelanggan, produk, dan durasi dari item lama', function () {
    $this->actingAs(tokoAdmin());
    $produk = Product::create(['nama_akun' => 'ChatGPT Plus', 'harga_perbulan' => 35000]);
    $order = tokoPesanan([], ['duration_value' => 3, 'end_date' => today()->addDays(2)], $produk);
    $item = $order->items->first();

    Livewire::test(OrderForm::class, ['perpanjang' => (string) $item->id])
        ->assertSet('customer_id', $order->customer_id)
        ->assertSet('nama', 'Sari')
        ->assertSet('items.0.product_id', $produk->id)
        ->assertSet('items.0.duration_value', 3)
        ->assertSee('Perpanjangan ChatGPT Plus')
        ->assertSee($order->order_number);
});

it('pencatatan riwayat tidak pernah menggagalkan pesanan walau tabelnya belum ada', function () {
    \Illuminate\Support\Facades\Schema::drop('order_riwayat');
    RiwayatPesanan::lupakan();

    $order = tokoPesanan(['status' => 'paid']);
    $order->update(['status' => 'completed']);

    expect($order->fresh()->status)->toBe('completed')
        ->and(RiwayatPesanan::untuk($order->fresh())->pluck('teks')->all())->toContain('Pesanan dibuat');
});

it('akun yang sudah dibeli ulang pelanggan yang sama tidak muncul lagi di Segera Habis & Akun Habis', function () {
    $this->actingAs(tokoAdmin());
    $produk = Product::create(['nama_akun' => 'Netflix']);
    $lama = tokoPesanan([], ['product_name' => 'Netflix Lama', 'end_date' => today()->addDays(2)], $produk);
    $habis = tokoPesanan(['customer_id' => $lama->customer_id], ['product_name' => 'Netflix Habis', 'end_date' => today()->subDays(2)], $produk);
    $habis->forceFill(['created_at' => now()->subMonths(2)])->saveQuietly();
    $lama->forceFill(['created_at' => now()->subMonth()])->saveQuietly();

    // Belum ada pembelian ulang: keduanya muncul.
    Livewire::test(OrderList::class)->call('setTab', 'segera')->assertSee('Netflix Lama');

    // Pembelian ulang yang BELUM dibayar tidak dihitung.
    $ulang = tokoPesanan(['customer_id' => $lama->customer_id, 'status' => 'pending'], [], $produk);
    Livewire::test(OrderList::class)->call('setTab', 'segera')->assertSee('Netflix Lama');

    $ulang->update(['status' => 'paid']);
    Livewire::test(OrderList::class)->call('setTab', 'segera')->assertDontSee('Netflix Lama');
    Livewire::test(OrderList::class)->call('setTab', 'habis')->assertDontSee('Netflix Habis');
});

it('pesanan lewat tombol Perpanjang menautkan akun lama, walau produknya diganti', function () {
    $this->actingAs(tokoAdmin());
    $lama = tokoPesanan([], ['product_name' => 'Akun Lama X', 'end_date' => today()->addDays(3)]);
    $item = $lama->items->first();
    $lain = Product::create(['nama_akun' => 'Produk Pengganti', 'harga_perbulan' => 20000]);

    Livewire::test(OrderForm::class, ['perpanjang' => (string) $item->id])
        ->set('items.0.product_id', $lain->id)
        ->set('payment_method', 'qris_dinamis')
        ->call('save');

    $baru = OrderItem::where('product_id', $lain->id)->latest()->first();
    expect($item->fresh()->diperpanjang_oleh_item_id)->toBe((string) $baru->id);

    // Masih menunggu bayar → tetap diingatkan; setelah dibayar → hilang.
    Livewire::test(OrderList::class)->call('setTab', 'segera')->assertSee('Akun Lama X');
    $baru->order->update(['status' => 'paid']);
    Livewire::test(OrderList::class)->call('setTab', 'segera')->assertDontSee('Akun Lama X');
});

it('pesanan belum dibayar bisa diubah; total dihitung ulang dan tercatat di riwayat', function () {
    $this->actingAs(tokoAdmin());
    $a = Product::create(['nama_akun' => 'Produk A', 'harga_perbulan' => 30000]);
    $b = Product::create(['nama_akun' => 'Produk B', 'harga_perbulan' => 45000]);
    $order = tokoPesanan(['status' => 'draft', 'unique_code' => 123, 'total' => 30123, 'subtotal' => 30000], ['price' => 30000, 'subtotal' => 30000], $a);

    Livewire::test(\App\Livewire\Pages\Admin\Order\OrderEdit::class, ['order' => $order])
        ->assertSee('Total baru')
        ->set('baris.0.product_id', $b->id)
        ->set('baris.0.duration_value', 2)
        ->call('simpan')
        ->assertRedirect(route('admin.pesanantoko.detail', $order));

    $order->refresh();
    expect((int) $order->subtotal)->toBe(90000)
        ->and((int) $order->total)->toBe(90123)
        ->and($order->items()->count())->toBe(1)
        ->and($order->items()->first()->product_name)->toBe('Produk B')
        ->and(OrderRiwayat::where('order_id', $order->id)->where('aksi', 'diubah')->value('keterangan'))
        ->toContain('Rp 30.123 → Rp 90.123');
});

it('pesanan yang sudah dibayar, ber-bukti, ber-QRIS, atau berdiskon tidak bisa diubah', function (array $isian, string $potongan) {
    $this->actingAs(tokoAdmin());
    $order = tokoPesanan(array_merge(['status' => 'pending'], $isian));

    expect(\App\Support\EditPesanan::alasanTidakBisa($order))->toContain($potongan);

    Livewire::test(\App\Livewire\Pages\Admin\Order\OrderEdit::class, ['order' => $order])
        ->assertSee('tidak bisa diubah')
        ->call('simpan');
    expect($order->fresh()->items()->count())->toBe(1);
})->with([
    'dibayar' => [['status' => 'paid'], 'belum dibayar'],
    'bukti' => [['bukti_pembayaran' => 'bukti_pembayaran/x.jpg'], 'Bukti pembayaran'],
    'qris' => [['payment_method' => 'qris_dinamis', 'qris_content' => '000201'], 'QRIS sudah dibuat'],
    'diskon' => [['total_discount' => 5000], 'promo'],
]);

it('tombol Ubah hanya tampil di detail pesanan yang boleh diubah', function () {
    $this->actingAs(tokoAdmin());

    Livewire::test(OrderDetail::class, ['order' => tokoPesanan(['status' => 'draft'])])
        ->assertSee('/ubah', false);
    Livewire::test(OrderDetail::class, ['order' => tokoPesanan(['status' => 'completed'])])
        ->assertDontSee('/ubah', false);
});

it('menu samping menandai akun toko yang segera habis dan belum diingatkan', function () {
    $this->actingAs(tokoAdmin());
    tokoPesanan([], ['end_date' => today()->addDays(2)]);
    tokoPesanan([], ['end_date' => today()->addDays(2), 'ingat_perpanjang_at' => now()]);

    $html = \Livewire\Volt\Volt::test('layout.sidebar')->html();
    expect($html)->toContain('sidebar-badge-segera')
        ->toContain('1 akun segera habis dan belum diingatkan');
});

function pesananDuaItem(array $isian = [], array $itemB = []): Order
{
    $order = tokoPesanan(array_merge(['subtotal' => 80000, 'total' => 80000], $isian), ['product_name' => 'Akun A', 'price' => 50000, 'subtotal' => 50000]);
    OrderItem::create(array_merge([
        'order_id' => $order->id, 'product_id' => Product::create(['nama_akun' => 'Akun B'])->id, 'product_name' => 'Akun B',
        'duration_type' => 'bulan', 'duration_value' => 1, 'price' => 30000, 'quantity' => 1, 'subtotal' => 30000,
    ], $itemB));

    return $order->fresh('items');
}

it('batal item BELUM dibayar: item dihapus dan total pesanan berkurang', function () {
    $this->actingAs(tokoAdmin());
    $order = pesananDuaItem(['status' => 'draft']);
    $b = $order->items->firstWhere('product_name', 'Akun B');

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->call('bukaBatalItem', $b->id)
        ->assertSee('Pesanan belum dibayar')
        ->set('batalAlasan', 'Stok habis')
        ->call('simpanBatalItem');

    $order->refresh();
    expect(OrderItem::find($b->id))->toBeNull()
        ->and((int) $order->total)->toBe(50000)
        ->and((int) $order->subtotal)->toBe(50000)
        ->and(\App\Models\Spending::count())->toBe(0)
        ->and(OrderRiwayat::where('order_id', $order->id)->where('aksi', 'batal')->value('keterangan'))->toContain('total berkurang Rp 30.000');
});

it('batal item SUDAH dibayar: total tetap, refund dicatat sebagai pengeluaran & cash flow', function () {
    $this->actingAs(tokoAdmin());
    $order = pesananDuaItem(['status' => 'processing', 'paid_at' => now()], ['delivery_status' => 'pending']);
    $order->items->firstWhere('product_name', 'Akun A')->update(['delivery_status' => 'delivered']);
    $b = $order->items->firstWhere('product_name', 'Akun B');

    Livewire::test(OrderDetail::class, ['order' => $order->fresh()])
        ->call('bukaBatalItem', $b->id)
        ->assertSee('Pesanan sudah dibayar')
        ->set('batalAlasan', 'Akun tidak tersedia')
        ->set('batalRefund', 25000)
        ->call('simpanBatalItem');

    $b->refresh();
    $order->refresh();
    $spending = \App\Models\Spending::first();
    expect((int) $order->total)->toBe(80000)
        ->and($b->delivery_status)->toBe('cancelled')
        ->and((int) $b->refund_nominal)->toBe(25000)
        ->and($b->batal_setelah_kirim)->toBeFalse()
        ->and((int) $spending->nominal)->toBe(25000)
        ->and($spending->jenis_pengeluaran)->toBe('lainnya')
        ->and($spending->deskripsi)->toContain($order->order_number)
        ->and($spending->cashFlow()->where('type', 'expense')->where('amount', 25000)->exists())->toBeTrue()
        // Sisa item sudah terkirim → pesanan selesai, tidak menggantung.
        ->and($order->status)->toBe('completed');
});

it('refund tidak boleh melebihi subtotal item, dan item terakhir tidak bisa dibatalkan', function () {
    $this->actingAs(tokoAdmin());
    $order = pesananDuaItem(['status' => 'paid', 'paid_at' => now()]);
    $b = $order->items->firstWhere('product_name', 'Akun B');

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->call('bukaBatalItem', $b->id)
        ->set('batalAlasan', 'Salah input')
        ->set('batalRefund', 99999)
        ->call('simpanBatalItem')
        ->assertHasErrors('batalRefund');
    expect($b->fresh()->delivery_status)->not->toBe('cancelled');

});

it('satu-satunya item: sudah dibayar → refund seperti biasa; belum dibayar → pesanan ikut batal', function () {
    $this->actingAs(tokoAdmin());

    $lunas = tokoPesanan(['status' => 'completed', 'paid_at' => now()], ['delivery_status' => 'delivered']);
    $pesan = \App\Support\BatalItemPesanan::batalkan($lunas->items->first(), 'Akun bermasalah', 50000);
    $lunas->refresh();
    expect($pesan)->toContain('Refund Rp 50.000')
        ->and((int) $lunas->total)->toBe(50000)
        ->and($lunas->status)->toBe('completed')
        ->and($lunas->items->first()->delivery_status)->toBe('cancelled')
        ->and(\App\Models\Spending::where('nominal', 50000)->exists())->toBeTrue();

    $draft = tokoPesanan(['status' => 'draft']);
    \App\Support\BatalItemPesanan::batalkan($draft->items->first(), 'Pelanggan batal');
    expect($draft->fresh()->status)->toBe('cancelled')
        ->and($draft->items()->count())->toBe(1);

    // Pesanan dibayar yang SEMUA itemnya batal tidak dipaksa jadi "Selesai".
    $diproses = tokoPesanan(['status' => 'processing', 'paid_at' => now()]);
    \App\Support\BatalItemPesanan::batalkan($diproses->items->first(), 'Stok habis', 0);
    expect($diproses->fresh()->status)->toBe('processing');
});

it('batal item ditolak tanpa izin ubah pesanan', function () {
    $this->actingAs(tokoAdmin(['view_pemesanantoko']));
    $order = pesananDuaItem(['status' => 'paid']);

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->call('bukaBatalItem', $order->items->last()->id)
        ->set('batalAlasan', 'Coba')
        ->call('simpanBatalItem')
        ->assertForbidden();
});

it('item yang dibatalkan tidak bisa diproses dan tidak diingatkan perpanjangan', function () {
    $this->actingAs(tokoAdmin());
    $order = pesananDuaItem(['status' => 'paid'], ['end_date' => today()->addDays(2), 'delivery_status' => 'cancelled']);
    $b = $order->items->firstWhere('product_name', 'Akun B');

    Livewire::test(\App\Livewire\Pages\Admin\Order\ProcessOrder::class, ['id' => $b->id])
        ->assertRedirect(route('admin.pesanantoko.detail', $order));

    $segera = Livewire::test(OrderList::class)->call('setTab', 'segera')->viewData('segeraItems');
    expect($segera->pluck('id')->all())->not->toContain($b->id);
});

it('detail menampilkan tombol Perpanjang & Batalkan item hanya bila berlaku', function () {
    $this->actingAs(tokoAdmin());
    $order = pesananDuaItem(['status' => 'completed'], ['end_date' => today()->addDays(5), 'delivery_status' => 'delivered']);

    $html = Livewire::test(OrderDetail::class, ['order' => $order])->html();
    expect($html)->toContain('perpanjang='.$order->items->firstWhere('product_name', 'Akun B')->id)
        ->toContain("bukaBatalItem('");
});

it('item paket bundling menampilkan bagian harga paket yang dibayar, sama dengan batas refund', function () {
    $this->actingAs(tokoAdmin());
    $p = Product::create(['nama_akun' => 'DeepL Premium', 'harga_perbulan' => 50000]);
    $order = pesananDuaItem(['status' => 'completed'], ['product_id' => $p->id, 'product_name' => '[Combo Hemat] DeepL Premium', 'price' => 30000, 'subtotal' => 30000]);
    $item = $order->items->firstWhere('product_name', '[Combo Hemat] DeepL Premium');

    Livewire::test(OrderDetail::class, ['order' => $order])
        ->assertSee('Bagian harga paket Combo Hemat')
        ->assertSee('Rp 30.000')
        ->assertSee('hemat Rp 20.000')
        ->call('bukaBatalItem', $item->id)
        ->assertSet('batalRefund', 30000)
        ->assertSee('bagian harga paket Combo Hemat, bukan harga normal');
});

it('pesanan dibayar yang itemnya batal diberi label jujur, statusnya tetap (omzet tidak hilang)', function () {
    $this->actingAs(tokoAdmin());
    $semua = tokoPesanan(['status' => 'completed', 'order_number' => 'INV-SEMUA-BATAL'], ['delivery_status' => 'delivered']);
    \App\Support\BatalItemPesanan::batalkan($semua->items->first(), 'testing', 35000);
    $sebagian = pesananDuaItem(['status' => 'completed', 'order_number' => 'INV-SEBAGIAN'], ['delivery_status' => 'delivered']);
    \App\Support\BatalItemPesanan::batalkan($sebagian->items->last(), 'stok habis', 10000);

    expect($semua->fresh()->status)->toBe('completed');

    expect(Livewire::test(OrderList::class)->call('setTab', 'cancelled')->html())->toContain('Dibatalkan · refund');
    expect(Livewire::test(OrderList::class)->call('setTab', 'completed')->html())->toContain('1 item batal');

    Livewire::test(OrderDetail::class, ['order' => $semua->fresh()])
        ->assertSee('Semua item dibatalkan · refund Rp 35.000');
});

it('pesanan dibayar yang semua itemnya batal pindah ke tab Dibatalkan, keluar dari Selesai', function () {
    $this->actingAs(tokoAdmin());
    $semua = tokoPesanan(['status' => 'completed', 'order_number' => 'INV-PINDAH-BATAL'], ['delivery_status' => 'delivered']);
    \App\Support\BatalItemPesanan::batalkan($semua->items->first(), 'testing', 0);
    $sebagian = pesananDuaItem(['status' => 'completed', 'order_number' => 'INV-TETAP-SELESAI'], ['delivery_status' => 'delivered']);
    \App\Support\BatalItemPesanan::batalkan($sebagian->items->last(), 'stok habis', 0);
    $dibayar = tokoPesanan(['status' => 'paid', 'order_number' => 'INV-PAID-BATAL']);
    \App\Support\BatalItemPesanan::batalkan($dibayar->items->first(), 'testing', 0);

    $t = Livewire::test(OrderList::class);
    $angka = $t->viewData('tabCounts');
    expect($angka['cancelled'])->toBe(2)->and($angka['completed'])->toBe(1)->and($angka['neworder'])->toBe(0);

    $t->call('setTab', 'cancelled')->assertSee('INV-PINDAH-BATAL')->assertSee('INV-PAID-BATAL')->assertDontSee('INV-TETAP-SELESAI');
    Livewire::test(OrderList::class)->call('setTab', 'completed')->assertSee('INV-TETAP-SELESAI')->assertDontSee('INV-PINDAH-BATAL');

    // Status tersimpan tidak berubah → omzet tetap.
    expect($semua->fresh()->status)->toBe('completed')->and($dibayar->fresh()->status)->toBe('paid');
});
