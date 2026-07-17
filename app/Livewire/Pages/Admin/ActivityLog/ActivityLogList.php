<?php

namespace App\Livewire\Pages\Admin\ActivityLog;

use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogList extends Component
{
    use WithPagination;

    public $search = '';

    public $filterType = '';   // '' | error | auth

    public $filterLevel = '';  // '' | error | warning | info

    public $filterTanggal = ''; // YYYY-MM-DD

    public ?ActivityLog $selected = null;

    public $showDetail = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterLevel()
    {
        $this->resetPage();
    }

    public function updatingFilterTanggal()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterType', 'filterLevel', 'filterTanggal']);
        $this->resetPage();
    }

    public function lihat($id)
    {
        $this->selected = ActivityLog::find($id);
        $this->showDetail = $this->selected !== null;
    }

    public function tutupDetail()
    {
        $this->showDetail = false;
        $this->selected = null;
    }

    /** Hapus log lebih lama dari N hari (default 30). */
    #[On('clear-activity-log')]
    public function clearOld($hari = 30)
    {
        if (! auth()->user()->hasPermission('clear_activity_log')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin membersihkan log.');

            return;
        }

        $hari = max(0, (int) $hari);
        $batas = now()->subDays($hari);

        $jumlah = ActivityLog::where('created_at', '<', $batas)->count();
        ActivityLog::where('created_at', '<', $batas)->delete();

        $this->resetPage();
        $this->dispatch('swal-success', message: $jumlah.' log lebih lama dari '.$hari.' hari berhasil dihapus.');
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $query = ActivityLog::query()->latest();

        if ($this->filterType === 'slow') {
            // "Request Lambat" bukan tipe tersendiri, melainkan kunjungan/error
            // apa pun yang durasinya >= 1 detik.
            $query->where('duration_ms', '>=', 1000);
        } elseif ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }
        if ($this->filterLevel !== '') {
            $query->where('level', $this->filterLevel);
        }
        if ($this->filterTanggal !== '') {
            $query->whereDate('created_at', $this->filterTanggal);
        }
        if ($this->search !== '') {
            $s = '%'.$this->search.'%';
            $query->where(function ($q) use ($s) {
                $q->where('message', 'like', $s)
                    ->orWhere('exception_class', 'like', $s)
                    ->orWhere('url', 'like', $s)
                    ->orWhere('user_name', 'like', $s)
                    ->orWhere('ip', 'like', $s);
            });
        }

        $logs = $query->paginate(15);

        return view('livewire.pages.admin.activity-log.activity-log-list', [
            'logs' => $logs,
            'totalError' => ActivityLog::where('type', 'error')->count(),
            'totalVisit' => ActivityLog::where('type', 'visit')->count(),
            'totalSlow' => ActivityLog::where('duration_ms', '>=', 1000)->count(),
            'totalHariIni' => ActivityLog::whereDate('created_at', now()->toDateString())->count(),
        ]);
    }
}
