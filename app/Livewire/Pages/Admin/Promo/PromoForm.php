<?php

namespace App\Livewire\Pages\Admin\Promo;

use App\Models\Product;
use App\Models\Promo;
use Exception;
use Illuminate\Support\Str;
use Livewire\Component;

class PromoForm extends Component
{
    public $mode = 'create';

    public ?Promo $promo = null;

    public $nama_promo = '';

    public $kode_promo = '';

    public $deskripsi = '';

    public $tipe_promo = 'flash_sale';

    public $tipe_diskon = 'persen';

    public $diskon_member_persen = "";

    public $diskon_member_nominal = "";

    public $diskon_non_member_persen = "";

    public $diskon_non_member_nominal = "";

    public $untuk_member = 'semua';

    public $untuk_pembeli_pertama = false;

    /** Kosong = tanpa batas kuota. */
    public $kuota = '';

    public $min_pembelian = "";

    public $mulai_promo = '';

    public $selesai_promo = '';

    public $is_active = true;

    public $prioritas = 50;

    public $can_stack_with_other = true;

    public $can_stack_with_referral = true;

    public $can_stack_with_points = true;

    public $show_on_homepage = false;

    public $badge_text = '';

    public $selectedProducts = [];

    public $allProducts = [];

    public function mount($promo = null)
    {
        if ($promo) {
            $this->promo = $promo;
            $this->nama_promo = $promo->nama_promo;
            $this->kode_promo = $promo->kode_promo ?? '';
            $this->deskripsi = $promo->deskripsi ?? '';
            $this->tipe_promo = $promo->tipe_promo;
            $this->tipe_diskon = $promo->tipe_diskon;
            $this->diskon_member_persen = $promo->diskon_member_persen;
            $this->diskon_member_nominal = $promo->diskon_member_nominal ? number_format($promo->diskon_member_nominal, 0, '', '.') : '';
            $this->diskon_non_member_persen = $promo->diskon_non_member_persen;
            $this->diskon_non_member_nominal = $promo->diskon_non_member_nominal ? number_format($promo->diskon_non_member_nominal, 0, '', '.') : '';
            $this->untuk_member = $promo->untuk_member;
            $this->untuk_pembeli_pertama = $promo->untuk_pembeli_pertama;
            $this->kuota = $promo->kuota !== null ? number_format($promo->kuota, 0, '', '.') : '';
            $this->min_pembelian = $promo->min_pembelian ? number_format($promo->min_pembelian, 0, '', '.') : '';
            $this->mulai_promo = $promo->mulai_promo ? $promo->mulai_promo->format('Y-m-d\TH:i') : '';
            $this->selesai_promo = $promo->selesai_promo ? $promo->selesai_promo->format('Y-m-d\TH:i') : '';
            $this->is_active = $promo->is_active;
            $this->prioritas = $promo->prioritas;
            $this->can_stack_with_other = $promo->can_stack_with_other;
            $this->can_stack_with_referral = $promo->can_stack_with_referral;
            $this->can_stack_with_points = $promo->can_stack_with_points;
            $this->show_on_homepage = $promo->show_on_homepage;
            $this->badge_text = $promo->badge_text ?? '';
            $this->mode = 'edit';
            $this->selectedProducts = $promo->products->pluck('id')->toArray();
        } else {
            $this->mulai_promo = '';
            $this->selesai_promo = '';
        }

        $this->allProducts = Product::orderBy('nama_akun')->get();
    }

    public function updatedTipePromo()
    {
        if ($this->tipe_promo === 'flash_sale') {
            $this->kode_promo = '';
        }
    }

