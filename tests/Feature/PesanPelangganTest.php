<?php

use App\Livewire\Pages\Admin\Message\CustomerMessageDetail;
use App\Livewire\Pages\Admin\Message\CustomerMessageList;
use App\Models\CustomerMessage;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Layar Pesan Pelanggan (helpdesk): tab antrean, saringan, aksi massal,
 * halaman detail, dan unduhan.
 */
function adminPesan(array $izin = ['view_customer_message', 'delete_customer_message']): \App\Models\User
{
    $peran = \App\Models\Role::create(['name' => 'uji-pp-'.Str::random(5), 'description' => 'uji']);
    foreach ($izin as $nama) {
        $p = \App\Models\Permission::firstOrCreate(['name' => $nama], ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']);
        $peran->permissions()->attach($p->id);
    }

    return \App\Models\User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);
}

function pesan(array $isian = []): CustomerMessage
{
    $dibuat = $isian['created_at'] ?? null;
    unset($isian['created_at']);

    $p = CustomerMessage::create(array_merge([
        'name' => 'Pengirim '.Str::random(4),
        'email' => 'uji'.Str::random(4).'@contoh.com',
        'no_telp' => '081200000000',
        'status' => 'open',
        'priority' => 'low',
        'message' => 'Halo, saya mau tanya soal pesanan saya.',
    ], $isian));

    if ($dibuat) {
        // created_at tidak fillable — ditulis terpisah supaya ujinya jujur.
        $p->forceFill(['created_at' => $dibuat])->saveQuietly();
        $p->refresh();
    }

    return $p;
}

it('tab antrean menghitung dan menyaring sesuai keadaan tiket', function () {
    $this->actingAs(adminPesan());
    pesan(['name' => 'Belum Dibaca Satu']);
    $dibaca = pesan(['name' => 'Sedang Diproses', 'status' => 'in_progress']);
    $dibaca->markAsRead();
    $selesai = pesan(['name' => 'Sudah Selesai', 'status' => 'resolved']);
    $selesai->markAsRead();

    $t = Livewire::test(CustomerMessageList::class);
    expect($t->viewData('tabCounts'))->toMatchArray(['baru' => 1, 'berjalan' => 2, 'selesai' => 1, 'semua' => 3]);

    $t->assertSet('tab', 'baru')->assertSee('Belum Dibaca Satu')->assertDontSee('Sudah Selesai');
    $t->call('setTab', 'selesai')->assertSee('Sudah Selesai')->assertDontSee('Sedang Diproses');
    $t->call('setTab', 'berjalan')->assertSee('Sedang Diproses')->assertDontSee('Sudah Selesai');
    $t->call('setTab', 'ngawur')->assertSet('tab', 'baru');
});

it('pencarian menjangkau tiket, nama, email, isi pesan, dan nomor', function () {
    $this->actingAs(adminPesan());
    $a = pesan(['name' => 'Budi Santoso', 'email' => 'budi@contoh.com', 'no_telp' => '089511223344', 'message' => 'Akun Canva belum masuk.']);
    pesan(['name' => 'Sita Dewi', 'email' => 'sita@contoh.com', 'no_telp' => '081200001111', 'message' => 'Paketnya sudah aktif, terima kasih.']);

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua');
    // Kartu ringkasan menyebut tiket terlama apa pun saringannya, jadi yang
    // diperiksa isi daftarnya, bukan seluruh halaman.
    $daftar = fn () => $t->viewData('messages')->pluck('name')->all();

    expect($daftar($t->set('search', 'Budi')))->toBe(['Budi Santoso'])
        ->and($daftar($t->set('search', 'sita@contoh.com')))->toBe(['Sita Dewi'])
        ->and($daftar($t->set('search', 'Canva')))->toBe(['Budi Santoso'])
        ->and($daftar($t->set('search', $a->ticket)))->toBe(['Budi Santoso'])
        // Nomor apa pun formatnya.
        ->and($daftar($t->set('search', '+6289511223344')))->toBe(['Budi Santoso'])
        ->and($daftar($t->set('search', '0895-1122-3344')))->toBe(['Budi Santoso'])
        // Angka nyasar di kata/kode tiket tidak boleh menyeret nomor orang lain.
        ->and($daftar($t->set('search', 'TKT-1A2B-3C4D')))->toBe([])
        ->and($daftar($t->set('search', 'tidak ada yang cocok')))->toBe([]);
});

