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
function adminPesan(array $izin = ['view_customer_message', 'edit_customer_message', 'delete_customer_message', 'view_all_customer_message']): \App\Models\User
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

it('umur tiket dihitung dengan jam kerja dan berhenti saat dibaca', function () {
    // Senin 09.00; toko buka 08.00-21.00.
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 09:00:00');

    // Masuk 20.00 kemarin: 1 jam sebelum tutup + 1 jam pagi ini = 2 jam kerja,
    // bukan 13 jam kalender.
    $p = pesan(['created_at' => \Illuminate\Support\Carbon::parse('2026-09-20 20:00:00')]);
    expect($p->menungguJam())->toBe(2);

    $p->markAsRead();
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 15:00:00');
    // Sesudah dibaca umurnya beku; angka yang terus tumbuh cuma kecemasan palsu.
    expect($p->fresh()->menungguJam())->toBe(2);

    \Illuminate\Support\Carbon::setTestNow();
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

// ===================== Izin menangani tiket =====================

it('izin lihat saja tidak boleh mengubah status tiket', function () {
    $p = pesan();
    $this->actingAs(adminPesan(['view_customer_message']));

    Livewire::test(CustomerMessageList::class)
        ->call('updateStatus', $p->id, 'resolved')
        ->assertDispatched('swal-error');

    expect($p->fresh()->status)->toBe('open');

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])->set('status', 'resolved');
    expect($p->fresh()->status)->toBe('open');
});

it('status ngawur dari halaman detail ditolak, bukan disimpan', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    // Kolomnya ENUM di MySQL: satu nilai asing dari klien = galat 500 di server.
    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->set('status', 'ngawur')->assertSet('status', 'open')
        ->set('priority', 'ngawur')->assertSet('priority', 'low')
        ->set('kategori', 'ngawur')->assertSet('kategori', '')
        ->set('petugas', '99999')->assertSet('petugas', '');

    expect($p->fresh()->only(['status', 'priority', 'kategori', 'assigned_to']))
        ->toBe(['status' => 'open', 'priority' => 'low', 'kategori' => null, 'assigned_to' => null]);
});

// ===================== Balasan, catatan, linimasa =====================

it('balasan tercatat di linimasa dan menandai tiket sudah dibalas', function () {
    $petugas = adminPesan();
    $this->actingAs($petugas);
    $p = pesan();

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->set('balasanIsi', 'Halo, akunnya sudah kami kirim ke email ya.')
        ->set('balasanKanal', 'whatsapp')
        ->set('balasanSelesai', true)
        ->call('simpanBalasan')
        ->assertSet('balasanIsi', '');

    $p->refresh();
    expect($p->sudahDibalas())->toBeTrue()
        ->and($p->replied_by)->toBe($petugas->id)
        ->and($p->status)->toBe('resolved');

    $log = $p->logs()->where('jenis', 'balasan')->first();
    expect($log->isi)->toContain('akunnya sudah kami kirim')
        ->and($log->kanal)->toBe('whatsapp')
        ->and($log->pelaku())->toBe($petugas->name);
});

it('balasan kedua tidak menggeser waktu balasan pertama', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    $t = Livewire::test(CustomerMessageDetail::class, ['message' => $p]);
    $t->set('balasanIsi', 'Balasan pertama.')->call('simpanBalasan');
    $pertama = $p->fresh()->replied_at;

    \Illuminate\Support\Carbon::setTestNow(now()->addHours(3));
    $t->set('balasanIsi', 'Balasan susulan.')->call('simpanBalasan');
    \Illuminate\Support\Carbon::setTestNow();

    // replied_at = waktu tanggap, jadi yang dipegang balasan PERTAMA.
    expect($p->fresh()->replied_at->eq($pertama))->toBeTrue()
        ->and($p->logs()->where('jenis', 'balasan')->count())->toBe(2);
});

it('balasan kosong ditolak dan tidak menandai tiket dibalas', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->set('balasanIsi', ' ')
        ->call('simpanBalasan')
        ->assertHasErrors('balasanIsi');

    expect($p->fresh()->sudahDibalas())->toBeFalse();
});

it('catatan internal tersimpan tanpa dianggap balasan', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->set('catatanIsi', 'Sudah ditelepon, minta ditunda sampai besok.')
        ->call('simpanCatatan')
        ->assertSet('catatanIsi', '');

    expect($p->fresh()->sudahDibalas())->toBeFalse()
        ->and($p->logs()->where('jenis', 'catatan')->first()->isi)->toContain('ditelepon');
});

it('perubahan dari daftar ikut tercatat di linimasa', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    $t = Livewire::test(CustomerMessageList::class);
    $t->call('tandaiDibaca', $p->id);
    $t->call('updateStatus', $p->id, 'in_progress');
    $t->call('updatePriority', $p->id, 'urgent');
    $t->call('ambilTiket', $p->id);

    expect($p->logs()->pluck('jenis')->all())->toBe(['dibaca', 'status', 'prioritas', 'tugas'])
        ->and($p->logs()->where('jenis', 'status')->first()->isi)->toBe('Terbuka → Diproses');
});

// ===================== Template balasan =====================

it('template mengisi kotak balasan dengan nama & nomor tiket', function () {
    $this->actingAs(adminPesan());
    $p = pesan(['name' => 'Rina']);
    $template = \App\Models\CustomerMessageTemplate::urut()->first();

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->call('pakaiTemplate', $template->id)
        ->assertSet('balasanIsi', str_replace(['{nama}', '{tiket}'], ['Rina', $p->ticket], $template->isi));
});

it('template bisa ditambah dan dihapus', function () {
    $this->actingAs(adminPesan());
    $p = pesan();
    $awal = \App\Models\CustomerMessageTemplate::count();

    $t = Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->set('templateNama', 'Minta bukti transfer')
        ->set('templateIsi', 'Halo {nama}, boleh dikirim bukti transfernya?')
        ->call('simpanTemplate')
        ->assertSet('templateNama', '');

    expect(\App\Models\CustomerMessageTemplate::count())->toBe($awal + 1);

    $baru = \App\Models\CustomerMessageTemplate::where('nama', 'Minta bukti transfer')->first();
    $t->call('hapusTemplate', $baru->id);
    expect(\App\Models\CustomerMessageTemplate::count())->toBe($awal);
});

// ===================== Arsip & spam =====================

it('hapus memindahkan ke arsip, bukan menghilangkan selamanya', function () {
    $this->actingAs(adminPesan());
    $p = pesan();
    $p->markAsRead();

    Livewire::test(CustomerMessageList::class)->call('delete', $p->id)->assertDispatched('CustomerMessage-deleted');

    expect(CustomerMessage::find($p->id))->toBeNull()
        ->and(CustomerMessage::onlyTrashed()->find($p->id))->not->toBeNull();

    // Tab arsip menampilkannya, dan bisa dikembalikan.
    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'arsip');
    expect($t->viewData('messages')->pluck('id')->all())->toBe([$p->id]);

    $t->call('pulihkan', $p->id);
    expect(CustomerMessage::find($p->id))->not->toBeNull();
});

it('hapus permanen hanya menyentuh yang sudah diarsipkan', function () {
    $this->actingAs(adminPesan());
    $hidup = pesan();
    $hidup->markAsRead();
    $diarsip = pesan();
    $diarsip->markAsRead();
    $diarsip->delete();

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'arsip');
    $t->call('hapusPermanen', $hidup->id);
    expect(CustomerMessage::find($hidup->id))->not->toBeNull();

    $t->call('hapusPermanen', $diarsip->id);
    expect(CustomerMessage::withTrashed()->find($diarsip->id))->toBeNull();
});

