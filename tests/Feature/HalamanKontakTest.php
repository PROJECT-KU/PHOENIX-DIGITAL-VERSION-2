<?php

use App\Livewire\Pages\Public\Contact\Contact;
use App\Models\CustomerMessage;
use Livewire\Livewire;

/**
 * Halaman /contact: seragam dengan Shop, Bundling, Layanan & Tentang Kami,
 * tanpa mengubah alur formulirnya (honeypot, batas kirim, widget telepon).
 */
it('halaman kontak terbuka dengan kartu judul yang sama seperti shop', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('<div class="page-title ph-page-title">', false)
        ->assertSeeText('Mari terhubung dengan kami')
        ->assertSeeText('0895-0596-7995');
});

it('tiap kanal kontak punya warna dan ubin ikonnya sendiri', function () {
    $warna = collect(Contact::kanal())->pluck('warna');

    expect($warna)->toHaveCount(4)
        ->and($warna->unique())->toHaveCount(4);

    Livewire::test(Contact::class)
        ->assertSeeHtml('<span class="kn-ubin"><i class="bi bi-whatsapp"></i></span>')
        ->assertSeeHtml('style="--c: #2563eb"')
        ->assertSeeHtml('href="'.Contact::wa('Halo Phoenix Digital, saya ingin bertanya.').'"')
        ->assertSeeHtml('href="mailto:'.Contact::EMAIL.'"')
        ->assertSee('Jam Operasional');
});

it('penanda yang dipakai skrip widget telepon tetap ada', function () {
    // Skrip di halaman ini mencari id berikut; mengubahnya mematikan widget
    // nomor telepon dan pengosongan formulir setelah pesan terkirim.
    Livewire::test(Contact::class)
        ->assertSeeHtml('id="ct-form"')
        ->assertSeeHtml('id="ct-phone"')
        ->assertSeeHtml('id="ct-phone-e164"')
        ->assertSeeHtml('wire:ignore')
        ->assertSeeHtml('id="website_url"');
});

it('pesan yang valid tersimpan dan pengirim dikabari', function () {
    Livewire::test(Contact::class)
        ->set('name', 'Sari')
        ->set('email', 'sari@contoh.test')
        ->set('no_telp', '+628123456789')
        ->set('message', 'Halo, saya ingin bertanya soal paket bundling.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('contact-success');

    expect(CustomerMessage::where('email', 'sari@contoh.test')->count())->toBe(1);
});

it('kolom yang salah ditandai tanpa direktif blade di dalam atribut', function () {
    // @error('...') di dalam class membuat Livewire melewati penanda morph-nya.
    Livewire::test(Contact::class)
        ->set('name', '')
        ->set('email', 'bukan-email')
        ->set('no_telp', '08123')
        ->set('message', '')
        ->call('save')
        ->assertHasErrors(['name', 'email', 'no_telp', 'message'])
        ->assertSeeHtml('form-control is-invalid');

    expect(CustomerMessage::count())->toBe(0);
});
