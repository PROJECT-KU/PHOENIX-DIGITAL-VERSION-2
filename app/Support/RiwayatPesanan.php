<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderRiwayat;
use App\Models\OrderUpload;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak pesanan toko: siapa melakukan apa, kapan.
 *
 * Dipasang lewat event model (AppServiceProvider), jadi setiap jalur yang
 * mengubah pesanan — admin, checkout pelanggan, poller QRIS, bot Turnitin —
 * ikut tercatat tanpa menyentuh alurnya. Pencatatan GAGAL-AMAN: kesalahan apa
 * pun di sini ditelan dan dilaporkan, tidak pernah menggagalkan checkout atau
 * pemrosesan pesanan.
 *
 * user_id NULL berarti bukan admin yang sedang login: pelanggan, bot, atau
 * tugas terjadwal. Tampil sebagai "Sistem".
 */
class RiwayatPesanan
{
    public const STATUS = [
        'draft' => 'Draft', 'pending' => 'Menunggu bayar', 'paid' => 'Dibayar', 'processing' => 'Diproses',
        'completed' => 'Selesai', 'cancelled' => 'Dibatalkan',
    ];

    private static ?bool $siap = null;

    public static function siap(): bool
    {
        return self::$siap ??= rescue(fn () => Schema::hasTable('order_riwayat'), false, false);
    }

    /** Untuk tes: lupakan hasil pemeriksaan tabel. */
    public static function lupakan(): void
    {
        self::$siap = null;
    }

