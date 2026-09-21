<?php

namespace App\Livewire\Pages\Admin;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\GajiKaryawans;
use App\Models\Loan;
use App\Models\Order;
use App\Models\Pengembalian;
use App\Models\User;
use App\Support\PeriodeGaji;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Periode yang sedang dilihat: 0 = berjalan, 1 = satu periode ke belakang,
     * dan seterusnya. Angka, bukan tanggal, supaya nilai dari peramban tidak
     * bisa menyeret kueri ke rentang sembarang.
     */
    #[Url(as: 'periode', keep: false)]
    public int $mundur = 0;

    /** Sejauh mana boleh menengok ke belakang — setahun sudah lebih dari cukup. */
    public const MUNDUR_MAKS = 12;

    public function pilihPeriode(int $mundur): void
    {
        $this->mundur = max(0, min(self::MUNDUR_MAKS, $mundur));
    }

    /**
     * Hitung ulang seluruh angka halaman.
     *
     * Badannya memang kosong: Livewire merender ulang komponen setelah setiap
     * aksi, dan itulah yang dibutuhkan. Ditulis sebagai metode sungguhan —
     * bukan aksi ajaib $refresh — karena wire:target hanya cocok dengan NAMA
     * METODE; dengan $refresh, penanda "Memuat…" tidak akan pernah muncul.
     */
    public function muatUlang(): void {}

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        // Dashboard PERUSAHAAN (admin/finance) vs PRIBADI (karyawan).
        // Konvensi scoping: lihat angka perusahaan butuh view_all_dashboard.
        if (Auth::user()->canViewAll('dashboard')) {
            return $this->renderDashboardPerusahaan();
        }

        return $this->renderDashboardKaryawan();
    }

    /**
     * Unduh slip gaji milik karyawan yang sedang login.
     * visibleTo() memastikan ia tidak bisa mengunduh slip orang lain.
     */
    public function downloadSlip($id)
    {
        // visibleTo() menghormati permission: karyawan hanya bisa unduh slip miliknya.
        $gaji = GajiKaryawans::visibleTo()->with('karyawan')->findOrFail($id);

        $pdf = Pdf::loadView('livewire.pages.admin.gaji-karyawans.slip-gaji-pdf', [
            'g' => $gaji,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'slip-gaji-'.$gaji->id_transaksi.'.pdf'
        );
    }

    /**
     * Dashboard pribadi karyawan: hanya data miliknya sendiri (gaji, pinjaman,
     * slip, profil). Semua query lewat visibleTo() agar tidak bocor.
     */
    protected function renderDashboardKaryawan()
    {
        $user = Auth::user();
        $tahunIni = now()->year;

        // Ringkasan gaji (visibleTo menghormati permission: karyawan -> data sendiri)
        $gajiTerakhir = GajiKaryawans::visibleTo()
            ->orderByDesc('tanggal_transaksi')
            ->first();

        $totalGajiTahunIni = GajiKaryawans::visibleTo()
            ->whereYear('tanggal_transaksi', $tahunIni)
            ->sum('total');

        // Grafik gaji & pengembalian per bulan (1 tahun, data sendiri)
        $grafikGaji = array_fill(0, 12, 0);
        $grafikPengembalian = array_fill(0, 12, 0);

        foreach (GajiKaryawans::visibleTo()->whereYear('tanggal_transaksi', $tahunIni)->get() as $g) {
            $bulan = \Carbon\Carbon::parse($g->tanggal_transaksi)->month - 1;
            $grafikGaji[$bulan] += (float) $g->total;
        }

        foreach (Pengembalian::visibleTo()->whereYear('tanggal_pengembalian', $tahunIni)->get() as $p) {
            $bulan = \Carbon\Carbon::parse($p->tanggal_pengembalian)->month - 1;
            $grafikPengembalian[$bulan] += (float) $p->nominal;
        }

        // Ringkasan pinjaman (data sendiri)
        $totalPinjaman = (float) Loan::visibleTo()->sum('nominal');
        $totalPengembalian = (float) Pengembalian::visibleTo()->sum('nominal');
        $sisaPinjaman = max($totalPinjaman - $totalPengembalian, 0);
        $statusPinjaman = Loan::statusDari($totalPinjaman, $totalPengembalian);

        // Riwayat pinjaman & pengembalian terbaru (gabungan, data sendiri)
        $riwayatPinjaman = Loan::visibleTo()->orderByDesc('tanggal_peminjam')->take(5)->get()
            ->map(fn (Loan $l) => [
                'jenis' => 'Peminjaman',
                'tanggal' => $l->tanggal_peminjam_formatted,
                'tanggal_sort' => optional($l->tanggal_peminjam)->timestamp ?? 0,
                'nominal' => $l->nominal_formatted,
                'arah' => 'keluar',
                'deskripsi' => $l->deskripsi,
            ]);

        $riwayatPengembalian = Pengembalian::visibleTo()->orderByDesc('tanggal_pengembalian')->take(5)->get()
            ->map(fn (Pengembalian $p) => [
                'jenis' => 'Pengembalian',
                'tanggal' => $p->tanggal_pengembalian_formatted,
                'tanggal_sort' => optional($p->tanggal_pengembalian)->timestamp ?? 0,
                'nominal' => $p->nominal_formatted,
                'arah' => 'masuk',
                'deskripsi' => $p->deskripsi,
            ]);

        $riwayat = $riwayatPinjaman->concat($riwayatPengembalian)
            ->sortByDesc('tanggal_sort')
            ->take(6)
            ->values();

        // Pekerjaan hari ini. Dasbor karyawan sebelumnya hanya memuat gaji,
        // pinjaman, dan data diri — semua yang dibuka sebulan sekali —
        // sementara task dan presensi, dua hal yang dibuka tiap hari, tidak
        // ada sama sekali dan harus dicari lewat menu.
        $taskSaya = \App\Models\Task::visibleTo($user)
            ->where('user_id', $user->id)
            ->where('progress', '!=', 'selesai')
            ->orderByRaw('CASE WHEN deadline_selesai < ? THEN 0 ELSE 1 END', [today()->toDateString()])
            ->orderBy('deadline_selesai')
            ->take(5)
            ->get();

        $taskRingkas = [
            'belum' => (int) \App\Models\Task::where('user_id', $user->id)->where('progress', 'belum')->count(),
            'dikerjakan' => (int) \App\Models\Task::where('user_id', $user->id)->where('progress', 'dikerjakan')->count(),
            'telat' => (int) \App\Models\Task::where('user_id', $user->id)
                ->where('progress', '!=', 'selesai')
                ->whereDate('deadline_selesai', '<', today())
                ->count(),
        ];

        $presensiHariIni = \App\Models\Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();

        return view('livewire.pages.admin.dashboard-karyawan', [
            'user' => $user,
            'detail' => $user->detail,
            'gajiTerakhir' => $gajiTerakhir,
            'totalGajiTahunIni' => $totalGajiTahunIni,
            'dataGrafikGaji' => $grafikGaji,
            'dataGrafikPengembalian' => $grafikPengembalian,
            'totalPinjaman' => $totalPinjaman,
            'totalPengembalian' => $totalPengembalian,
            'sisaPinjaman' => $sisaPinjaman,
            'statusPinjaman' => $statusPinjaman,
            'riwayat' => $riwayat,
            'tahunIni' => $tahunIni,
            'taskSaya' => $taskSaya,
            'taskRingkas' => $taskRingkas,
            'presensiHariIni' => $presensiHariIni,
            'agendaSaya' => $this->agendaSaya(),
        ])->layout('livewire.layout.templateindex');
    }

    /**
     * Kegiatan mendatang yang menyangkut pengguna ini.
     *
     * Dipakai kedua dasbor: pengurus pun ikut diundang rapat, jadi tidak ada
     * alasan agendanya hanya muncul bagi karyawan.
     */
    protected function agendaSaya(int $batas = 5)
    {
        return \App\Models\Kegiatan::agenda(Auth::id())->limit($batas)->get();
    }

    protected function renderDashboardPerusahaan()
    {
        $authUser = Auth::user();

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

        // ==========================================
        // DATA KEUANGAN — DISINKRONKAN DENGAN CASHFLOW
        // Semua angka bersumber dari tabel cash_flows (type income/expense)
        // agar identik dengan halaman Cashflow.
        // ==========================================
        $tahunIni = now()->year;

        // Periode BERJALAN mengikuti siklus gaji 21-20 (seragam dgn Cashflow &
        // Gaji), bukan bulan kalender. Pada tgl 21+ periode ini beda dari kalender.
        // Mundur N periode dari yang berjalan. subMonthNoOverflow lewat
        // PeriodeGaji::mulai() sendiri, jadi akhir bulan tidak melompat.
        $mundur = max(0, min(self::MUNDUR_MAKS, $this->mundur));
        $acuan = PeriodeGaji::mulai(
            PeriodeGaji::dariTanggal(now())['bulan'],
            PeriodeGaji::dariTanggal(now())['tahun']
        );
        for ($i = 0; $i < $mundur; $i++) {
            $acuan = PeriodeGaji::mulai(
                PeriodeGaji::dariTanggal($acuan->copy()->subDay())['bulan'],
                PeriodeGaji::dariTanggal($acuan->copy()->subDay())['tahun']
            );
        }

        $per = PeriodeGaji::dariTanggal($acuan);
        $perMulai = PeriodeGaji::mulai($per['bulan'], $per['tahun']);
        $perAkhirEks = PeriodeGaji::akhir($per['bulan'], $per['tahun'])->copy()->addDay()->startOfDay();
        $periodeLabel = PeriodeGaji::label($per['bulan'], $per['tahun']);

        // Total periode berjalan (mengikuti cashflow: berdasarkan transaction_date)
        $cfPeriode = fn ($type) => (float) CashFlow::where('type', $type)
            ->where('transaction_date', '>=', $perMulai)
            ->where('transaction_date', '<', $perAkhirEks)
            ->sum('amount');

        $totalPemasukanBulanIni = $cfPeriode('income');
        $totalPengeluaranBulanIni = $cfPeriode('expense');
        $saldoBersihBulanIni = $totalPemasukanBulanIni - $totalPengeluaranBulanIni;

        // Total kode unik: pesanan dibayar pada PERIODE BERJALAN (tgl bayar,
        // fallback tgl dibuat).
        $paidStatuses = ['paid', 'processing', 'completed'];
        $totalKodeUnikBulanIni = (float) Order::whereIn('status', $paidStatuses)
            ->whereRaw('COALESCE(paid_at, created_at) >= ?', [$perMulai->toDateTimeString()])
            ->whereRaw('COALESCE(paid_at, created_at) < ?', [$perAkhirEks->toDateTimeString()])
            ->sum('unique_code');

        // Pemasukan HARI INI = pesanan yg UANGNYA sudah masuk hari ini —
        // status paid/processing/completed, berdasarkan tgl bayar. Sengaja TIDAK
        // menunggu 'completed': order paid/processing (mis. cek plagiasi 5x yg
        // masih berjalan) tetap dihitung karena uangnya sudah diterima.
        $pendapatanHariIni = (float) Order::whereIn('status', ['paid', 'processing', 'completed'])
            ->whereRaw('DATE(COALESCE(paid_at, created_at)) = ?', [today()->toDateString()])
            ->sum('total');

        // Kode unik HARI INI + perbandingan dgn KEMARIN untuk keduanya.
        // Dihitung di App\Support\PerbandinganHarian — SATU sumber bersama
        // dengan Cash Flow, supaya dua layar itu mustahil menampilkan angka
        // berbeda untuk hal yang sama.
        $banding = \App\Support\PerbandinganHarian::ringkas();
        $kodeUnikHariIni = $banding['kode_unik']['hari_ini'];

        // ==========================================
        // GRAFIK 1 TAHUN — income vs expense per bulan (dari cashflow)
        // ==========================================
        $grafikPemasukan = array_fill(0, 12, 0);
        $grafikPengeluaran = array_fill(0, 12, 0);

        $monthly = CashFlow::whereYear('transaction_date', $tahunIni)
            ->selectRaw('MONTH(transaction_date) as bln, type, SUM(amount) as total')
            ->groupBy('bln', 'type')
            ->get();

        foreach ($monthly as $row) {
            $idx = (int) $row->bln - 1;
            if ($idx < 0 || $idx > 11) {
                continue;
            }
            if ($row->type === 'income') {
                $grafikPemasukan[$idx] = (float) $row->total;
            } else {
                $grafikPengeluaran[$idx] = (float) $row->total;
            }
        }

        // ==========================================
        // DATA UNTUK TABEL TERBARU (5 DATA TERAKHIR)
        // ==========================================

        // Ambil 5 Orderan Terbaru
        $recentOrders = Order::latest('created_at')->take(5)->get();

        // Ambil 5 Customer Terbaru
        $recentCustomers = Customer::latest('created_at')->take(5)->get();

        // Pembanding periode LALU untuk saldo, pemasukan, dan pengeluaran.
        // Ketiganya angka periode, jadi pembandingnya pun periode — bukan
        // "kemarin" seperti kartu harian. Lihat App\Support\PerbandinganPeriode.
        $bandingPeriode = \App\Support\PerbandinganPeriode::ringkas($perMulai);

        // Angka OPERASIONAL: pekerjaan & uang yang menunggu tindakan. Tidak
        // ikut berubah saat periode digeser — yang menunggu tindakan selalu
        // "sekarang", bukan bulan lalu.
        //
        // KECUALI task telat: ia mengikuti periode 21–20 yang dipilih. Task
        // dari periode lalu yang tak pernah ditutup hanya menumpuk di kartu
        // ini dan menenggelamkan yang memang perlu dikejar periode ini.
        $operasional = [
            'langganan' => \App\Support\RingkasanOperasional::langganan(),
            'jasa' => \App\Support\RingkasanOperasional::antreanJasa(),
            'pesanan' => \App\Support\RingkasanOperasional::pesananMenunggu(),
            'task' => \App\Support\RingkasanOperasional::taskTerlambat($perMulai, $perAkhirEks),
            'stok' => \App\Support\RingkasanOperasional::stokAkun(),
        ];

        // Daftar langganan yang perlu dihubungi — bukan hitungannya saja.
        // Selalu keadaan SEKARANG, seperti kartu "Butuh Perhatian": yang mau
        // habis tidak bisa dihubungi di periode lampau.
        $langgananSegera = \App\Support\RingkasanOperasional::langgananSegeraRinci();

        // Laba, jumlah transaksi, dan pelanggan baru vs kembali. Ketiganya
        // dihitung dari himpunan pesanan yang sama supaya kartunya tidak
        // saling bertentangan.
        $laba = \App\Support\RingkasanLaba::banding($perMulai);
        $penjualan = \App\Support\RingkasanPenjualan::banding($perMulai);
        $kecepatanJasa = \App\Support\KecepatanJasa::periode($perMulai, $perAkhirEks);

        // Lima pengeluaran terakhir. "Catat Pengeluaran" sudah jadi aksi cepat
        // di atas, tetapi hasil catatannya tidak pernah terlihat lagi dari
        // dasbor — jadi pengeluaran ganda baru ketahuan di layar Cash Flow.
        $pengeluaranTerbaru = \App\Models\Spending::with('penginput')
            ->orderByDesc('tanggal_transaksi')
            ->take(5)
            ->get();

        $produkTerlaris = \App\Support\RingkasanOperasional::produkTerlaris($perMulai, $perAkhirEks);
        $pemasukanHarian = \App\Support\RingkasanOperasional::pemasukanHarian($perMulai, $perAkhirEks);

        // Pemakaian promo pada periode berjalan (flash sale, kode promo,
        // promo otomatis, kode rujukan) — lihat App\Support\RingkasanPromo.
        $promoDipakai = \App\Support\RingkasanPromo::periode($perMulai, $perAkhirEks);
        $promoRincian = \App\Support\RingkasanPromo::rincian($perMulai, $perAkhirEks);

        // ==========================================
        // DISTRIBUSI METODE PEMBAYARAN (data nyata dari tabel orders)
        // ==========================================
        $paymentData = Order::query()
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, COUNT(*) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->pluck('total', 'payment_method');

        $labelMap = [
            'qris' => 'QRIS',
            'qris_dinamis' => 'QRIS Dinamis',
            'qris_statis' => 'QRIS Statis',
            'bank_transfer' => 'Transfer Bank',
            'transfer' => 'Transfer',
            'va' => 'Virtual Account',
            'ewallet' => 'E-Wallet',
            'cash' => 'Tunai',
            'manual' => 'Manual',
        ];

        $paymentLabels = $paymentData->keys()
            ->map(fn ($m) => $labelMap[strtolower((string) $m)] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', (string) $m)))
            ->all();
        $paymentCounts = $paymentData->values()->map(fn ($v) => (int) $v)->all();

        return view('livewire.pages.admin.dashboard', [
            'user' => $authUser,
            'onlineUsers' => $users,

            'totalPemasukan' => number_format($totalPemasukanBulanIni ?? 0, 0, ',', '.'),
            'totalPengeluaran' => number_format($totalPengeluaranBulanIni ?? 0, 0, ',', '.'),
            'totalKodeUnik' => number_format($totalKodeUnikBulanIni ?? 0, 0, ',', '.'),
            'kodeUnikHariIni' => number_format($kodeUnikHariIni ?? 0, 0, ',', '.'),
            // Angka mentah (bukan string terformat) — komponen <x-banding-harian>
            // yang mengurus format & kalimat naik/turunnya.
            'bandingPendapatan' => $banding['pendapatan'],
            'bandingKodeUnik' => $banding['kode_unik'],
            'saldoBersih' => number_format($saldoBersihBulanIni ?? 0, 0, ',', '.'),
            'saldoIsNegatif' => $saldoBersihBulanIni < 0,
            'pendapatanHariIni' => number_format($pendapatanHariIni ?? 0, 0, ',', '.'),
            'periodeLabel' => $periodeLabel,

            'dataGrafikPemasukan' => $grafikPemasukan,
            'dataGrafikPengeluaran' => $grafikPengeluaran,

            // Variabel Baru untuk Tabel
            'recentOrders' => $recentOrders,
            'recentCustomers' => $recentCustomers,
            'bandingPeriode' => $bandingPeriode,
            'operasional' => $operasional,
            'produkTerlaris' => $produkTerlaris,
            'pemasukanHarian' => $pemasukanHarian,
            'mundur' => $mundur,
            'periodeBerjalan' => $mundur === 0,
            'laba' => $laba,
            'penjualan' => $penjualan,
            'kecepatanJasa' => $kecepatanJasa,
            'langgananSegera' => $langgananSegera,
            'pengeluaranTerbaru' => $pengeluaranTerbaru,
            'promoDipakai' => $promoDipakai,
            'promoRincian' => $promoRincian,
            'countries' => $paymentLabels,
            'counts' => $paymentCounts,
            'agendaSaya' => $this->agendaSaya(),
        ])
            ->layout('livewire.layout.templateindex');
    }
}
