<?php

namespace App\Livewire\Pages\Admin;

use App\Models\User;
use App\Models\Order;
use App\Models\Spending;
use App\Models\GajiKaryawans;
use App\Models\PemesananRsc;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    }

    public function render()
    {
        $authUser = Auth::user();

        // ==========================================
        // DATA UNTUK CARD (BULAN INI)
        // ==========================================
        $awalBulan = now()->startOfMonth()->toDateString();
        $akhirBulan = now()->endOfMonth()->toDateString();

        // User Online 
        $users = User::where('id', '!=', $authUser->id)
            ->whereNotNull('last_seen_at')
            ->get()
            ->map(function ($user) {
                $user->online = $user->last_seen_at->gt(now()->subMinutes(1));
                return $user;
            })
            ->sortByDesc(function ($user) {
                return [$user->online ? 1 : 0, $user->last_seen_at->timestamp];
            })
            ->take(5)
            ->values();

        $totalPemasukanBulanIni = Order::whereBetween('created_at', [$awalBulan, $akhirBulan])
            ->where('status', 'completed')->sum('total');

        $totalPengeluaranBulanIni = Spending::whereBetween('tanggal_transaksi', [$awalBulan, $akhirBulan])
            ->where('status', 'completed')->sum('nominal');

        $totalGajiKaryawanBulanIni = GajiKaryawans::whereBetween('tanggal_transaksi', [$awalBulan, $akhirBulan])
            ->sum('total');

        $totalPemesananRscBulanIni = PemesananRsc::whereBetween('tanggal_pemesanan', [$awalBulan, $akhirBulan])
            ->sum('total');

        // ==========================================
        // DATA UNTUK GRAFIK (SATU TAHUN)
        // ==========================================
        $tahunIni = now()->year;

        $grafikPemasukan = array_fill(0, 12, 0);
        $grafikPengeluaran = array_fill(0, 12, 0);
        $grafikGaji = array_fill(0, 12, 0);
        $grafikRsc = array_fill(0, 12, 0);

        // Pemasukan 1 Tahun
        $orders = Order::whereYear('created_at', $tahunIni)->where('status', 'completed')->get();
        foreach ($orders as $o) {
            $bulan = \Carbon\Carbon::parse($o->created_at)->month - 1;
            $grafikPemasukan[$bulan] += $o->total;
        }

        // Pengeluaran 1 Tahun
        $spendings = Spending::whereYear('tanggal_transaksi', $tahunIni)->where('status', 'completed')->get();
        foreach ($spendings as $s) {
            $bulan = \Carbon\Carbon::parse($s->tanggal_transaksi)->month - 1;
            $grafikPengeluaran[$bulan] += $s->nominal;
        }

        // Gaji Karyawan 1 Tahun
        $gajis = GajiKaryawans::whereYear('tanggal_transaksi', $tahunIni)->get();
        foreach ($gajis as $g) {
            $bulan = \Carbon\Carbon::parse($g->tanggal_transaksi)->month - 1;
            $grafikGaji[$bulan] += $g->total;
        }

        // Pemesanan RSc 1 Tahun
        $rscs = PemesananRsc::whereYear('tanggal_pemesanan', $tahunIni)->get();
        foreach ($rscs as $r) {
            $bulan = \Carbon\Carbon::parse($r->tanggal_pemesanan)->month - 1;
            $grafikRsc[$bulan] += $r->total;
        }

        // ==========================================
        // DATA UNTUK TABEL TERBARU (5 DATA TERAKHIR)
        // ==========================================

        // Ambil 5 Orderan Terbaru
        $recentOrders = Order::latest('created_at')->take(5)->get();

        // Ambil 5 Customer Terbaru
        $recentCustomers = Customer::latest('created_at')->take(5)->get();

        // ==========================================
        // DATA UNTUK VISITOR
        // ==========================================

        // Ambil data dari session, jika tidak ada buat array kosong
        $visitors = session()->get('temp_visitors', []);

        // Simulasi deteksi negara (hanya untuk contoh)
        $country = ['Indonesia', 'USA', 'India', 'Europe'][array_rand(['Indonesia', 'USA', 'India', 'Europe'])];

        // Tambahkan kunjungan baru ke session
        $visitors[] = $country;
        session()->put('temp_visitors', $visitors);

        // Hitung jumlah per negara
        $visitorData = array_count_values($visitors);

        return view('livewire.pages.admin.dashboard', [
            'user' => $authUser,
            'onlineUsers' => $users,

            'totalPemasukan' => number_format($totalPemasukanBulanIni ?? 0, 0, ',', '.'),
            'totalPengeluaran' => number_format($totalPengeluaranBulanIni ?? 0, 0, ',', '.'),
            'totalGajiKaryawan' => number_format($totalGajiKaryawanBulanIni ?? 0, 0, ',', '.'),
            'totalPemesananRsc' => number_format($totalPemesananRscBulanIni ?? 0, 0, ',', '.'),

            'dataGrafikPemasukan' => $grafikPemasukan,
            'dataGrafikPengeluaran' => $grafikPengeluaran,
            'dataGrafikGaji' => $grafikGaji,
            'dataGrafikRsc' => $grafikRsc,

            // Variabel Baru untuk Tabel
            'recentOrders' => $recentOrders,
            'recentCustomers' => $recentCustomers,
            'countries' => array_keys($visitorData),
            'counts' => array_values($visitorData),
        ])
            ->layout('livewire.layout.templateindex');
    }
}