it('spam masuk arsip dan berhenti dihitung sebagai pesan baru', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    $t = Livewire::test(CustomerMessageList::class);
    expect($t->viewData('tabCounts')['baru'])->toBe(1);

    $t->call('tandaiSpam', $p->id);

    $p = CustomerMessage::withTrashed()->find($p->id);
    expect($p->is_spam)->toBeTrue()
        ->and($p->trashed())->toBeTrue()
        ->and($p->status)->toBe('closed');

    $segar = Livewire::test(CustomerMessageList::class);
    expect($segar->viewData('tabCounts')['baru'])->toBe(0)
        ->and($segar->viewData('tabCounts')['semua'])->toBe(0)
        ->and($segar->viewData('tabCounts')['arsip'])->toBe(1);

    // Pulih dari arsip sekaligus melepas tanda spam.
    $segar->call('setTab', 'arsip')->call('pulihkan', $p->id);
    expect(CustomerMessage::find($p->id)->is_spam)->toBeFalse();
});

it('arsip & spam menolak pengguna tanpa izin hapus', function () {
    $p = pesan();
    $p->markAsRead();
    $this->actingAs(adminPesan(['view_customer_message', 'edit_customer_message']));

    $t = Livewire::test(CustomerMessageList::class);
    $t->call('tandaiSpam', $p->id)->assertDispatched('swal-error');
    $t->set('pilih', [(string) $p->id])->call('spamTerpilih')->assertDispatched('swal-error');

    expect(CustomerMessage::find($p->id))->not->toBeNull();
});

it('arsip & spam dari halaman detail mengembalikan ke daftar', function () {
    $this->actingAs(adminPesan());
    $a = pesan();
    $b = pesan();

    Livewire::test(CustomerMessageDetail::class, ['message' => $a])
        ->call('arsipkan')
        ->assertRedirect(route('admin.customer-message.index'));
    expect(CustomerMessage::find($a->id))->toBeNull();

    Livewire::test(CustomerMessageDetail::class, ['message' => $b])->call('tandaiSpam');
    expect(CustomerMessage::withTrashed()->find($b->id)->is_spam)->toBeTrue();
});

// ===================== Penugasan & topik =====================

it('ambil tiket menjadikan pemakai yang sedang login pemegangnya', function () {
    $petugas = adminPesan();
    $this->actingAs($petugas);
    $p = pesan();

    Livewire::test(CustomerMessageList::class)->call('ambilTiket', $p->id);
    expect($p->fresh()->assigned_to)->toBe($petugas->id);
});

it('penugasan massal dan saringan "tiket saya" bekerja bersama', function () {
    $petugas = adminPesan();
    $lain = adminPesan();
    $this->actingAs($petugas);

    $a = pesan(['name' => 'Punya Saya']);
    $b = pesan(['name' => 'Punya Orang Lain']);

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua');
    $t->set('pilih', [(string) $a->id])->call('tugaskanTerpilih', (string) $petugas->id);
    $t->set('pilih', [(string) $b->id])->call('tugaskanTerpilih', (string) $lain->id);

    expect($a->fresh()->assigned_to)->toBe($petugas->id)
        ->and($b->fresh()->assigned_to)->toBe($lain->id);

    $daftar = fn () => $t->viewData('messages')->pluck('name')->all();
    expect($daftar($t->set('fPetugas', 'saya')))->toBe(['Punya Saya'])
        ->and($daftar($t->set('fPetugas', (string) $lain->id)))->toBe(['Punya Orang Lain']);

    // Petugas ngawur tidak pernah tersimpan.
    $t->set('fPetugas', '')->set('pilih', [(string) $a->id])->call('tugaskanTerpilih', '999999');
    expect($a->fresh()->assigned_to)->toBe($petugas->id);
});

it('topik tiket bisa diisi dan dipakai menyaring', function () {
    $this->actingAs(adminPesan());
    $p = pesan(['name' => 'Komplain Satu']);
    pesan(['name' => 'Tanya Biasa']);

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])->set('kategori', 'komplain');
    expect($p->fresh()->kategori)->toBe('komplain')
        ->and($p->fresh()->labelKategori())->toBe('Komplain / kendala');

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua')->set('fKategori', 'komplain');
    expect($t->viewData('messages')->pluck('name')->all())->toBe(['Komplain Satu']);
});

it('prioritas massal ikut tercatat', function () {
    $this->actingAs(adminPesan());
    $a = pesan();
    $b = pesan();

    Livewire::test(CustomerMessageList::class)
        ->set('pilih', [(string) $a->id, (string) $b->id])
        ->call('prioritasTerpilih', 'urgent');

    expect([$a->fresh()->priority, $b->fresh()->priority])->toBe(['urgent', 'urgent'])
        ->and($a->logs()->where('jenis', 'prioritas')->count())->toBe(1);
});

// ===================== Batas waktu membalas =====================

it('tiket lewat batas ditandai menurut prioritasnya', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 14:00:00');
    $this->actingAs(adminPesan());

    // Mendesak: batas 2 jam. Rendah: batas 24 jam.
    $mendesak = pesan(['priority' => 'urgent', 'created_at' => now()->subHours(3)]);
    $rendah = pesan(['priority' => 'low', 'created_at' => now()->subHours(3)]);

    expect($mendesak->lewatBatas())->toBeTrue()
        ->and($rendah->lewatBatas())->toBeFalse();

    // Sudah dibalas = tidak pernah lewat batas lagi.
    $mendesak->update(['replied_at' => now()]);
    expect($mendesak->fresh()->lewatBatas())->toBeFalse();

    \Illuminate\Support\Carbon::setTestNow();
});

it('saringan lewat batas dan hitungan ringkasannya sejalan', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 14:00:00');
    $this->actingAs(adminPesan());
    $telat = pesan(['name' => 'Sudah Telat', 'priority' => 'urgent', 'created_at' => now()->subHours(5)]);
    pesan(['name' => 'Masih Aman', 'priority' => 'low']);

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua');
    expect($t->viewData('lewatBatas'))->toBe(1);

    $t->set('fBatas', 'lewat');
    expect($t->viewData('messages')->pluck('name')->all())->toBe(['Sudah Telat']);

    $t->set('fBatas', 'belum');
    expect($t->viewData('messages')->count())->toBe(2);

    // Begitu dibalas, ia keluar dari kedua saringan.
    $telat->update(['replied_at' => now(), 'replied_by' => auth()->id()]);
    $t->set('fBatas', 'lewat');
    expect($t->viewData('messages')->count())->toBe(0);
});

it('ringkasan menghitung pesan sepekan dan rata-rata waktu tanggap', function () {
    $this->actingAs(adminPesan());
    $a = pesan(['created_at' => now()->subHours(4)]);
    $a->forceFill(['replied_at' => now()->subHours(2)])->saveQuietly();
    pesan(['created_at' => now()->subDays(20)]);

    $data = Livewire::test(CustomerMessageList::class);
    expect($data->viewData('masukPekanIni'))->toBe(1)
        ->and($data->viewData('rataResponJam'))->toBe(2.0);
});

// ===================== Tampilan & navigasi =====================

it('tampilan tabel dan kartu sama-sama menampilkan tiketnya', function () {
    $this->actingAs(adminPesan());
    pesan(['name' => 'Dilihat Dua Cara']);

    $t = Livewire::test(CustomerMessageList::class)->assertSee('Dilihat Dua Cara');
    $t->call('setTampilan', 'tabel')->assertSet('tampilan', 'tabel')->assertSee('Dilihat Dua Cara');
    $t->call('setTampilan', 'ngawur')->assertSet('tampilan', 'kartu');
});

