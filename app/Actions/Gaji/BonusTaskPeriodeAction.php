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
    public const STATUS_PERSEN = ['tepat_waktu' => 1.0, 'terlambat' => 0.6, 'tidak_selesai' => 0.0];

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

                if ($adaGajiPending && ! $dikecualikan && $totalBobot > 0) {
                    $alokasi = (int) round($sisaPool * $t->bobotPoin() / $totalBobot);
                    $dibayar = (int) round($alokasi * (self::STATUS_PERSEN[$status] ?? 0));
                }

                // Komentar "baru untuk admin" = komentar dari karyawan yang belum dibaca admin.
                $komentarBaru = $t->comments
                    ->where('user_id', $t->user_id)
                    ->whereNull('admin_read_at')
                    ->count();

                $rincian[] = [
                    'task_id' => $t->id,
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
