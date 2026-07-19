<?php

namespace App\Actions\Gaji;

use App\Actions\Finance\SyncCashFlowAction;
use App\Models\GajiKaryawans;
use App\Models\Setting;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

/**
 * Distribusi bonus penyelesaian task dari SATU pool budget bersama per periode.
 *
 * Sumber task = tabel `tasks` (entitas mandiri). Status bonus tiap task DITURUNKAN
 * otomatis dari Task::bonusStatus() (waktu selesai vs deadline). Pool dibagi ke task
 * milik karyawan yang punya gaji PENDING pada periode, sesuai bobot, lalu dikali
 * persentase status. Gaji COMPLETED dikunci (bonus beku, porsinya keluar dari pool).
 */
class BonusTaskPeriodeAction
{
    // Persentase pembayaran per status. 'terlambat' (selesai melebihi deadline)
    // TIDAK pakai angka ini — dibayar = alokasi × (bobot/4) × faktorTelat(), di distribusi().
    public const STATUS_PERSEN = ['tepat_waktu' => 1.0, 'terlambat' => 0.6, 'tidak_selesai' => 0.0];

    // Masa tenggang: telat s/d 5 hari TIDAK kena penalti tambahan (tetap bobot/4 penuh).
    public const TENGGANG_TELAT_HARI = 5;

    // Penalti bertingkat di ATAS masa tenggang: 'batas hari' => faktor pengali.
    // Lebih dari batas terakhir memakai FAKTOR_TELAT_TERBURUK.
    public const FAKTOR_TELAT = [15 => 0.75];

    public const FAKTOR_TELAT_TERBURUK = 0.5;

    /**
     * Faktor penalti berdasar LAMA keterlambatan (hari), bukan batas periode gaji —
     * batas periode itu kebetulan kalender, jadi telat 2 hari bisa terlihat lebih
     * buruk dari telat 19 hari. Yang adil: makin lama telat, makin kecil.
     *
     * telat 1-5 hari -> 1.00 | 6-15 hari -> 0.75 | >15 hari -> 0.50
     */
    public static function faktorTelat(int $hariTelat): float
    {
        if ($hariTelat <= self::TENGGANG_TELAT_HARI) {
            return 1.0;
        }

        foreach (self::FAKTOR_TELAT as $batas => $faktor) {
            if ($hariTelat <= $batas) {
                return $faktor;
            }
        }

        return self::FAKTOR_TELAT_TERBURUK;
    }

    public static function settingKey(int $bulan, int $tahun): string
    {
        return "task_budget_{$tahun}_{$bulan}";
    }

    public static function pool(int $bulan, int $tahun): int
    {
        return (int) Setting::get(self::settingKey($bulan, $tahun), 0);
    }