it('detail menyediakan tiket tetangga sesuai urutan waktu', function () {
    $this->actingAs(adminPesan());
    $lama = pesan(['created_at' => now()->subDays(2)]);
    $tengah = pesan(['created_at' => now()->subDay()]);
    $baru = pesan();

    $t = Livewire::test(CustomerMessageDetail::class, ['message' => $tengah]);
    expect($t->viewData('sebelum')->id)->toBe($baru->id)
        ->and($t->viewData('berikut')->id)->toBe($lama->id);

    $ujung = Livewire::test(CustomerMessageDetail::class, ['message' => $baru]);
    expect($ujung->viewData('sebelum'))->toBeNull();
});

it('detail menautkan pengirim ke pelanggan terdaftar dan pesan lamanya', function () {
    $this->actingAs(adminPesan());
    $pelanggan = \App\Models\Customer::create([
        'nama' => 'Bu Sari', 'email' => 'sari@contoh.com', 'no_hp' => '081299887766',
    ]);
    $lama = pesan(['email' => 'sari@contoh.com', 'no_telp' => '081299887766']);
    $baru = pesan(['email' => 'sari@contoh.com', 'no_telp' => '081299887766']);

    $t = Livewire::test(CustomerMessageDetail::class, ['message' => $baru]);
    expect($t->viewData('pelanggan')->id)->toBe($pelanggan->id)
        ->and($t->viewData('pesanLain')->pluck('id')->all())->toBe([$lama->id]);

    // Pengirim tanpa jejak tidak menyeret pesan orang lain.
    $asing = pesan(['email' => 'entah@contoh.com', 'no_telp' => '']);
    $u = Livewire::test(CustomerMessageDetail::class, ['message' => $asing]);
    expect($u->viewData('pelanggan'))->toBeNull()
        ->and($u->viewData('pesanLain'))->toHaveCount(0);
});

it('satu tiket bisa dicetak jadi PDF', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])->call('unduhPdf')->assertFileDownloaded();
});

it('umur tiket ditulis dalam menit, jam, lalu hari', function () {
    // Dipatok siang hari supaya hitungannya tidak menyentuh jam tutup.
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 14:00:00');

    expect(pesan(['created_at' => now()->subMinutes(20)])->menungguTeks())->toBe('20 menit')
        ->and(pesan(['created_at' => now()->subHours(5)])->menungguTeks())->toBe('5 jam')
        // 3 hari kalender = 39 jam kerja = 1 hari kerja lebih.
        ->and(pesan(['created_at' => now()->subDays(3)])->menungguTeks())->toBe('1 hari');

    \Illuminate\Support\Carbon::setTestNow();
});

// ===================== Topik: massal & laporan =====================

it('topik bisa diisi massal dan muncul di sebaran 30 hari', function () {
    $this->actingAs(adminPesan());
    $a = pesan();
    $b = pesan();

    $t = Livewire::test(CustomerMessageList::class)
        ->set('pilih', [(string) $a->id, (string) $b->id])
        ->call('kategoriTerpilih', 'komplain');

    expect([$a->fresh()->kategori, $b->fresh()->kategori])->toBe(['komplain', 'komplain']);

    $segar = Livewire::test(CustomerMessageList::class);
    expect($segar->viewData('sebaranTopik'))->toHaveCount(1)
        ->and($segar->viewData('sebaranTopik')[0])->toMatchArray(['kunci' => 'komplain', 'jumlah' => 2, 'persen' => 100])
        ->and($segar->viewData('tanpaTopik'))->toBe(0);

    // Topik ngawur tidak pernah tersimpan.
    $t->set('pilih', [(string) $a->id])->call('kategoriTerpilih', 'ngawur');
    expect($a->fresh()->kategori)->toBe('komplain');
});

it('sebaran topik menghitung tiket tanpa topik secara terpisah', function () {
    $this->actingAs(adminPesan());
    pesan(['kategori' => 'komplain']);
    pesan();

    $t = Livewire::test(CustomerMessageList::class);
    expect($t->viewData('sebaranTopik'))->toHaveCount(1)
        ->and($t->viewData('tanpaTopik'))->toBe(1);
});

// ===================== Urut dari kepala kolom =====================

it('kepala kolom tabel mengurutkan naik lalu turun', function () {
    $this->actingAs(adminPesan());
    pesan(['name' => 'Ahmad']);
    pesan(['name' => 'Zulkifli']);

    $t = Livewire::test(CustomerMessageList::class)->call('setTampilan', 'tabel');
    $nama = fn () => $t->viewData('messages')->pluck('name')->all();

    $t->call('urutkanKolom', 'nama');
    expect($t->get('urut'))->toBe('nama')
        ->and($nama())->toBe(['Ahmad', 'Zulkifli'])
        ->and($t->instance()->arahUrut('nama'))->toBe('naik');

    $t->call('urutkanKolom', 'nama');
    expect($nama())->toBe(['Zulkifli', 'Ahmad'])
        ->and($t->instance()->arahUrut('nama'))->toBe('turun');

    // Kolom yang tidak ada di daftar diabaikan, bukan bikin kueri ngawur.
    $t->call('urutkanKolom', 'rahasia');
    expect($t->get('urut'))->toBe('nama-turun');
});

// ===================== Pintasan kartu ringkasan =====================

it('kartu ringkasan jadi pintasan saringan', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 14:00:00');
    $this->actingAs(adminPesan());
    $telat = pesan(['name' => 'Telat', 'priority' => 'urgent', 'created_at' => now()->subHours(5)]);
    $telat->markAsRead();
    pesan(['name' => 'Baru Masuk', 'kategori' => 'komplain']);

    $t = Livewire::test(CustomerMessageList::class);

    $t->call('sorotLewatBatas');
    expect($t->get('tab'))->toBe('semua')
        ->and($t->get('fBatas'))->toBe('lewat')
        ->and($t->viewData('messages')->pluck('name')->all())->toBe(['Telat']);

    $t->call('sorotBelumDibaca');
    expect($t->get('tab'))->toBe('baru')
        ->and($t->get('fBatas'))->toBe('')
        ->and($t->viewData('messages')->pluck('name')->all())->toBe(['Baru Masuk']);

    $t->call('sorotPekanIni');
    expect($t->get('fDari'))->toBe(now()->subDays(7)->toDateString())
        ->and($t->viewData('messages'))->toHaveCount(2);

    $t->call('sorotTopik', 'komplain');
    expect($t->get('fKategori'))->toBe('komplain')
        ->and($t->viewData('messages')->pluck('name')->all())->toBe(['Baru Masuk']);

    $t->call('sorotTopik', 'ngawur');
    expect($t->get('fKategori'))->toBe('komplain');
});

// ===================== Jejak spam berulang =====================

it('pengirim yang pernah spam ditandai dan bisa disaring', function () {
    $this->actingAs(adminPesan());

    $dulu = pesan(['email' => 'spam@contoh.com', 'no_telp' => '081200000001']);
    $dulu->markAsRead();
    Livewire::test(CustomerMessageList::class)->call('tandaiSpam', $dulu->id);

    $baru = pesan(['name' => 'Pengirim Sama', 'email' => 'spam@contoh.com', 'no_telp' => '081200000001']);
    $bersih = pesan(['name' => 'Pengirim Bersih', 'email' => 'halo@contoh.com', 'no_telp' => '081200000002']);

    expect($baru->pernahSpam())->toBeTrue()
        ->and($bersih->pernahSpam())->toBeFalse();

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua')->set('fCuriga', '1');
    expect($t->viewData('messages')->pluck('name')->all())->toBe(['Pengirim Sama']);
});

