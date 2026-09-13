<?php

use App\Livewire\Pages\Admin\BotTurnitin\PanelBotTurnitin;
use App\Mail\JasaHasilMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderUpload;
use App\Models\Product;
use App\Support\BotTurnitin;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Bot Turnitin: antrean Phoenix ⇄ skrip Tampermonkey di Chrome admin ⇄ submitin.id.
 * Yang paling dijaga: laporan TIDAK PERNAH tersimpan ke pesanan yang salah,
 * dan kegagalan apa pun berakhir di tangan admin, bukan hilang diam-diam.
 */
beforeEach(function () {
    BotTurnitin::lupakanSkema();
    Storage::fake('local');
    Mail::fake();
});

function pesananPlagiasi(array $orderIsian = [], array $addon = [], ?string $email = null): Order
{
    $order = Order::create(array_merge([
        'id' => Str::uuid(),
        'order_number' => 'INV-BOT-'.Str::upper(Str::random(6)),
        'customer_id' => Customer::create([
            'nama' => 'Budi Santoso',
            'no_hp' => '0812'.random_int(10000000, 99999999),
            'email' => $email ?? 'bot'.uniqid().'@contoh.test',
        ])->id,
        'subtotal' => 15000,
        'total' => 15000,
        'unique_code' => 0,
        'status' => 'paid',
        'payment_method' => 'qris_dinamis',
        'expired_at' => now()->addDay(),
    ], $orderIsian));

    OrderItem::create([
        'id' => Str::uuid(),
        'order_id' => $order->id,
        'product_id' => Product::create(['nama_akun' => 'Cek Plagiasi Turnitin', 'butuh_file' => true, 'pakai_exclude' => true])->id,
        'product_name' => 'Cek Plagiasi Turnitin',
        'duration_type' => 'bulan',
        'duration_value' => 1,
        'price' => 15000,
        'quantity' => 1,
        'subtotal' => 15000,
        'addons' => array_map(fn ($n) => ['nama' => $n, 'harga' => 0], $addon),
    ]);

    return $order->fresh('items');
}

function unggahanBot(Order $order, array $isian = []): OrderUpload
{
    $path = 'order-uploads/'.$order->id.'/masuk/skripsi.docx';
    Storage::disk('local')->put($path, 'isi dokumen customer');

    return OrderUpload::create(array_merge([
        'order_id' => $order->id,
        'jenis' => 'plagiasi',
        'path' => $path,
        'nama_asli' => 'Skripsi Budi Santoso.docx',
        'status' => 'menunggu',
        'exclude_bibliografi' => true,
        'exclude_kutipan' => true,
    ], $isian));
}

function tokenBot(): array
{
    return ['Authorization' => 'Bearer '.BotTurnitin::buatToken(), 'Accept' => 'application/json'];
}

function pdfPalsu(): UploadedFile
{
    return UploadedFile::fake()->createWithContent('laporan.pdf', "%PDF-1.4\n%%EOF");
}

/* ---------------------------------------------------------------- */

it('API menolak tanpa token yang benar', function () {
    BotTurnitin::buatToken();

    $this->getJson('/api/bot-turnitin/tugas')->assertStatus(401);
    $this->getJson('/api/bot-turnitin/tugas', ['Authorization' => 'Bearer salah'])->assertStatus(401);
});

it('mengambil unggahan plagiasi tertua dan menandainya dikerjakan bot', function () {
    $lama = unggahanBot(pesananPlagiasi());
    $this->travel(1)->minutes();
    unggahanBot(pesananPlagiasi());

    $r = $this->getJson('/api/bot-turnitin/tugas', tokenBot())->assertOk();

    expect($r->json('tugas.id'))->toBe($lama->id);
    expect($lama->fresh())
        ->status->toBe('diproses')
        ->dikerjakan_oleh->toBe('bot')
        ->bot_status->toBe(BotTurnitin::DIAMBIL);
});

it('data tugas tidak memuat nama, HP, maupun email customer', function () {
    $order = pesananPlagiasi([], [], 'rahasia@contoh.test');
    $up = unggahanBot($order, ['exclude_sumber_kecil' => true, 'ambang_sumber_kecil' => '10 kata',
        'exclude_kecocokan_kecil' => true, 'ambang_kecocokan_kecil' => 8]);

    $r = $this->getJson('/api/bot-turnitin/tugas', tokenBot())->assertOk();
    $json = json_encode($r->json());

    expect($json)->not->toContain('Budi')
        ->and($json)->not->toContain('rahasia@contoh.test')
        ->and($json)->not->toContain($order->customer->no_hp)
        ->and($r->json('tugas.nama_berkas'))->toBe(BotTurnitin::penanda($up).'-'.$order->order_number.'.docx')
        ->and($r->json('tugas.filter'))->toBe([
            'excl_biblio' => true,
            'excl_quotes' => true,
            'excl_source' => ['tipe' => 'words', 'nilai' => 10],
            'excl_match' => ['kata' => 8],
        ]);
});