    public static function catat(string $orderId, string $aksi, ?string $keterangan = null): void
    {
        if (! self::siap()) {
            return;
        }

        try {
            OrderRiwayat::create([
                'order_id' => $orderId,
                'user_id' => auth()->id(),
                'aksi' => $aksi,
                'keterangan' => $keterangan ? mb_substr($keterangan, 0, 500) : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Riwayat pesanan gagal dicatat: '.$e->getMessage());
        }
    }

    public static function pasang(): void
    {
        Order::created(function (Order $o) {
            self::catat($o->id, 'dibuat', auth()->check()
                ? 'Dibuat admin · '.(self::STATUS[$o->status] ?? $o->status)
                : 'Dibuat pelanggan lewat toko online');
        });

        Order::updated(function (Order $o) {
            if ($o->wasChanged('status')) {
                $dari = self::STATUS[$o->getOriginal('status')] ?? $o->getOriginal('status');
                $ke = self::STATUS[$o->status] ?? $o->status;
                self::catat($o->id, 'status', "Status {$dari} → {$ke}");
            }
            if ($o->wasChanged('bukti_pembayaran') && $o->bukti_pembayaran) {
                self::catat($o->id, 'bukti', $o->getOriginal('bukti_pembayaran') ? 'Bukti pembayaran diganti' : 'Bukti pembayaran diunggah');
            }
        });

        OrderItem::updated(function (OrderItem $it) {
            $nama = $it->product_name ?: 'akun';

            if ($it->wasChanged('delivery_status') && $it->delivery_status === 'delivered') {
                self::catat($it->order_id, 'dikirim', "Akun {$nama} dikirim ke pelanggan");
            }
            if ($it->wasChanged('subscription_status') && $it->getOriginal('subscription_status')) {
                self::catat($it->order_id, 'langganan', "Langganan {$nama}: {$it->getOriginal('subscription_status')} → {$it->subscription_status}");
            }
            if ($it->wasChanged('end_date') && $it->end_date && $it->getOriginal('end_date')) {
                self::catat($it->order_id, 'masa', "Masa aktif {$nama} diubah sampai ".$it->end_date->locale('id')->translatedFormat('d M Y'));
            }
            if ($it->wasChanged('habis_notified_at') && $it->habis_notified_at) {
                self::catat($it->order_id, 'wa', "Pelanggan diberi tahu akun {$nama} sudah habis");
            }
            if ($it->wasChanged('ingat_perpanjang_at') && $it->ingat_perpanjang_at) {
                self::catat($it->order_id, 'wa', "Pelanggan diingatkan akun {$nama} segera habis");
            }
            if ($it->wasChanged('diperpanjang_oleh_item_id') && $it->diperpanjang_oleh_item_id) {
                $nomor = OrderItem::with('order:id,order_number')->find($it->diperpanjang_oleh_item_id)?->order?->order_number;
                self::catat($it->order_id, 'langganan', "Akun {$nama} diperpanjang".($nomor ? " lewat pesanan {$nomor}" : ''));
            }
            if ($it->wasChanged('processing_notes') && blank($it->processing_notes) && filled($it->getOriginal('processing_notes'))) {
                self::catat($it->order_id, 'catatan', "Catatan internal {$nama} diselesaikan");
            }
        });

        OrderUpload::created(function (OrderUpload $up) {
            self::catat($up->order_id, 'jasa', 'Naskah diunggah: '.$up->nama_asli);
        });

        OrderUpload::updated(function (OrderUpload $up) {
            if (! $up->wasChanged('status')) {
                return;
            }
            $oleh = $up->dikerjakan_oleh === 'bot' ? ' oleh bot' : '';
            $teks = match ($up->status) {
                'diproses' => 'Mulai diperiksa'.$oleh,
                'selesai' => 'Hasil diunggah'.$oleh,
                'dibatalkan' => 'Pengecekan dibatalkan',
                'menunggu' => 'Kembali ke antrean',
                default => null,
            };
            if ($teks) {
                self::catat($up->order_id, 'jasa', $teks.': '.$up->nama_asli);
            }
        });
    }

    /**
     * Jejak untuk ditampilkan, terbaru di atas.
     *
     * Pesanan sebelum fitur ini ada tidak punya jejak, jadi beberapa peristiwa
     * penting disusun dari kolom pesanan sendiri (dibuat, dibayar, akun
     * diproses) — ditandai 'dari_data' supaya tidak disangka jejak asli.
     *
     * @return Collection<int, array{waktu: Carbon, aksi: string, teks: string, oleh: string, dari_data: bool}>
     */
    public static function untuk(Order $order): Collection
    {
        $asli = self::siap()
            ? $order->riwayat()->with('user:id,name')->get()->map(fn (OrderRiwayat $r) => [
                'waktu' => $r->created_at,
                'aksi' => $r->aksi,
                'teks' => $r->keterangan ?: $r->aksi,
                'oleh' => $r->user->name ?? 'Sistem',
                'dari_data' => false,
            ])
            : collect();

        $susun = collect();
        if (! $asli->contains('aksi', 'dibuat') && $order->created_at) {
            $susun->push(['waktu' => $order->created_at, 'aksi' => 'dibuat', 'teks' => 'Pesanan dibuat', 'oleh' => '—', 'dari_data' => true]);
        }
        $adaBayar = $asli->contains(fn ($r) => $r['aksi'] === 'status' && str_contains($r['teks'], '→ Dibayar'));
        if (! $adaBayar && $order->paid_at) {
            $susun->push(['waktu' => $order->paid_at, 'aksi' => 'status', 'teks' => 'Pembayaran diterima', 'oleh' => '—', 'dari_data' => true]);
        }
        if (! $asli->contains('aksi', 'dikirim')) {
            foreach ($order->items as $it) {
                if ($it->processed_at) {
                    $susun->push([
                        'waktu' => Carbon::parse($it->processed_at), 'aksi' => 'dikirim',
                        'teks' => 'Akun '.($it->product_name ?: 'akun').' diproses',
                        'oleh' => $it->processedBy->name ?? '—', 'dari_data' => true,
                    ]);
                }
            }
        }

        return $asli->concat($susun)->sortByDesc(fn ($r) => $r['waktu']?->getTimestamp() ?? 0)->values();
    }
}