it('arsip bisa disaring hanya spam atau tanpa spam', function () {
    $this->actingAs(adminPesan());
    $spam = pesan(['name' => 'Iklan Judi']);
    $spam->markAsRead();
    $biasa = pesan(['name' => 'Tiket Biasa']);
    $biasa->markAsRead();

    $t = Livewire::test(CustomerMessageList::class);
    $t->call('tandaiSpam', $spam->id);
    $t->call('delete', $biasa->id);

    $t->call('setTab', 'arsip');
    expect($t->viewData('messages'))->toHaveCount(2);

    $t->set('fSpam', 'spam');
    expect($t->viewData('messages')->pluck('name')->all())->toBe(['Iklan Judi']);

    $t->set('fSpam', 'biasa');
    expect($t->viewData('messages')->pluck('name')->all())->toBe(['Tiket Biasa']);
});

// ===================== Notifikasi =====================

it('petugas diberi tahu saat tiket diserahkan kepadanya', function () {
    \Illuminate\Support\Facades\Notification::fake();
    $saya = adminPesan();
    $orangLain = adminPesan();
    $this->actingAs($saya);
    $p = pesan();

    Livewire::test(CustomerMessageList::class)
        ->set('pilih', [(string) $p->id])
        ->call('tugaskanTerpilih', (string) $orangLain->id);

    \Illuminate\Support\Facades\Notification::assertSentTo($orangLain, \App\Notifications\TiketDitugaskan::class);

    // Menugaskan ke diri sendiri tidak perlu diberitahukan.
    Livewire::test(CustomerMessageList::class)->call('ambilTiket', $p->id);
    \Illuminate\Support\Facades\Notification::assertNotSentTo($saya, \App\Notifications\TiketDitugaskan::class);
});

it('pengingat harian hanya menyentuh tiket yang lewat batas', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 14:00:00');
    \Illuminate\Support\Facades\Notification::fake();
    $petugas = adminPesan();

    $telat = pesan(['priority' => 'urgent', 'created_at' => now()->subHours(6)]);
    $telat->update(['assigned_to' => $petugas->id]);
    pesan(['priority' => 'low']);

    $this->artisan('helpdesk:ingatkan-lewat-batas')->assertSuccessful();

    \Illuminate\Support\Facades\Notification::assertSentTo($petugas, \App\Notifications\TiketLewatBatas::class,
        fn ($notif) => $notif->tiket->count() === 1);

    // --kering tidak mengirim apa pun.
    \Illuminate\Support\Facades\Notification::fake();
    $this->artisan('helpdesk:ingatkan-lewat-batas --kering')->assertSuccessful();
    \Illuminate\Support\Facades\Notification::assertNothingSent();
});

// ===================== Pembersih arsip =====================

it('pembersih arsip membuang spam lebih cepat daripada tiket biasa', function () {
    $lamaBiasa = pesan();
    $lamaBiasa->delete();
    $lamaBiasa->forceFill(['deleted_at' => now()->subDays(200)])->saveQuietly();

    $barusanBiasa = pesan();
    $barusanBiasa->delete();
    $barusanBiasa->forceFill(['deleted_at' => now()->subDays(60)])->saveQuietly();

    $spamLama = pesan(['is_spam' => true]);
    $spamLama->delete();
    $spamLama->forceFill(['deleted_at' => now()->subDays(45)])->saveQuietly();

    $this->artisan('helpdesk:bersihkan-arsip --kering')->assertSuccessful();
    expect(CustomerMessage::withTrashed()->count())->toBe(3);

    $this->artisan('helpdesk:bersihkan-arsip')->assertSuccessful();

    expect(CustomerMessage::withTrashed()->pluck('id')->all())->toBe([$barusanBiasa->id]);
});

// ===================== Balasan lewat surel =====================

it('balasan bisa dikirim langsung lewat surel dan tercatat', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $this->actingAs(adminPesan());
    $p = pesan(['email' => 'pelanggan@contoh.com']);

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->set('balasanKanal', 'email')
        ->set('balasanKirimSurel', true)
        ->set('balasanIsi', 'Akunnya sudah aktif ya, silakan dicoba.')
        ->call('simpanBalasan')
        ->assertHasNoErrors();

    \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\BalasanTiketMail::class,
        fn ($mail) => $mail->hasTo('pelanggan@contoh.com') && str_contains($mail->isi, 'sudah aktif'));

    $p->refresh();
    expect($p->sudahDibalas())->toBeTrue()
        ->and($p->logs()->pluck('jenis')->all())->toContain('surel');
});

it('balasan lewat surel ditolak bila tiketnya tanpa alamat surel', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $this->actingAs(adminPesan());
    $p = pesan(['email' => '']);

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->set('balasanKanal', 'email')
        ->set('balasanKirimSurel', true)
        ->set('balasanIsi', 'Halo, ini balasannya.')
        ->call('simpanBalasan')
        ->assertHasErrors('balasanIsi');

    \Illuminate\Support\Facades\Mail::assertNothingSent();
    // Tiket TIDAK boleh terlanjur berstatus dibalas kalau surelnya tidak terkirim.
    expect($p->fresh()->sudahDibalas())->toBeFalse();
});

it('kanal selain surel tidak pernah mengirim email', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $this->actingAs(adminPesan());
    $p = pesan(['email' => 'pelanggan@contoh.com']);

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->set('balasanKanal', 'whatsapp')
        ->set('balasanKirimSurel', true)
        ->set('balasanIsi', 'Dibalas lewat WhatsApp.')
        ->call('simpanBalasan');

    \Illuminate\Support\Facades\Mail::assertNothingSent();
    expect($p->fresh()->sudahDibalas())->toBeTrue();
});

// ===================== Lampiran =====================

it('pelanggan bisa melampirkan berkas dari formulir kontak', function () {
    \Illuminate\Support\Facades\Storage::fake('local');

    Livewire::test(\App\Livewire\Pages\Public\Contact\Contact::class)
        ->set('name', 'Bu Ani')
        ->set('email', 'ani@contoh.com')
        ->set('no_telp', '+6281234567890')
        ->set('message', 'Ini bukti transfernya ya.')
        ->set('lampiran', \Illuminate\Http\UploadedFile::fake()->image('bukti.png'))
        ->call('save')
        ->assertHasNoErrors();

    $pesan = CustomerMessage::latest('id')->first();
    $lampiran = $pesan->lampiran()->first();

    expect($lampiran->nama_asli)->toBe('bukti.png')
        ->and($lampiran->sumber)->toBe('pelanggan');

    // Disk PRIVAT, bukan public: isinya bisa berupa tangkapan layar mutasi bank.
    \Illuminate\Support\Facades\Storage::disk('local')->assertExists($lampiran->path);
});

it('lampiran pelanggan tidak bisa dihapus admin, lampiran admin bisa', function () {
    \Illuminate\Support\Facades\Storage::fake('local');
    $this->actingAs(adminPesan());
    $p = pesan();

    $dariPelanggan = $p->lampiran()->create([
        'sumber' => 'pelanggan', 'nama_asli' => 'bukti.png', 'path' => 'helpdesk/bukti.png', 'ukuran' => 1024,
    ]);

    $t = Livewire::test(CustomerMessageDetail::class, ['message' => $p]);
    $t->call('hapusLampiran', $dariPelanggan->id)->assertDispatched('toast-error');
    expect($p->lampiran()->count())->toBe(1);

    $t->set('berkasBaru', \Illuminate\Http\UploadedFile::fake()->create('panduan.pdf', 120, 'application/pdf'))
        ->call('unggahLampiran')
        ->assertHasNoErrors();

    $dariAdmin = $p->lampiran()->where('sumber', 'admin')->first();
    expect($dariAdmin->nama_asli)->toBe('panduan.pdf')
        ->and($p->logs()->where('jenis', 'lampiran')->count())->toBe(1);

    $t->call('hapusLampiran', $dariAdmin->id);
    expect($p->lampiran()->count())->toBe(1);
});