it('hanya satu tugas aktif: selama masih berjalan, tugas yang sama dikembalikan', function () {
    $a = unggahanBot(pesananPlagiasi());
    $this->travel(1)->minutes();
    unggahanBot(pesananPlagiasi());
    $h = tokenBot();

    expect($this->getJson('/api/bot-turnitin/tugas', $h)->json('tugas.id'))->toBe($a->id)
        ->and($this->getJson('/api/bot-turnitin/tugas', $h)->json('tugas.id'))->toBe($a->id)
        ->and(OrderUpload::where('bot_status', BotTurnitin::DIAMBIL)->count())->toBe(1);
});

it('tidak menyentuh parafrase, cek AI, pesanan belum bayar, maupun unggahan yang dibatalkan', function () {
    unggahanBot(pesananPlagiasi(), ['jenis' => 'parafrase']);
    unggahanBot(pesananPlagiasi(), ['jenis' => 'ai']);
    unggahanBot(pesananPlagiasi(['status' => 'cancelled']));
    unggahanBot(pesananPlagiasi(), ['status' => 'dibatalkan']);

    $this->getJson('/api/bot-turnitin/tugas', tokenBot())->assertOk()->assertJsonPath('tugas', null);
});

it('dijeda atau kuota habis: tidak ada tugas yang diambil', function () {
    $up = unggahanBot(pesananPlagiasi());
    $h = tokenBot();

    BotTurnitin::setJeda(true);
    $this->getJson('/api/bot-turnitin/tugas', $h)->assertJsonPath('tugas', null)->assertJsonPath('dijeda', true);

    BotTurnitin::setJeda(false);
    BotTurnitin::tandaiKuotaHabis('Paket Standard habis.');
    $this->getJson('/api/bot-turnitin/tugas', $h)->assertJsonPath('tugas', null)->assertJsonPath('kuota_habis', true);

    expect($up->fresh()->status)->toBe('menunggu');

    BotTurnitin::kuotaSudahDiisi();
    expect($this->getJson('/api/bot-turnitin/tugas', $h)->json('tugas.id'))->toBe($up->id);
});

it('berkas customer hanya bisa diunduh selama dipegang bot', function () {
    $up = unggahanBot(pesananPlagiasi());
    $h = tokenBot();

    $this->get('/api/bot-turnitin/tugas/'.$up->id.'/berkas', $h)->assertStatus(409);

    $this->getJson('/api/bot-turnitin/tugas', $h);
    $this->get('/api/bot-turnitin/tugas/'.$up->id.'/berkas', $h)->assertOk();
});

it('alur lengkap: terkirim → hasil tersimpan, status selesai, email ke customer yang BENAR', function () {
    $order = pesananPlagiasi();
    $up = unggahanBot($order);
    $h = tokenBot();

    $this->getJson('/api/bot-turnitin/tugas', $h);
    $this->postJson('/api/bot-turnitin/tugas/'.$up->id.'/terkirim', ['kode' => 'SC-D0FEDA0427D4'], $h)->assertOk();

    $this->post('/api/bot-turnitin/tugas/'.$up->id.'/hasil', [
        'kode' => 'SC-D0FEDA0427D4', 'persen' => 3, 'berkas' => pdfPalsu(),
    ], $h)->assertOk()->assertJsonPath('status', 'selesai');

    $up->refresh();
    expect($up)
        ->status->toBe('selesai')
        ->bot_status->toBe(BotTurnitin::SELESAI)
        ->dikerjakan_oleh->toBe('bot')
        ->persentase->toBe(3);
    Storage::disk('local')->assertExists($up->hasil_path);

    Mail::assertSent(JasaHasilMail::class, fn ($m) => $m->hasTo($order->customer->email) && $m->upload->id === $up->id);
    // Kuota 1 dari 1 terpakai & selesai → pesanan jasa dituntaskan.
    expect($order->fresh()->status)->toBe('completed');
});

