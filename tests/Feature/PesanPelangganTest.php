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
function adminPesan(array $izin = ['view_customer_message', 'edit_customer_message', 'delete_customer_message']): \App\Models\User
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
    $this->actingAs(adminPesan());

    // Mendesak: batas 2 jam. Rendah: batas 24 jam.
    $mendesak = pesan(['priority' => 'urgent', 'created_at' => now()->subHours(3)]);
    $rendah = pesan(['priority' => 'low', 'created_at' => now()->subHours(3)]);

    expect($mendesak->lewatBatas())->toBeTrue()
        ->and($rendah->lewatBatas())->toBeFalse();

    // Sudah dibalas = tidak pernah lewat batas lagi.
    $mendesak->update(['replied_at' => now()]);
    expect($mendesak->fresh()->lewatBatas())->toBeFalse();
});

it('saringan lewat batas dan hitungan ringkasannya sejalan', function () {
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
    expect(pesan(['created_at' => now()->subMinutes(20)])->menungguTeks())->toBe('20 menit')
        ->and(pesan(['created_at' => now()->subHours(5)])->menungguTeks())->toBe('5 jam')
        ->and(pesan(['created_at' => now()->subDays(3)])->menungguTeks())->toBe('3 hari');
});