it('menyaring menurut status, prioritas, dan rentang tanggal', function () {
    $this->actingAs(adminPesan());
    pesan(['name' => 'Tiket Mendesak', 'priority' => 'urgent', 'status' => 'open']);
    pesan(['name' => 'Tiket Santai', 'priority' => 'low', 'status' => 'pending']);

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua');
    $daftar = fn () => $t->viewData('messages')->pluck('name')->all();

    expect($daftar($t->set('fPrioritas', 'urgent')))->toBe(['Tiket Mendesak'])
        ->and($daftar($t->set('fPrioritas', '')->set('fStatus', 'pending')))->toBe(['Tiket Santai'])
        ->and($daftar($t->set('fStatus', '')->set('fDari', now()->addDay()->toDateString())))->toBe([]);

    $t->call('resetFilters')->assertSet('fDari', '');
    expect($daftar($t))->toHaveCount(2);
});

it('urutan prioritas menaikkan yang mendesak, urutan lama menaikkan yang tertua', function () {
    $this->actingAs(adminPesan());
    $lama = pesan(['name' => 'Paling Lama', 'priority' => 'low', 'created_at' => now()->subWeek()]);
    $mendesak = pesan(['name' => 'Paling Mendesak', 'priority' => 'urgent']);

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua');
    expect($t->set('urut', 'prioritas')->viewData('messages')->first()->id)->toBe($mendesak->id);
    expect($t->set('urut', 'lama')->viewData('messages')->first()->id)->toBe($lama->id);
    $t->set('urut', 'ngawur')->assertSet('urut', 'baru');
});

it('status & prioritas bisa diubah dari daftar, dan nilai ngawur ditolak', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    $t = Livewire::test(CustomerMessageList::class);
    $t->call('updateStatus', $p->id, 'in_progress');
    expect($p->fresh()->status)->toBe('in_progress');

    $t->call('updatePriority', $p->id, 'urgent');
    expect($p->fresh()->priority)->toBe('urgent');

    // Nilai di luar daftar tidak pernah tersimpan.
    $t->call('updateStatus', $p->id, 'ngawur');
    $t->call('updatePriority', $p->id, 'ngawur');
    expect($p->fresh()->status)->toBe('in_progress')
        ->and($p->fresh()->priority)->toBe('urgent');
});

it('tandai dibaca bekerja dari daftar tanpa membuka halamannya', function () {
    $this->actingAs(adminPesan());
    $p = pesan();
    expect($p->belumDibaca())->toBeTrue();

    Livewire::test(CustomerMessageList::class)->call('tandaiDibaca', $p->id);
    expect($p->fresh()->belumDibaca())->toBeFalse();
});

it('pesan yang belum dibaca tidak bisa dihapus', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    Livewire::test(CustomerMessageList::class)
        ->call('delete', $p->id)
        ->assertDispatched('CustomerMessage-deleteError');

    expect(CustomerMessage::find($p->id))->not->toBeNull();

    // Sesudah dibaca, baru boleh.
    $p->markAsRead();
    Livewire::test(CustomerMessageList::class)->call('delete', $p->id)->assertDispatched('CustomerMessage-deleted');
    expect(CustomerMessage::find($p->id))->toBeNull();
});

it('hapus menolak pengguna tanpa izin hapus', function () {
    $p = pesan();
    $p->markAsRead();
    $this->actingAs(adminPesan(['view_customer_message']));

    Livewire::test(CustomerMessageList::class)->call('delete', $p->id)->assertDispatched('CustomerMessage-deleteError');
    expect(CustomerMessage::find($p->id))->not->toBeNull();
});

it('aksi massal menandai dibaca, mengubah status, dan menghapus', function () {
    $this->actingAs(adminPesan());
    $a = pesan();
    $b = pesan();
    $ids = [(string) $a->id, (string) $b->id];

    Livewire::test(CustomerMessageList::class)->set('pilih', $ids)->call('tandaiDibacaTerpilih')->assertSet('pilih', []);
    expect([$a->fresh()->belumDibaca(), $b->fresh()->belumDibaca()])->toBe([false, false]);

    Livewire::test(CustomerMessageList::class)->set('pilih', $ids)->call('statusTerpilih', 'resolved');
    expect([$a->fresh()->status, $b->fresh()->status])->toBe(['resolved', 'resolved']);

    Livewire::test(CustomerMessageList::class)->set('pilih', $ids)->call('hapusTerpilih');
    expect(CustomerMessage::count())->toBe(0);
});

it('hapus massal melewati pesan yang belum dibaca', function () {
    $this->actingAs(adminPesan());
    $dibaca = pesan();
    $dibaca->markAsRead();
    $belum = pesan();

    Livewire::test(CustomerMessageList::class)
        ->set('pilih', [(string) $dibaca->id, (string) $belum->id])
        ->call('hapusTerpilih');

    expect(CustomerMessage::find($dibaca->id))->toBeNull()
        ->and(CustomerMessage::find($belum->id))->not->toBeNull();
});