it('kode submitin yang tidak cocok DITOLAK dan tidak menyimpan apa pun', function () {
    $up = unggahanBot(pesananPlagiasi());
    $h = tokenBot();

    $this->getJson('/api/bot-turnitin/tugas', $h);
    $this->postJson('/api/bot-turnitin/tugas/'.$up->id.'/terkirim', ['kode' => 'SC-AAAAAAAAAAAA'], $h);

    $this->post('/api/bot-turnitin/tugas/'.$up->id.'/hasil', [
        'kode' => 'SC-BBBBBBBBBBBB', 'berkas' => pdfPalsu(),
    ], $h)->assertStatus(409);

    expect($up->fresh())->status->toBe('diproses')->hasil_path->toBeNull();
    Mail::assertNothingSent();
});

it('hasil untuk unggahan A tidak bisa masuk ke unggahan B walau kodenya milik A', function () {
    $a = unggahanBot(pesananPlagiasi());
    $this->travel(1)->minutes();
    $b = unggahanBot(pesananPlagiasi());
    $h = tokenBot();

    $this->getJson('/api/bot-turnitin/tugas', $h);
    $this->postJson('/api/bot-turnitin/tugas/'.$a->id.'/terkirim', ['kode' => 'SC-AAAAAAAAAAAA'], $h);

    // B belum pernah terkirim → ditolak.
    $this->post('/api/bot-turnitin/tugas/'.$b->id.'/hasil', ['kode' => 'SC-AAAAAAAAAAAA', 'berkas' => pdfPalsu()], $h)
        ->assertStatus(409);

    expect($b->fresh()->hasil_path)->toBeNull();
});

it('satu kode submitin tidak boleh tercatat untuk dua unggahan', function () {
    $a = unggahanBot(pesananPlagiasi(), ['bot_status' => BotTurnitin::SELESAI, 'bot_kode' => 'SC-AAAAAAAAAAAA', 'status' => 'selesai']);
    $b = unggahanBot(pesananPlagiasi());
    $h = tokenBot();

    $this->getJson('/api/bot-turnitin/tugas', $h);
    $this->postJson('/api/bot-turnitin/tugas/'.$b->id.'/terkirim', ['kode' => 'SC-AAAAAAAAAAAA'], $h)->assertStatus(409);

    expect($b->fresh()->bot_kode)->toBeNull();
});

it('berkas yang bukan PDF ditolak', function () {
    $up = unggahanBot(pesananPlagiasi());
    $h = tokenBot();
    $this->getJson('/api/bot-turnitin/tugas', $h);
    $this->postJson('/api/bot-turnitin/tugas/'.$up->id.'/terkirim', ['kode' => 'SC-AAAAAAAAAAAA'], $h);

    $this->post('/api/bot-turnitin/tugas/'.$up->id.'/hasil', [
        'kode' => 'SC-AAAAAAAAAAAA', 'berkas' => UploadedFile::fake()->createWithContent('x.pdf', '<html>login</html>'),
    ], $h)->assertStatus(409);

    expect($up->fresh()->hasil_path)->toBeNull();
});

it('admin sudah mengunggah hasil manual: hasil bot yang datang belakangan ditolak', function () {
    $up = unggahanBot(pesananPlagiasi());
    $h = tokenBot();
    $this->getJson('/api/bot-turnitin/tugas', $h);
    $this->postJson('/api/bot-turnitin/tugas/'.$up->id.'/terkirim', ['kode' => 'SC-AAAAAAAAAAAA'], $h);

    $up->update(['status' => 'selesai', 'hasil_path' => 'manual.pdf', 'dikerjakan_oleh' => 'admin']);

    $this->post('/api/bot-turnitin/tugas/'.$up->id.'/hasil', ['kode' => 'SC-AAAAAAAAAAAA', 'berkas' => pdfPalsu()], $h)
        ->assertStatus(409);

    expect($up->fresh())->hasil_path->toBe('manual.pdf')->dikerjakan_oleh->toBe('admin');
});

it('pesanan yang juga menuntut hasil AI tidak ditandai selesai dan tidak mengirim email', function () {
    $order = pesananPlagiasi([], ['Cek AI']);
    $up = unggahanBot($order);
    $h = tokenBot();
    $this->getJson('/api/bot-turnitin/tugas', $h);
    $this->postJson('/api/bot-turnitin/tugas/'.$up->id.'/terkirim', ['kode' => 'SC-AAAAAAAAAAAA'], $h);

    $this->post('/api/bot-turnitin/tugas/'.$up->id.'/hasil', ['kode' => 'SC-AAAAAAAAAAAA', 'berkas' => pdfPalsu()], $h)
        ->assertOk()->assertJsonPath('status', BotTurnitin::PERLU_DILENGKAPI);

    expect($up->fresh())->status->toBe('diproses')->bot_status->toBe(BotTurnitin::PERLU_DILENGKAPI);
    Mail::assertNothingSent();
    expect(BotTurnitin::perluAdmin()->pluck('id')->all())->toBe([$up->id]);
});