it('lampiran hanya bisa diunduh lewat route ber-izin', function () {
    \Illuminate\Support\Facades\Storage::fake('local');
    \Illuminate\Support\Facades\Storage::disk('local')->put('helpdesk/bukti.png', 'isi');
    $p = pesan();
    $lampiran = $p->lampiran()->create([
        'sumber' => 'pelanggan', 'nama_asli' => 'bukti.png', 'path' => 'helpdesk/bukti.png', 'ukuran' => 3,
    ]);

    $this->get(route('admin.customer-message.lampiran', $lampiran->id))->assertRedirect();

    // Lewat HTTP sungguhan: EnsureProfileComplete ikut jalan, jadi profil
    // petugasnya dilengkapi dulu supaya yang diuji benar-benar izinnya.
    $petugas = adminPesan();
    $petugas->detail()->create([
        'jabatan' => 'Helpdesk',
        'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01',
        'phone' => '081234567890',
        'alamat' => 'Jl. Uji No. 1',
    ]);

    $this->actingAs($petugas)
        ->get(route('admin.customer-message.lampiran', $lampiran->id))
        ->assertOk();

    // Tanpa izin lihat helpdesk: ditolak, bukan diunduh.
    $asing = \App\Models\User::factory()->create([
        'role_id' => \App\Models\Role::create(['name' => 'uji-pp-asing-'.Str::random(4), 'description' => 'uji'])->id,
        'status' => 'active',
    ]);
    $asing->detail()->create([
        'jabatan' => 'Lainnya',
        'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01',
        'phone' => '081234567890',
        'alamat' => 'Jl. Uji No. 2',
    ]);

    $this->actingAs($asing)
        ->get(route('admin.customer-message.lampiran', $lampiran->id))
        ->assertForbidden();
});

// ===================== Gabung tiket ganda =====================

it('tiket ganda bisa digabungkan ke tiket utama', function () {
    $this->actingAs(adminPesan());
    $lama = pesan(['email' => 'sama@contoh.com', 'message' => 'Pesanan saya belum masuk.']);
    $baru = pesan(['email' => 'sama@contoh.com', 'message' => 'Halo, saya tanya lagi soal pesanan.']);

    Livewire::test(CustomerMessageDetail::class, ['message' => $baru])->call('gabungkanTiket', $lama->id);

    $lama = CustomerMessage::withTrashed()->find($lama->id);
    expect($lama->trashed())->toBeTrue()
        ->and($lama->status)->toBe('closed')
        ->and($lama->merged_into)->toBe($baru->id);

    // Isi tiket lama ikut tersalin ke linimasa tiket utama.
    expect($baru->logs()->where('jenis', 'gabung')->first()->isi)->toContain('Pesanan saya belum masuk.');

    // Tidak bisa digabung dua kali.
    Livewire::test(CustomerMessageDetail::class, ['message' => $baru])
        ->call('gabungkanTiket', $lama->id)
        ->assertDispatched('toast-error');
});

// ===================== Template: ubah & urutkan =====================

it('template bisa diubah, diurutkan, dan dipakai di catatan', function () {
    $this->actingAs(adminPesan());
    $p = pesan(['name' => 'Rina']);
    $semua = \App\Models\CustomerMessageTemplate::urut()->get();
    $pertama = $semua->first();
    $kedua = $semua[1];

    $t = Livewire::test(CustomerMessageDetail::class, ['message' => $p]);

    $t->call('editTemplate', $pertama->id)
        ->assertSet('templateNama', $pertama->nama)
        ->set('templateNama', 'Sapaan baru')
        ->call('simpanTemplate')
        ->assertSet('templateId', '');

    expect($pertama->fresh()->nama)->toBe('Sapaan baru')
        ->and(\App\Models\CustomerMessageTemplate::count())->toBe($semua->count());

    // Geser: yang kedua naik jadi paling atas.
    $t->call('geserTemplate', $kedua->id, 'naik');
    expect(\App\Models\CustomerMessageTemplate::urut()->first()->id)->toBe($kedua->id);

    $t->call('pakaiTemplateCatatan', $kedua->id);
    expect($t->get('catatanIsi'))->toBe($kedua->fresh()->untuk($p));
});

// ===================== Ekspor membawa jejak balasan =====================

it('ekspor excel memuat balasan terakhir', function () {
    \Maatwebsite\Excel\Facades\Excel::fake();
    $this->actingAs(adminPesan());
    $p = pesan();
    $p->catat('balasan', 'Sudah kami kirim ulang ke email Anda.', 'email');

    Livewire::test(CustomerMessageList::class)->call('setTab', 'semua')->call('unduhExcel');

    \Maatwebsite\Excel\Facades\Excel::assertDownloaded('pesan-pelanggan-'.now()->format('Ymd-His').'.xlsx',
        function (\App\Exports\PesanPelangganExport $ekspor) {
            return str_contains($ekspor->view()->render(), 'Sudah kami kirim ulang');
        });
});

// ===================== Jam kerja =====================

it('tenggat dan keterlambatan dihitung memakai jam kerja', function () {
    // Toko buka 08.00-21.00. Tiket mendesak (batas 2 jam) yang masuk 23.00
    // TIDAK boleh dianggap terlambat pukul 01.00 dini hari.
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 01:00:00');
    $malam = pesan(['priority' => 'urgent', 'created_at' => \Illuminate\Support\Carbon::parse('2026-09-20 23:00:00')]);

    expect($malam->lewatBatas())->toBeFalse()
        ->and($malam->tenggat()->format('Y-m-d H:i'))->toBe('2026-09-21 10:00');

    // Jam 11.00 barulah ia benar-benar lewat batas.
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 11:00:00');
    expect($malam->fresh()->lewatBatas())->toBeTrue();

    \Illuminate\Support\Carbon::setTestNow();
});

it('saringan lewat batas ikut memakai jam kerja', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 09:00:00');
    $this->actingAs(adminPesan());

    $malam = pesan(['name' => 'Kiriman Malam', 'priority' => 'urgent', 'created_at' => \Illuminate\Support\Carbon::parse('2026-09-20 23:00:00')]);
    $kemarin = pesan(['name' => 'Kiriman Kemarin Pagi', 'priority' => 'urgent', 'created_at' => \Illuminate\Support\Carbon::parse('2026-09-20 09:00:00')]);

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua')->set('fBatas', 'lewat');

    // Hanya yang masuk saat toko buka kemarin yang sudah lewat jatahnya.
    expect($t->viewData('messages')->pluck('name')->all())->toBe(['Kiriman Kemarin Pagi'])
        ->and($t->viewData('lewatBatas'))->toBe(1)
        ->and($malam->fresh()->lewatBatas())->toBeFalse();

    \Illuminate\Support\Carbon::setTestNow();
});

// ===================== Tandai belum dibaca =====================

it('tiket bisa dikembalikan ke belum dibaca', function () {
    $this->actingAs(adminPesan());
    $p = pesan();
    $p->markAsRead();

    $t = Livewire::test(CustomerMessageList::class);
    expect($t->viewData('tabCounts')['baru'])->toBe(0);

    $t->call('tandaiBelumDibaca', $p->id);

    expect($p->fresh()->belumDibaca())->toBeTrue()
        ->and($p->logs()->where('jenis', 'belum-dibaca')->count())->toBe(1)
        ->and(Livewire::test(CustomerMessageList::class)->viewData('tabCounts')['baru'])->toBe(1);
});

