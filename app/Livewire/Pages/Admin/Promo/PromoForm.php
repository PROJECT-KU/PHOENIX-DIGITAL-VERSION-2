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

    public $diskon_member_persen = 0;

    public $diskon_member_nominal = 0;

    public $diskon_non_member_persen = 0;

    public $diskon_non_member_nominal = 0;

    public $untuk_member = 'semua';

    public $untuk_pembeli_pertama = false;

    public $min_pembelian = 0;

    public $mulai_promo = '';

    public $selesai_promo = '';

    public $is_active = true;

    public $prioritas = 50;

    public $can_stack_with_other = true;

    public $can_stack_with_referral = true;

    public $can_stack_with_points = true;

    public $show_on_homepage = false;

    public $badge_text = '';

    public $badge_color = '#ff6b6b';

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
            $this->diskon_member_nominal = $promo->diskon_member_nominal;
            $this->diskon_non_member_persen = $promo->diskon_non_member_persen;
            $this->diskon_non_member_nominal = $promo->diskon_non_member_nominal;
            $this->untuk_member = $promo->untuk_member;
            $this->untuk_pembeli_pertama = $promo->untuk_pembeli_pertama;
            $this->min_pembelian = $promo->min_pembelian;
            $this->mulai_promo = $promo->mulai_promo->format('Y-m-d\TH:i');
            $this->selesai_promo = $promo->selesai_promo->format('Y-m-d\TH:i');
            $this->is_active = $promo->is_active;
            $this->prioritas = $promo->prioritas;
            $this->can_stack_with_other = $promo->can_stack_with_other;
            $this->can_stack_with_referral = $promo->can_stack_with_referral;
            $this->can_stack_with_points = $promo->can_stack_with_points;
            $this->show_on_homepage = $promo->show_on_homepage;
            $this->badge_text = $promo->badge_text ?? '';
            $this->badge_color = $promo->badge_color ?? '#FF6B6B';
            $this->mode = 'edit';
            $this->selectedProducts = $promo->products->pluck('id')->toArray();
        }
        $this->allProducts = Product::orderBy('nama_akun')->get();
        $this->mulai_promo = now()->format('Y-m-d\TH:i');
        $this->selesai_promo = now()->addDays(7)->format('Y-m-d\TH:i');
    }

    public function updatedTipePromo()
    {
        if ($this->tipe_promo === 'flash_sale') {
            $this->kode_promo = '';
        }
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
                'diskon_member_persen' => $this->diskon_member_persen,
                'diskon_member_nominal' => $this->diskon_member_nominal,
                'diskon_non_member_persen' => $this->diskon_non_member_persen,
                'diskon_non_member_nominal' => $this->diskon_non_member_nominal,
                'untuk_member' => $this->untuk_member,
                'untuk_pembeli_pertama' => $this->untuk_pembeli_pertama,
                'min_pembelian' => $this->min_pembelian,
                'mulai_promo' => $this->mulai_promo,
                'selesai_promo' => $this->selesai_promo,
                'is_active' => $this->is_active,
                'prioritas' => $this->prioritas,
                'can_stack_with_other' => $this->can_stack_with_other,
                'can_stack_with_referral' => $this->can_stack_with_referral,
                'can_stack_with_points' => $this->can_stack_with_points,
                'show_on_homepage' => $this->show_on_homepage,
                'badge_text' => $this->badge_text,
                'badge_color' => $this->badge_color,
            ];

            $promo = Promo::create($data);

            // Sync products
            $promo->products()->sync($this->selectedProducts);

            session()->flash('success', 'Promo berhasil dibuat');

            return redirect()->route('admin.promo.index');

        } catch (Exception $e) {
            dump($e->getMessage());
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
                'diskon_member_persen' => $this->diskon_member_persen,
                'diskon_member_nominal' => $this->diskon_member_nominal,
                'diskon_non_member_persen' => $this->diskon_non_member_persen,
                'diskon_non_member_nominal' => $this->diskon_non_member_nominal,
                'untuk_member' => $this->untuk_member,
                'untuk_pembeli_pertama' => $this->untuk_pembeli_pertama,
                'min_pembelian' => $this->min_pembelian,
                'mulai_promo' => $this->mulai_promo,
                'selesai_promo' => $this->selesai_promo,
                'is_active' => $this->is_active,
                'prioritas' => $this->prioritas,
                'can_stack_with_other' => $this->can_stack_with_other,
                'can_stack_with_referral' => $this->can_stack_with_referral,
                'can_stack_with_points' => $this->can_stack_with_points,
                'show_on_homepage' => $this->show_on_homepage,
                'badge_text' => $this->badge_text,
                'badge_color' => $this->badge_color,
            ];

            $this->promo->update($data);

            // Sync products
            $this->promo->products()->sync($this->selectedProducts);

            session()->flash('success', 'Promo berhasil diupdate');

            return redirect()->route('admin.promo.index');

        } catch (Exception $e) {
            dump($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.promo.promo-form');
    }
}