it('gagal SEBELUM terkirim: kembali ke antrean admin dan muncul di kartu dashboard', function () {
    $up = unggahanBot(pesananPlagiasi());
    $h = tokenBot();
    $this->getJson('/api/bot-turnitin/tugas', $h);

    $this->postJson('/api/bot-turnitin/tugas/'.$up->id.'/gagal', ['pesan' => 'Form submitin tidak termuat'], $h)->assertOk();

    expect($up->fresh())
        ->status->toBe('menunggu')
        ->dikerjakan_oleh->toBeNull()
        ->bot_status->toBe(BotTurnitin::GAGAL);
    expect(BotTurnitin::perluAdmin()->pluck('id')->all())->toBe([$up->id]);

    // Bot tidak mengambilnya lagi dengan sendirinya.
    $this->getJson('/api/bot-turnitin/tugas', $h)->assertJsonPath('tugas', null);
});

it('gagal SESUDAH terkirim: tetap diproses, kode submitin disimpan untuk dicek admin', function () {
    $up = unggahanBot(pesananPlagiasi());
    $h = tokenBot();
    $this->getJson('/api/bot-turnitin/tugas', $h);
    $this->postJson('/api/bot-turnitin/tugas/'.$up->id.'/terkirim', ['kode' => 'SC-AAAAAAAAAAAA'], $h);

    $this->postJson('/api/bot-turnitin/tugas/'.$up->id.'/gagal', ['pesan' => 'Laporan belum keluar setelah 60 menit.'], $h);

    expect($up->fresh())->status->toBe('diproses')->bot_kode->toBe('SC-AAAAAAAAAAAA')->bot_status->toBe(BotTurnitin::GAGAL);
    // Coba lagi pakai bot TIDAK boleh: bisa memakai kuota dua kali.
    expect(BotTurnitin::cobaLagi($up->fresh()))->toBeFalse();
});

it('kuota habis saat mengisi form: unggahan kembali ke antrean apa adanya, kartu kuota menyala', function () {
    $up = unggahanBot(pesananPlagiasi());
    $h = tokenBot();
    $this->getJson('/api/bot-turnitin/tugas', $h);

    $this->postJson('/api/bot-turnitin/tugas/'.$up->id.'/gagal', ['pesan' => 'Paket Standard habis.', 'kuota_habis' => true], $h);

    expect($up->fresh())->status->toBe('menunggu')->bot_status->toBeNull();
    expect(BotTurnitin::kuotaHabis())->not->toBeNull()
        ->and(BotTurnitin::perluAdmin())->toBeEmpty();
});

it('tugas tanpa kabar lebih dari batas dianggap macet dan masuk kartu dashboard', function () {
    $up = unggahanBot(pesananPlagiasi());
    $h = tokenBot();
    $this->getJson('/api/bot-turnitin/tugas', $h);

    expect(BotTurnitin::perluAdmin())->toBeEmpty();

    $this->travel(BotTurnitin::MACET_MENIT + 1)->minutes();
    expect(BotTurnitin::perluAdmin()->pluck('id')->all())->toBe([$up->id]);

    // Detak "masih menunggu" dari bot menyegarkannya lagi.
    $this->postJson('/api/bot-turnitin/detak', ['tugas' => $up->id], $h)->assertOk();
    expect(BotTurnitin::perluAdmin())->toBeEmpty();
});

it('coba lagi & ambil alih dari kartu dashboard', function () {
    $up = unggahanBot(pesananPlagiasi());
    $h = tokenBot();
    $this->getJson('/api/bot-turnitin/tugas', $h);
    $this->postJson('/api/bot-turnitin/tugas/'.$up->id.'/gagal', ['pesan' => 'x'], $h);

    expect(BotTurnitin::cobaLagi($up->fresh()))->toBeTrue();
    expect($up->fresh())->bot_status->toBeNull()->status->toBe('menunggu');

    $this->getJson('/api/bot-turnitin/tugas', $h);
    BotTurnitin::ambilAlih($up->fresh());
    expect($up->fresh())->bot_status->toBe(BotTurnitin::MANUAL)->status->toBe('menunggu');
    $this->getJson('/api/bot-turnitin/tugas', $h)->assertJsonPath('tugas', null);
});

