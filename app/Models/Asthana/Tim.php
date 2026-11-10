<?php

namespace App\Models\Asthana;

use Illuminate\Database\Eloquent\Model;

class Tim extends Model
{
    use KontenSitus;

    protected $table = 'asthana_tim';

    protected $fillable = ['nama', 'jabatan', 'foto', 'linkedin', 'atasan_id', 'tampil', 'urutan'];

    protected $casts = ['tampil' => 'boolean', 'urutan' => 'integer', 'atasan_id' => 'integer'];

    public function atasan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'atasan_id');
    }

    /**
     * Id semua bawahan (langsung maupun tidak) — tidak boleh dipilih sebagai
     * atasan orang ini, kalau tidak bagannya berputar.
     *
     * @return array<int, int>
     */
    public function idBawahan(): array
    {
        $semua = self::query()->pluck('atasan_id', 'id');
        $hasil = [];
        $antre = [$this->id];
        while ($antre) {
            $induk = array_shift($antre);
            foreach ($semua as $id => $atasan) {
                if ((int) $atasan === (int) $induk && ! in_array($id, $hasil, true)) {
                    $hasil[] = $id;
                    $antre[] = $id;
                }
            }
        }

        return $hasil;
    }
}
