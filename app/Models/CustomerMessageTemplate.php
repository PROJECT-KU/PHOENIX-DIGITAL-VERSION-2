<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Template balasan cepat helpdesk. */
class CustomerMessageTemplate extends Model
{
    protected $fillable = ['nama', 'isi', 'urutan', 'status_baru', 'kategori_baru'];

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    /** Ringkasan aksi ikutan template, untuk ditampilkan di tombolnya. */
    public function aksiTeks(): ?string
    {
        $bagian = [];

        if ($this->status_baru) {
            $bagian[] = \App\Livewire\Pages\Admin\Message\CustomerMessageList::STATUS[$this->status_baru] ?? $this->status_baru;
        }

        if ($this->kategori_baru) {
            $bagian[] = config('helpdesk.kategori')[$this->kategori_baru] ?? $this->kategori_baru;
        }

        return $bagian ? implode(' · ', $bagian) : null;
    }

    /** Isi template dengan data tiket: {nama} dan {tiket}. */
    public function untuk(CustomerMessage $pesan): string
    {
        return strtr($this->isi, [
            '{nama}' => $pesan->name,
            '{tiket}' => $pesan->ticket,
        ]);
    }
}