it('dari halaman detail, tandai belum dibaca mengembalikan ke daftar', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    // Kalau tetap di halaman detail, mount() akan menandainya dibaca lagi.
    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->call('tandaiBelumDibaca')
        ->assertRedirect(route('admin.customer-message.index'));

    expect($p->fresh()->belumDibaca())->toBeTrue();
});

// ===================== Tunda =====================

it('tiket yang ditunda berhenti dihitung lewat batas', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 14:00:00');
    $this->actingAs(adminPesan());

    $p = pesan(['priority' => 'urgent', 'created_at' => now()->subHours(5)]);
    expect($p->lewatBatas())->toBeTrue();

    $t = Livewire::test(CustomerMessageList::class);
    $t->call('tunda', $p->id, 3);

    $p->refresh();
    expect($p->ditunda())->toBeTrue()
        ->and($p->lewatBatas())->toBeFalse()
        ->and($p->logs()->where('jenis', 'tunda')->count())->toBe(1);

    // Tidak ikut saringan & hitungan lewat batas.
    $segar = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua')->set('fBatas', 'lewat');
    expect($segar->viewData('messages'))->toHaveCount(0)
        ->and($segar->viewData('lewatBatas'))->toBe(0);

    // Saringan "sedang ditunda" menemukannya.
    $segar->set('fBatas', 'tunda');
    expect($segar->viewData('messages')->pluck('id')->all())->toBe([$p->id]);

    // Dilanjutkan lagi.
    $t->call('lanjutkanTunda', $p->id);
    expect($p->fresh()->ditunda())->toBeFalse()
        ->and($p->fresh()->lewatBatas())->toBeTrue();

    // Lama tunda di luar pilihan diabaikan.
    $t->call('tunda', $p->id, 99);
    expect($p->fresh()->ditunda())->toBeFalse();

    \Illuminate\Support\Carbon::setTestNow();
});

// ===================== Pencarian, saringan, pilih semua =====================

it('pencarian menjangkau isi balasan dan catatan internal', function () {
    $this->actingAs(adminPesan());
    $p = pesan(['name' => 'Bu Tuti', 'message' => 'Halo, saya mau tanya.']);
    $p->catat('balasan', 'Dananya sudah kami refund ke rekening Anda.', 'whatsapp');
    pesan(['name' => 'Orang Lain', 'message' => 'Pertanyaan biasa.']);

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua')->set('search', 'refund');
    expect($t->viewData('messages')->pluck('name')->all())->toBe(['Bu Tuti']);
});

it('saringan belum ditugaskan memisahkan tiket tanpa petugas', function () {
    $petugas = adminPesan();
    $this->actingAs($petugas);
    $dipegang = pesan(['name' => 'Sudah Dipegang', 'assigned_to' => $petugas->id]);
    pesan(['name' => 'Belum Dipegang']);

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua')->set('fPetugas', 'kosong');
    expect($t->viewData('messages')->pluck('name')->all())->toBe(['Belum Dipegang']);

    $t->set('fPetugas', 'saya');
    expect($t->viewData('messages')->pluck('name')->all())->toBe(['Sudah Dipegang']);
});

it('pilih semua hasil mencentang lintas halaman', function () {
    $this->actingAs(adminPesan());
    collect(range(1, 15))->each(fn () => pesan());

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua')->set('perPage', 12);
    expect($t->viewData('messages'))->toHaveCount(12);

    $t->call('pilihSemuaHasil');
    expect($t->get('pilih'))->toHaveCount(15);
});

// ===================== Urungkan aksi massal =====================

it('arsip massal bisa diurungkan sekali klik', function () {
    $this->actingAs(adminPesan());
    $a = pesan();
    $a->markAsRead();
    $b = pesan();
    $b->markAsRead();

    $t = Livewire::test(CustomerMessageList::class)
        ->set('pilih', [(string) $a->id, (string) $b->id])
        ->call('hapusTerpilih');

    expect($t->get('urungkanId'))->toHaveCount(2)
        ->and(CustomerMessage::count())->toBe(0);

    $t->call('urungkan');
    expect(CustomerMessage::count())->toBe(2)
        ->and($t->get('urungkanId'))->toBe([]);
});

it('spam massal juga bisa diurungkan berikut tanda spamnya', function () {
    $this->actingAs(adminPesan());
    $p = pesan();
    $p->markAsRead();

    $t = Livewire::test(CustomerMessageList::class)->set('pilih', [(string) $p->id])->call('spamTerpilih');
    expect(CustomerMessage::withTrashed()->find($p->id)->is_spam)->toBeTrue();

    $t->call('urungkan');
    $p->refresh();
    expect($p->trashed())->toBeFalse()->and($p->is_spam)->toBeFalse();
});

// ===================== Rekap petugas & tutup otomatis =====================

it('rekap petugas menghitung tiket dan waktu tanggapnya', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 14:00:00');
    $petugas = adminPesan();
    $this->actingAs($petugas);

    $dibalas = pesan(['assigned_to' => $petugas->id, 'created_at' => now()->subHours(3)]);
    $dibalas->forceFill(['replied_at' => now()->subHour()])->saveQuietly();
    pesan(['assigned_to' => $petugas->id]);

    $rekap = Livewire::test(CustomerMessageList::class)->viewData('rekapPetugas');

    expect($rekap)->toHaveCount(1)
        ->and($rekap[0])->toMatchArray(['nama' => $petugas->name, 'jumlah' => 2, 'dibalas' => 1, 'rata' => 2.0]);

    \Illuminate\Support\Carbon::setTestNow();
});

it('tiket selesai ditutup otomatis setelah lewat batas harinya', function () {
    $lama = pesan(['status' => 'resolved']);
    $lama->forceFill(['updated_at' => now()->subDays(10)])->saveQuietly();
    $baru = pesan(['status' => 'resolved']);

    $this->artisan('helpdesk:tutup-selesai --kering')->assertSuccessful();
    expect($lama->fresh()->status)->toBe('resolved');

    $this->artisan('helpdesk:tutup-selesai')->assertSuccessful();

    expect($lama->fresh()->status)->toBe('closed')
        ->and($baru->fresh()->status)->toBe('resolved')
        ->and($lama->logs()->where('jenis', 'status')->first()->isi)->toContain('otomatis');
});

it('menutup tiket tanpa balasan dicatat apa adanya', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])->set('status', 'resolved');

    expect($p->logs()->where('jenis', 'status')->first()->isi)->toContain('tanpa balasan tercatat');
});

// ===================== Sisi pelanggan =====================

it('pelanggan menerima surel tanda terima berisi nomor tiket', function () {
    \Illuminate\Support\Facades\Mail::fake();
    \Illuminate\Support\Facades\Storage::fake('local');

    Livewire::test(\App\Livewire\Pages\Public\Contact\Contact::class)
        ->set('name', 'Pak Budi')
        ->set('email', 'budi@contoh.com')
        ->set('no_telp', '+6281234567890')
        ->set('message', 'Akun saya belum aktif.')
        ->call('save')
        ->assertHasNoErrors();

    $pesan = CustomerMessage::latest('id')->first();

    \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\TiketDiterimaMail::class,
        fn ($mail) => $mail->hasTo('budi@contoh.com') && $mail->pesan->is($pesan));
});