    /**
     * Dua aksi terpisah, BUKAN satu tombol yang menebak keadaan.
     *
     * Checkbox produk memakai wire:model deferred, jadi saat admin menyalakan
     * saklar satu per satu server tidak tahu apa-apa dan tidak me-render ulang —
     * label tombol "Pilih/Hapus Semua" jadi basi & terbalik. Dua tombol tetap
     * benar apa pun keadaan saklarnya.
     *
     * Nilai dijadikan string karena checkbox Livewire mengirim value sbg string.
     */
    public function pilihSemuaProduk(): void
    {
        $this->selectedProducts = collect($this->allProducts)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function hapusSemuaProduk(): void
    {
        $this->selectedProducts = [];
    }

    public function rules()
    {
        $rules = [
            'nama_promo' => 'required|string|max:255',
            'tipe_promo' => 'required|in:flash_sale,kode_promo,referral_bonus,auto_promo',
            'tipe_diskon' => 'required|in:persen,nominal',
            'untuk_member' => 'required|in:semua,member_only,non_member_only',
            'mulai_promo' => 'required|date',
            'selesai_promo' => 'required|date|after:mulai_promo',
        ];

        return $rules;
    }

    private function cleanNumber($value)
    {
        return empty($value) ? 0 : (int) str_replace('.', '', $value);
    }

    /**
     * Kuota kosong = TANPA BATAS (null) — sengaja TIDAK pakai cleanNumber(),
     * karena itu memulangkan 0 untuk input kosong, dan kuota 0 berarti promo
     * langsung mati. Beda arti yang berbahaya.
     */
    private function cleanKuota($value): ?int
    {
        $v = trim((string) $value);

        return $v === '' ? null : max((int) str_replace('.', '', $v), 0);
    }

    public function save()
    {
        $this->validate();

        if ($this->tipe_promo === 'kode_promo' && empty($this->kode_promo)) {
            session()->flash('error', 'Kode promo wajib diisi untuk tipe Kode Promo');

            return;
        }

        if ($this->mode == 'create') {
            $this->createPromo();
        } else {
            $this->editPromo();
        }
    }

    public function createPromo()
    {
        try {
            $data = [
                'id' => Str::uuid(),
                'nama_promo' => $this->nama_promo,
                'kode_promo' => $this->tipe_promo === 'kode_promo' ? strtoupper($this->kode_promo) : null,
                'deskripsi' => $this->deskripsi,
                'tipe_promo' => $this->tipe_promo,
                'tipe_diskon' => $this->tipe_diskon,
                'diskon_member_persen' => (int) $this->diskon_member_persen,
                'diskon_member_nominal' => $this->cleanNumber($this->diskon_member_nominal),
                'diskon_non_member_persen' => (int) $this->diskon_non_member_persen,
                'diskon_non_member_nominal' => $this->cleanNumber($this->diskon_non_member_nominal),
                'untuk_member' => $this->untuk_member,
                'untuk_pembeli_pertama' => $this->untuk_pembeli_pertama,
                'kuota' => $this->cleanKuota($this->kuota),
                'min_pembelian' => $this->cleanNumber($this->min_pembelian),
                'mulai_promo' => $this->mulai_promo,
                'selesai_promo' => $this->selesai_promo,
                'is_active' => $this->is_active,
                'prioritas' => $this->prioritas,
                'can_stack_with_other' => $this->can_stack_with_other,
                'can_stack_with_referral' => $this->can_stack_with_referral,
                'can_stack_with_points' => $this->can_stack_with_points,
                'show_on_homepage' => $this->show_on_homepage,
                'badge_text' => $this->badge_text,
            ];

            $promo = Promo::create($data);

            // Sync products
            $promo->products()->sync($this->selectedProducts);

            session()->flash('successCreated', 'Promo berhasil dibuat');

            return redirect()->route('admin.promo.index');
        } catch (\Exception $e) {
            session()->flash('errorCreated', 'Gagal menambahkan Data Promo: ' . $e->getMessage());
        }
    }

    public function editPromo()
    {
        try {
            $data = [
                'nama_promo' => $this->nama_promo,
                'kode_promo' => $this->tipe_promo === 'kode_promo' ? strtoupper($this->kode_promo) : null,
                'deskripsi' => $this->deskripsi,
                'tipe_promo' => $this->tipe_promo,
                'tipe_diskon' => $this->tipe_diskon,
                'diskon_member_persen' => (int) $this->diskon_member_persen,
                'diskon_member_nominal' => $this->cleanNumber($this->diskon_member_nominal),
                'diskon_non_member_persen' => (int) $this->diskon_non_member_persen,
                'diskon_non_member_nominal' => $this->cleanNumber($this->diskon_non_member_nominal),
                'untuk_member' => $this->untuk_member,
                'untuk_pembeli_pertama' => $this->untuk_pembeli_pertama,
                'kuota' => $this->cleanKuota($this->kuota),
                'min_pembelian' => $this->cleanNumber($this->min_pembelian),
                'mulai_promo' => $this->mulai_promo,
                'selesai_promo' => $this->selesai_promo,
                'is_active' => $this->is_active,
                'prioritas' => $this->prioritas,
                'can_stack_with_other' => $this->can_stack_with_other,
                'can_stack_with_referral' => $this->can_stack_with_referral,
                'can_stack_with_points' => $this->can_stack_with_points,
                'show_on_homepage' => $this->show_on_homepage,
                'badge_text' => $this->badge_text,
            ];

            $this->promo->update($data);

            // Sync products
            $this->promo->products()->sync($this->selectedProducts);

            session()->flash('successUpdated', 'Promo berhasil diupdate');

            return redirect()->route('admin.promo.index');
        } catch (\Exception $e) {
            session()->flash('errorUpdated', 'Gagal mengupdate Data Promo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Info "sudah terpakai" hanya ada artinya saat mengedit promo yang sudah
        // berjalan. Dihitung dari pesanan nyata, jadi selalu ikut kondisi terkini.
        return view('livewire.pages.admin.promo.promo-form', [
            'kuotaTerpakai' => $this->promo?->kuotaTerpakai(),
            'kuotaSisa' => $this->promo?->sisaKuota(),
        ]);
    }
}
