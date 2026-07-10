<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PemesananRsc extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pemesanan_rsc';

    protected $fillable = [
        'id_transaksi',
        'nama_camp',
        'batch_camp',
        'tanggal_mulai_camp',
        'tanggal_akhir_camp',
        'nama_pembeli',
        'telp_pembeli',
        'jumlah_pemesanan',
        'metode_harga',
        'tanggal_pemesanan',
        'tanggal_berakhir',
        'harga_satuan',
        'total',
        'akun',
        'username',
        'password',
        'link_akses',
        'pic',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
    ];

    // relationship
    public function cashFlow(): MorphOne
    {
        return $this->morphOne(CashFlow::class, 'sourceable');
    }

    /**
     * Relasi ke tabel users.
     * Nama method dibuat 'nama_karyawan' supaya kompatibel dengan kode
     * yang memanggil ->nama_karyawan->name
     */
    public function users()
    {
        return $this->belongsTo(User::class, 'pic', 'id');
    }

    public function dataakun()
    {
        return $this->belongsTo(DataAkun::class, 'akun', 'id');
    }

    // Helper / accessor untuk menampilkan nama karyawan
    public function getNamaTextAttribute(): string
    {
        return $this->nama?->name ?? '-tidak ada-';
    }

    public function getTotalFormattedAttribute(): string
    {
        return 'Rp '.number_format($this->total ?? 0, 0, ',', '.');
    }

    public function getTanggalPemesananFormattedAttribute(): string
    {
        return Carbon::parse($this->tanggal_pemesanan)->translatedFormat('d F Y');
    }

    public function getTanggalBerakhirFormattedAttribute(): string
    {
        return Carbon::parse($this->tanggal_berakhir)->translatedFormat('d F Y');
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at->translatedFormat('d F Y H:i');
    }

    // Scope filter status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope filter tanggal
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_pemesanan', [$startDate, $endDate]);
    }

    // Scope filter karyawan
    public function scopeByPembeli($query, $pembeli)
    {
        return $query->where('nama', $pembeli);
    }

    // Scope filter id transaksi
    public function scopeByIDTransaksi($query, $idtransaksi)
    {
        return $query->where('id_transaksi', $idtransaksi);
    }

    /**
     * Susun item invoice (dikelompokkan per nama_camp+batch_camp) untuk daftar
     * batch terpilih (format "nama|batch"). Sumber tunggal dipakai preview & unduh
     * invoice agar konsisten. Untuk batch metode "per_akun" disertakan daftar akun
     * (nama + harga per akun) sehingga invoice menampilkan basis akun, bukan peserta.
     *
     * @param  array<string>  $selectedBatches
     */
    public static function invoiceItemsFor(array $selectedBatches)
    {
        $conditions = collect($selectedBatches)->map(function ($item) {
            [$nama, $batch] = explode('|', $item);

            return ['nama_camp' => $nama, 'batch_camp' => $batch];
        });

        $items = static::query()
            ->where(function ($query) use ($conditions) {
                foreach ($conditions as $condition) {
                    $query->orWhere(function ($q) use ($condition) {
                        $q->where('nama_camp', $condition['nama_camp'])
                            ->where('batch_camp', $condition['batch_camp']);
                    });
                }
            })
            ->selectRaw('
                nama_camp,
                batch_camp,
                MIN(tanggal_mulai_camp) as periode_mulai,
                MAX(tanggal_akhir_camp) as periode_akhir,
                COUNT(id) as total_peserta,
                SUM(total) as total_harga,
                MAX(harga_satuan) as harga_satuan,
                MAX(metode_harga) as metode_harga,
                MAX(jumlah_pemesanan) as jumlah_pemesanan,
                MAX(akun) as akun_utama_id
            ')
            ->groupBy('nama_camp', 'batch_camp')
            ->orderBy('nama_camp')
            ->orderBy('batch_camp')
            ->get();

        $toInt = fn ($v) => (int) preg_replace('/[^0-9]/', '', (string) $v);

        $items->each(function ($item) use ($toInt) {
            $item->metode_harga = $item->metode_harga ?: 'per_peserta';
            $item->bulan = max((int) $item->jumlah_pemesanan, 1);

            if ($item->metode_harga !== 'per_akun') {
                $item->akun_list = null;
                $item->jumlah_akun = 0;

                return;
            }

            $akunList = [];
            $mainNama = optional(DataAkun::find($item->akun_utama_id))->nama_akun ?? 'Akun utama';
            $akunList[] = ['nama' => $mainNama, 'harga' => (int) $item->harga_satuan];

            $tambahan = RscBatchAkun::where('nama_camp', $item->nama_camp)
                ->where('batch_camp', $item->batch_camp)
                ->orderBy('id')
                ->get();
            foreach ($tambahan as $t) {
                $d = DataAkun::find($t->akun_id);
                $akunList[] = [
                    'nama' => $t->nama_akun ?: (optional($d)->nama_akun ?? 'Akun'),
                    'harga' => $toInt(optional($d)->harga_satuan),
                ];
            }

            $item->akun_list = $akunList;
            $item->jumlah_akun = count($akunList);
        });

        return $items;
    }
}