it('panel dashboard menampilkan kartu kuota habis & perlu manual', function () {
    $role = \App\Models\Role::create(['name' => 'uji-bot-'.uniqid(), 'description' => 'Peran uji bot']);
    foreach (['view_pemesanantoko', 'edit_pemesanantoko'] as $nama) {
        $role->permissions()->attach(\App\Models\Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'pemesanan', 'description' => 'uji']
        )->id);
    }
    $admin = \App\Models\User::factory()->create(['role_id' => $role->id]);

    $up = unggahanBot(pesananPlagiasi());
    BotTurnitin::buatToken();
    BotTurnitin::ambilTugas();
    BotTurnitin::gagal($up, 'Form submitin tidak termuat');
    BotTurnitin::tandaiKuotaHabis('Paket Standard habis (s/d 10/10/2026).');

    Livewire\Livewire::actingAs($admin->fresh())
        ->test(PanelBotTurnitin::class)
        ->assertSee('Kuota paket Standard di submitin.id habis')
        ->assertSee('Paket Standard habis (s/d 10/10/2026).')
        ->assertSee('1 pengecekan perlu dikerjakan admin')
        ->assertSee($up->order->order_number)
        ->assertSee('Form submitin tidak termuat')
        ->call('kuotaSudahDiisi')
        ->assertDontSee('Kuota paket Standard di submitin.id habis');
});

it('skrip bot tersedia di public dan tidak memuat token atau rahasia', function () {
    $skrip = file_get_contents(public_path('bot/phoenix-turnitin.user.js'));

    expect($skrip)->toContain('// @match        https://submitin.id/*')
        ->and($skrip)->toContain("'/api/bot-turnitin'")
        // Pengaman uang: hanya mengirim dengan paket Standard, tidak pernah QRIS/Saldo.
        ->and($skrip)->toContain("salah.push('yang terpilih bukan paket Standard')")
        ->and($skrip)->toContain("$('#payMethodType').value !== 'package'")
        ->and($skrip)->not->toMatch('/pdbot_[A-Za-z0-9]{40}/');
});

it('customer bisa memilih Exclude Matches, dan bot menerjemahkannya ke kolom submitin', function () {
    $order = pesananPlagiasi(['share_token' => Str::upper(Str::random(10))]);

    Livewire\Livewire::test(\App\Livewire\Pages\Public\ShopPage\JasaCekPage::class, ['token' => $order->share_token])
        ->set('dokumen', UploadedFile::fake()->create('bab3.pdf', 20, 'application/pdf'))
        ->set('exclude_kecocokan_kecil', true)
        ->assertSet('ambang_kecocokan', 10)
        ->set('ambang_kecocokan', 12)
        ->call('uploadDokumen')
        ->assertHasNoErrors();

    $up = $order->uploads()->firstOrFail();
    expect($up)->exclude_kecocokan_kecil->toBeTrue()->ambang_kecocokan_kecil->toBe(12)
        ->and($up->daftarExclude())->toContain('Matches < 12 kata')
        ->and(BotTurnitin::filter($up)['excl_match'])->toBe(['kata' => 12]);
});

it('selama SQL deploy belum dijalankan, unggahan customer TETAP tersimpan', function () {
    // Kolom bot dianggap belum ada: halaman customer tidak boleh ikut galat.
    BotTurnitin::lupakanSkema(false);
    $order = pesananPlagiasi(['share_token' => Str::upper(Str::random(10))]);

    Livewire\Livewire::test(\App\Livewire\Pages\Public\ShopPage\JasaCekPage::class, ['token' => $order->share_token])
        ->set('dokumen', UploadedFile::fake()->create('bab3.pdf', 20, 'application/pdf'))
        ->set('exclude_kecocokan_kecil', true)
        ->call('uploadDokumen')
        ->assertHasNoErrors();

    // Kolom exclude matches dilewati, sisanya tersimpan seperti biasa.
    expect($order->uploads()->count())->toBe(1)
        ->and($order->uploads()->first()->exclude_kecocokan_kecil)->toBeFalse();

    // API bot menjawab 503 yang jelas, bukan galat SQL.
    BotTurnitin::buatToken();
    $this->getJson('/api/bot-turnitin/tugas', ['Authorization' => 'Bearer x'])->assertStatus(503);
});