it('pilih semua di halaman mencentang lalu melepas kembali', function () {
    $this->actingAs(adminPesan());
    $a = pesan();
    $b = pesan();
    $ids = [(string) $a->id, (string) $b->id];

    Livewire::test(CustomerMessageList::class)
        ->call('pilihHalaman', $ids)->assertSet('pilih', $ids)
        ->call('pilihHalaman', $ids)->assertSet('pilih', []);
});

it('chip saringan menampilkan yang aktif dan bisa dilepas satu-satu', function () {
    $this->actingAs(adminPesan());

    $t = Livewire::test(CustomerMessageList::class)->set('fStatus', 'open')->set('fPrioritas', 'urgent');
    expect(collect($t->instance()->chipSaring)->pluck('nama')->all())->toBe(['fStatus', 'fPrioritas']);

    $t->call('lepasSaring', 'fStatus')->assertSet('fStatus', '')->assertSet('fPrioritas', 'urgent');
    // Nama properti yang tidak dikenal diabaikan, bukan menimpa apa pun.
    $t->call('lepasSaring', 'perPage')->assertSet('perPage', 12);
});

it('unduhan mengikuti centang bila ada, dan butuh izin lihat', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-20 09:00:00');
    \Maatwebsite\Excel\Facades\Excel::fake();
    $this->actingAs(adminPesan());

    $dipilih = pesan(['name' => 'Dicentang']);
    pesan(['name' => 'Tidak Dicentang']);

    Livewire::test(CustomerMessageList::class)->set('pilih', [(string) $dipilih->id])->call('unduhExcel');

    \Maatwebsite\Excel\Facades\Excel::assertDownloaded('pesan-pelanggan-20260920-090000.xlsx', function (\App\Exports\PesanPelangganExport $ekspor) {
        $nama = $ekspor->view()->getData()['pesan']->pluck('name');

        return $nama->contains('Dicentang') && ! $nama->contains('Tidak Dicentang');
    });

    Livewire::test(CustomerMessageList::class)->call('unduhPdf')->assertFileDownloaded();

    $this->actingAs(\App\Models\User::factory()->create([
        'role_id' => \App\Models\Role::create(['name' => 'uji-pp-nihil-'.Str::random(4), 'description' => 'uji'])->id,
        'status' => 'active',
    ]));
    Livewire::test(CustomerMessageList::class)->call('unduhExcel')->assertForbidden();
    Livewire::test(CustomerMessageList::class)->call('unduhPdf')->assertForbidden();

    \Illuminate\Support\Carbon::setTestNow();
});

// ===================== Halaman detail =====================

it('membuka detail menandai pesan sudah dibaca dan memberi tahu sidebar', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->assertDispatched('sidebar-badge-updated')
        ->assertSee($p->ticket)
        ->assertSee('Balas WhatsApp');

    expect($p->fresh()->belumDibaca())->toBeFalse();
});

it('status & prioritas di detail langsung tersimpan', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->set('status', 'resolved')
        ->set('priority', 'high');

    expect($p->fresh()->status)->toBe('resolved')
        ->and($p->fresh()->priority)->toBe('high');
});

// ===================== Pembantu model =====================

it('umur tiket berhenti dihitung begitu pesannya dibaca', function () {
    $p = pesan(['created_at' => now()->subHours(10)]);
    expect($p->menungguJam())->toBe(10);

    $p->markAsRead();
    // Sesudah dibaca umurnya beku; angka yang terus tumbuh cuma kecemasan palsu.
    expect($p->fresh()->menungguJam())->toBe(10);
});

it('tautan balasan dinormalkan dan kosong bila kontaknya tidak ada', function () {
    $p = pesan(['no_telp' => '081234567890', 'email' => 'halo@contoh.com']);

    expect($p->tautanWa())->toBe('https://wa.me/6281234567890')
        ->and($p->tautanEmail())->toContain('mailto:halo@contoh.com')
        ->and($p->tautanEmail())->toContain(rawurlencode($p->ticket));

    // Kolom email & no_telp NOT NULL di skema, jadi kontak kosong = string kosong.
    $tanpa = pesan(['no_telp' => '', 'email' => '']);
    expect($tanpa->tautanWa())->toBeNull()->and($tanpa->tautanEmail())->toBeNull();
});

it('label status & prioritas memakai bahasa Indonesia', function () {
    expect(pesan(['status' => 'in_progress'])->tampilanStatus()[0])->toBe('Diproses')
        ->and(pesan(['status' => 'closed'])->tampilanStatus()[0])->toBe('Ditutup')
        ->and(pesan(['priority' => 'urgent'])->tampilanPrioritas()[0])->toBe('Mendesak')
        ->and(pesan(['priority' => 'medium'])->tampilanPrioritas()[0])->toBe('Sedang');
});