    /**
     * Hitung distribusi (read-only) untuk preview di halaman Penyelesaian Task.
     */
    public function distribusi(int $bulan, int $tahun): array
    {
        $pool = self::pool($bulan, $tahun);

        $gajis = GajiKaryawans::with('karyawan')
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->get()
            ->keyBy('nama_karyawan'); // key = user_id

        $tasks = Task::with(['karyawan', 'comments', 'category', 'label'])
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->orderBy('created_at')
            ->get()
            ->groupBy('user_id');

        $lockedBonus = (int) $gajis->where('status', 'completed')->sum('bonus_penyelesaian_task');
        $sisaPool = max($pool - $lockedBonus, 0);

        // Total bobot = task milik user yg punya gaji PENDING & status bukan tidak_ada_info.
        $totalBobot = 0;
        foreach ($tasks as $userId => $list) {
            $gaji = $gajis->get($userId);
            if (! $gaji || $gaji->status === 'completed') {
                continue;
            }
            foreach ($list as $t) {
                if ($t->bonusStatus() === 'tidak_ada_info') {
                    continue;
                }
                $totalBobot += $t->bobotPoin();
            }
        }

        // Kumpulan user yang tampil: yang punya gaji ATAU punya task.
        $userIds = collect($gajis->keys())->merge($tasks->keys())->unique();

        $rows = [];
        $terpakai = 0;

        foreach ($userIds as $userId) {
            $gaji = $gajis->get($userId);
            $list = $tasks->get($userId, collect());
            $locked = $gaji && $gaji->status === 'completed';
            $adaGajiPending = $gaji && $gaji->status !== 'completed';

            $rincian = [];
            $bonus = 0;

            foreach ($list as $t) {
                $status = $t->bonusStatus();
                $dikecualikan = $status === 'tidak_ada_info';
                $alokasi = 0;
                $dibayar = 0;
                $hariTelat = $t->hariTerlambat();
                $faktorTelat = $status === 'terlambat' ? self::faktorTelat($hariTelat) : 1.0;

                // Uang TIDAK dibulatkan — pecahan rupiah dipotong (floor), tidak pernah
                // dibulatkan ke atas. Jadi bonus tak pernah melebihi hak sebenarnya dan
                // total tak pernah melampaui pool; sisa pecahan tetap di 'sisa'.
                if ($adaGajiPending && ! $dikecualikan && $totalBobot > 0) {
                    $alokasi = (int) floor($sisaPool * $t->bobotPoin() / $totalBobot);
                    if ($status === 'terlambat') {
                        // Selesai MELEBIHI deadline: dibayar = alokasi × (bobot/4) × faktor telat.
                        // bobot/4 -> ringan 1/4, sedang 2/4, berat 3/4 dari alokasinya;
                        // faktor telat -> penalti tambahan bila telat > 5 hari.
                        $dibayar = (int) floor($alokasi * $t->bobotPoin() / 4 * $faktorTelat);
                    } else {
                        $dibayar = (int) floor($alokasi * (self::STATUS_PERSEN[$status] ?? 0));
                    }
                }

                // Komentar "baru untuk admin" = komentar dari karyawan yang belum dibaca admin.
                $komentarBaru = $t->comments
                    ->where('user_id', $t->user_id)
                    ->whereNull('admin_read_at')
                    ->count();

                $rincian[] = [
                    'task_id' => $t->id,
                    'group_id' => $t->group_id,
                    'nama' => $t->nama,
                    'kategori' => $t->category?->nama,
                    'label' => $t->label?->nama,
                    'bobot' => $t->bobot,
                    'progress' => $t->progress,
                    'komentar_baru' => $komentarBaru,
                    'bonus_status' => $status,
                    'deadline_mulai' => $t->deadline_mulai,
                    'deadline_selesai' => $t->deadline_selesai,
                    'durasi_hari' => ($t->deadline_mulai && $t->deadline_selesai)
                        ? ((int) $t->deadline_mulai->startOfDay()->diffInDays($t->deadline_selesai->startOfDay()) + 1)
                        : null,
                    'locked_task' => $t->isLocked(),
                    'alokasi' => $alokasi,
                    'dibayar' => $dibayar,
                    'dikecualikan' => $dikecualikan,
                    'hari_terlambat' => $hariTelat,
                    'faktor_telat' => $faktorTelat,
                ];
                $bonus += $dibayar;
            }

            if ($locked) {
                $bonus = (int) $gaji->bonus_penyelesaian_task; // beku
            }

            $terpakai += $bonus;

            $rows[] = [
                'user_id' => $userId,
                'gaji_id' => $gaji?->id,
                'nama' => $gaji?->karyawan->name ?? optional($list->first())->karyawan->name ?? '-',
                'status_gaji' => $gaji?->status ?? 'none',
                'ada_gaji' => (bool) $gaji,
                'locked' => $locked,
                'bonus' => $bonus,
                'tasks' => $rincian,
            ];
        }

        return [
            'pool' => $pool,
            'lockedBonus' => $lockedBonus,
            'sisaPool' => $sisaPool,
            'terpakai' => $terpakai,
            'sisa' => max($pool - $terpakai, 0),
            'rows' => $rows,
        ];
    }

    /**
     * Terapkan hasil distribusi ke gaji PENDING: tulis bonus, hitung ulang total,
     * dan sinkron cash flow. Gaji completed dilewati (dikunci).
     *
     * @return int jumlah gaji yang diperbarui
     */
    public function terapkan(int $bulan, int $tahun, SyncCashFlowAction $sync): int
    {
        $hasil = $this->distribusi($bulan, $tahun);
        $pool = $hasil['pool'];
        $diperbarui = 0;

        DB::transaction(function () use ($hasil, $pool, $sync, &$diperbarui) {
            foreach ($hasil['rows'] as $row) {
                if ($row['locked'] || ! $row['gaji_id']) {
                    continue;
                }

                $gaji = GajiKaryawans::find($row['gaji_id']);
                if (! $gaji) {
                    continue;
                }

                $gaji->bonus_penyelesaian_task = $row['bonus'];
                $gaji->task_budget = $pool;
                $gaji->total = $gaji->hitungTotalDariKolom();
                $gaji->save();

                $sync->execute($gaji, [
                    'amount' => (float) $gaji->total + (float) $gaji->potongan_pinjaman,
                    'type' => 'expense',
                    'date' => $gaji->tanggal_transaksi,
                    'category' => 'Gaji Karyawan',
                    'description' => $gaji->deskripsi ?: 'Pembayaran gaji karyawan',
                ]);

                $diperbarui++;
            }
        });

        return $diperbarui;
    }
}