it('kiriman kembar dalam 10 menit tidak membuat tiket kedua', function () {
    \Illuminate\Support\Facades\Mail::fake();
    \Illuminate\Support\Facades\Storage::fake('local');

    $isi = fn () => Livewire::test(\App\Livewire\Pages\Public\Contact\Contact::class)
        ->set('name', 'Bu Ani')
        ->set('email', 'ani@contoh.com')
        ->set('no_telp', '+6281234567891')
        ->set('message', 'Pesanan saya belum datang.');

    $isi()->call('save');
    $isi()->call('save');

    expect(CustomerMessage::where('email', 'ani@contoh.com')->count())->toBe(1);

    // Tiket kedua tetap dibuat kalau isinya memang berbeda.
    Livewire::test(\App\Livewire\Pages\Public\Contact\Contact::class)
        ->set('name', 'Bu Ani')
        ->set('email', 'ani@contoh.com')
        ->set('no_telp', '+6281234567891')
        ->set('message', 'Oh ya, satu lagi.')
        ->call('save');

    expect(CustomerMessage::where('email', 'ani@contoh.com')->count())->toBe(2);
});

it('pelanggan bisa melampirkan sampai tiga berkas', function () {
    \Illuminate\Support\Facades\Mail::fake();
    \Illuminate\Support\Facades\Storage::fake('local');

    Livewire::test(\App\Livewire\Pages\Public\Contact\Contact::class)
        ->set('name', 'Bu Sari')
        ->set('email', 'sari@contoh.com')
        ->set('no_telp', '+6281234567892')
        ->set('message', 'Ini tiga bukti transfernya.')
        ->set('lampiran', [
            \Illuminate\Http\UploadedFile::fake()->image('satu.png'),
            \Illuminate\Http\UploadedFile::fake()->image('dua.png'),
            \Illuminate\Http\UploadedFile::fake()->image('tiga.png'),
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect(CustomerMessage::latest('id')->first()->lampiran()->count())->toBe(3);
});

it('lebih dari tiga lampiran ditolak', function () {
    \Illuminate\Support\Facades\Mail::fake();
    \Illuminate\Support\Facades\Storage::fake('local');

    Livewire::test(\App\Livewire\Pages\Public\Contact\Contact::class)
        ->set('name', 'Bu Sari')
        ->set('email', 'sari2@contoh.com')
        ->set('no_telp', '+6281234567893')
        ->set('message', 'Terlalu banyak lampiran.')
        ->set('lampiran', collect(range(1, 4))->map(fn ($i) => \Illuminate\Http\UploadedFile::fake()->image("berkas{$i}.png"))->all())
        ->call('save')
        ->assertHasErrors('lampiran');

    expect(CustomerMessage::where('email', 'sari2@contoh.com')->count())->toBe(0);
});

// ===================== Halaman lacak tiket publik =====================

it('tautan bertanda tangan langsung membuka status tiket', function () {
    $p = pesan(['name' => 'Pak Rudi', 'email' => 'rudi@contoh.com']);
    $p->catat('balasan', 'Akunnya sudah kami kirim ya Pak.', 'email');
    $p->catat('catatan', 'CATATAN INTERNAL: hati-hati pelanggan ini sering komplain.');

    $tautan = \Illuminate\Support\Facades\URL::signedRoute('tiket.lacak', ['ticket' => $p->ticket]);

    $this->get($tautan)
        ->assertOk()
        ->assertSee($p->ticket)
        ->assertSee('Akunnya sudah kami kirim')
        // Catatan internal TIDAK pernah ikut ke halaman publik.
        ->assertDontSee('CATATAN INTERNAL');
});

it('tanpa tanda tangan, status tiket butuh nomor dan surel yang cocok', function () {
    $p = pesan(['email' => 'rudi@contoh.com']);

    $t = Livewire::test(\App\Livewire\Pages\Public\Contact\LacakTiket::class);

    // Surel keliru: tidak ketemu, dan isi pesannya tidak bocor.
    $t->set('ticket', $p->ticket)->set('email', 'orang@lain.com')->call('cari');
    expect($t->get('pesan'))->toBeNull();
    $t->assertDontSee($p->message);

    $t->set('email', 'rudi@contoh.com')->call('cari');
    expect($t->get('pesan')->id)->toBe($p->id);
    $t->assertSee($p->ticket);
});

it('lacak tiket menolak percobaan beruntun', function () {
    $p = pesan(['email' => 'rudi@contoh.com']);
    \Illuminate\Support\Facades\RateLimiter::clear('lacak-tiket:127.0.0.1');

    $t = Livewire::test(\App\Livewire\Pages\Public\Contact\LacakTiket::class)->set('ticket', $p->ticket)->set('email', 'salah@contoh.com');

    for ($i = 0; $i < 6; $i++) {
        $t->call('cari');
    }

    $t->call('cari')->assertHasErrors('ticket');
    \Illuminate\Support\Facades\RateLimiter::clear('lacak-tiket:127.0.0.1');
});

// ===================== Pembatasan akses antar petugas =====================

it('tanpa izin lihat semua, petugas hanya melihat tiketnya dan yang belum dipegang', function () {
    $saya = adminPesan(['view_customer_message', 'edit_customer_message']);
    $orangLain = adminPesan();
    $this->actingAs($saya);

    $milikSaya = pesan(['name' => 'Punya Saya', 'assigned_to' => $saya->id]);
    $milikOrangLain = pesan(['name' => 'Punya Orang Lain', 'assigned_to' => $orangLain->id]);
    $bebas = pesan(['name' => 'Belum Dipegang']);

    $t = Livewire::test(CustomerMessageList::class)->call('setTab', 'semua');
    $terlihat = $t->viewData('messages')->pluck('name')->all();

    expect($terlihat)->toContain('Punya Saya')
        ->and($terlihat)->toContain('Belum Dipegang')
        ->and($terlihat)->not->toContain('Punya Orang Lain')
        // Hitungan tabnya ikut dibatasi, bukan cuma daftarnya.
        ->and($t->viewData('tabCounts')['semua'])->toBe(2);

    // Dengan izin lihat semua, ketiganya terlihat.
    $this->actingAs(adminPesan());
    expect(Livewire::test(CustomerMessageList::class)->call('setTab', 'semua')->viewData('messages'))->toHaveCount(3);
});

// ===================== Nomor tiket =====================

it('nomor tiket tidak pernah bentrok dengan yang sudah ada', function () {
    $pertama = pesan();

    // Paksa pembangkit acak mengembalikan nomor yang sudah dipakai lebih dulu.
    $urutan = [substr($pertama->ticket, 4, 4), substr($pertama->ticket, 9, 4), 'ZZZZ', 'YYYY'];
    $i = 0;
    \Illuminate\Support\Str::createRandomStringsUsing(function () use (&$i, $urutan) {
        return $urutan[$i++] ?? 'XXXX';
    });

    $kedua = pesan();
    \Illuminate\Support\Str::createRandomStringsNormally();

    expect($kedua->ticket)->not->toBe($pertama->ticket)
        ->and($kedua->ticket)->toBe('TKT-ZZZZ-YYYY');
});

// ===================== Balas WA satu klik & template beraksi =====================

it('tombol sudah dibalas di WA mencatat tanpa mengetik ulang', function () {
    $petugas = adminPesan();
    $this->actingAs($petugas);
    $p = pesan();

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])->call('tandaiDibalasWa');

    $p->refresh();
    $log = $p->logs()->where('jenis', 'balasan')->first();

    expect($p->sudahDibalas())->toBeTrue()
        ->and($p->replied_by)->toBe($petugas->id)
        ->and($log->kanal)->toBe('whatsapp')
        ->and($log->isi)->toContain('WhatsApp');
});

it('template bisa sekalian mengubah status dan topik', function () {
    $this->actingAs(adminPesan());
    $p = pesan();

    $template = \App\Models\CustomerMessageTemplate::create([
        'nama' => 'Akun disiapkan',
        'isi' => 'Halo {nama}, akunnya sedang kami siapkan.',
        'status_baru' => 'in_progress',
        'kategori_baru' => 'jasa',
        'urutan' => 9,
    ]);

    Livewire::test(CustomerMessageDetail::class, ['message' => $p])
        ->call('pakaiTemplate', $template->id)
        ->assertSet('status', 'in_progress')
        ->assertSet('kategori', 'jasa');

    $p->refresh();
    expect($p->status)->toBe('in_progress')
        ->and($p->kategori)->toBe('jasa')
        ->and($template->aksiTeks())->toBe('Diproses · Jasa cek plagiasi & AI');
});

it('tunda bisa memakai tanggal pilihan sendiri', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 10:00:00');
    $this->actingAs(adminPesan());
    $p = pesan();

    $t = Livewire::test(CustomerMessageDetail::class, ['message' => $p]);
    $t->call('tundaSampai', '2026-10-02');

    expect($p->fresh()->tunda_sampai->format('Y-m-d H:i'))->toBe('2026-10-02 08:00');

    // Tanggal yang sudah lewat ditolak.
    $t->call('tundaSampai', '2026-09-01')->assertHasErrors('tundaTanggal');

    \Illuminate\Support\Carbon::setTestNow();
});

it('tiket induk menampilkan tiket yang digabungkan ke dalamnya', function () {
    $this->actingAs(adminPesan());
    $lama = pesan(['email' => 'sama@contoh.com']);
    $induk = pesan(['email' => 'sama@contoh.com']);

    $t = Livewire::test(CustomerMessageDetail::class, ['message' => $induk]);
    $t->call('gabungkanTiket', $lama->id);

    $segar = Livewire::test(CustomerMessageDetail::class, ['message' => $induk->fresh()]);
    expect($segar->viewData('gabungan')->pluck('id')->all())->toBe([$lama->id]);
});

// ===================== Pengingat per topik =====================

it('pengingat tiket tanpa petugas diarahkan menurut topiknya', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 14:00:00');
    \Illuminate\Support\Facades\Notification::fake();

    $keuangan = adminPesan(['view_customer_message', 'view_cashflow']);
    $helpdesk = adminPesan(['view_customer_message', 'edit_customer_message']);

    pesan(['priority' => 'urgent', 'kategori' => 'pembayaran', 'created_at' => now()->subHours(6)]);
    pesan(['priority' => 'urgent', 'kategori' => 'komplain', 'created_at' => now()->subHours(6)]);

    $this->artisan('helpdesk:ingatkan-lewat-batas')->assertSuccessful();

    // Tiket pembayaran ke pemegang izin cashflow, sisanya ke penanggung helpdesk.
    \Illuminate\Support\Facades\Notification::assertSentTo($keuangan, \App\Notifications\TiketLewatBatas::class,
        fn ($notif) => $notif->tiket->count() === 1 && $notif->tiket->first()->kategori === 'pembayaran');
    \Illuminate\Support\Facades\Notification::assertSentTo($helpdesk, \App\Notifications\TiketLewatBatas::class,
        fn ($notif) => $notif->tiket->first()->kategori === 'komplain');

    \Illuminate\Support\Carbon::setTestNow();
});

// ===================== Pelanggan: keterangan tambahan & penilaian =====================

it('keterangan dari pelanggan masuk ke tiket dan mengembalikannya ke antrean', function () {
    $p = pesan(['status' => 'resolved']);
    $p->markAsRead();

    $t = Livewire::test(\App\Livewire\Pages\Public\Contact\LacakTiket::class);
    $t->set('ticket', $p->ticket)->set('email', $p->email)->call('cari');

    $t->set('tambahan', 'Ternyata masih belum bisa dipakai, Pak.')->call('tambahKeterangan')
        ->assertSet('tambahan', '');

    $p->refresh();
    expect($p->logs()->where('jenis', 'pelanggan')->first()->isi)->toContain('masih belum bisa')
        // Tiket yang sudah ditutup dibuka lagi supaya tidak terlewat.
        ->and($p->status)->toBe('open')
        ->and($p->belumDibaca())->toBeTrue();
});

it('keterangan kosong ditolak dan butuh tiket yang sudah ditemukan', function () {
    $p = pesan();

    $t = Livewire::test(\App\Livewire\Pages\Public\Contact\LacakTiket::class);
    // Belum mencari apa pun: tidak melakukan apa-apa, bukan galat.
    $t->set('tambahan', 'Halo')->call('tambahKeterangan');
    expect($p->fresh()->logs()->count())->toBe(0);

    $t->set('ticket', $p->ticket)->set('email', $p->email)->call('cari');
    $t->set('tambahan', ' ')->call('tambahKeterangan')->assertHasErrors('tambahan');
});

it('pelanggan bisa menilai tiket yang sudah selesai', function () {
    $p = pesan(['status' => 'resolved']);

    $t = Livewire::test(\App\Livewire\Pages\Public\Contact\LacakTiket::class);
    $t->set('ticket', $p->ticket)->set('email', $p->email)->call('cari');

    $t->call('nilai', 3);
    expect($p->fresh()->kepuasan)->toBe(3)
        ->and($p->fresh()->tampilanKepuasan()[0])->toBe('Puas');

    $t->set('nilaiKomentar', 'Cepat dan jelas, terima kasih.')->call('simpanKomentarNilai');
    expect($p->fresh()->kepuasan_komentar)->toContain('Cepat dan jelas');

    // Nilai di luar 1-3 diabaikan.
    $t->call('nilai', 9);
    expect($p->fresh()->kepuasan)->toBe(3);
});

it('tiket yang belum selesai tidak bisa dinilai', function () {
    $p = pesan(['status' => 'in_progress']);

    $t = Livewire::test(\App\Livewire\Pages\Public\Contact\LacakTiket::class);
    $t->set('ticket', $p->ticket)->set('email', $p->email)->call('cari');
    $t->call('nilai', 3);

    expect($p->fresh()->kepuasan)->toBeNull();
});

it('ringkasan helpdesk memuat persentase kepuasan', function () {
    $this->actingAs(adminPesan());
    $puas = pesan(['status' => 'resolved']);
    $puas->update(['kepuasan' => 3, 'kepuasan_at' => now()]);
    $kecewa = pesan(['status' => 'resolved']);
    $kecewa->update(['kepuasan' => 1, 'kepuasan_at' => now()]);

    expect(Livewire::test(CustomerMessageList::class)->viewData('kepuasan'))
        ->toMatchArray(['jumlah' => 2, 'puas' => 1, 'persen' => 50]);
});

// ===================== Tautan surel =====================

it('tautan lacak di surel punya masa berlaku', function () {
    \Illuminate\Support\Carbon::setTestNow('2026-09-21 10:00:00');
    $p = pesan();

    $tautan = (new \App\Mail\TiketDiterimaMail($p))->content()->with['tautan'];

    // Masih berlaku hari ini…
    $this->get($tautan)->assertOk()->assertSee($p->ticket);

    // …tapi tidak selamanya.
    \Illuminate\Support\Carbon::setTestNow('2027-01-21 10:00:00');
    $this->get($tautan)->assertOk()->assertSee('sudah kedaluwarsa');

    \Illuminate\Support\Carbon::setTestNow();
});

it('surel balasan tidak lagi menjanjikan balasan surel masuk ke tiket', function () {
    $p = pesan();
    $isi = (new \App\Mail\BalasanTiketMail($p, 'Sudah kami proses ya.'))->render();

    // Kotak masuk surel tidak ada yang membaca otomatis; jangan pernah
    // menjanjikan sebaliknya.
    expect($isi)->not->toContain('balasannya masuk ke tiket yang sama')
        ->and($isi)->toContain('halaman status tiket');
});
