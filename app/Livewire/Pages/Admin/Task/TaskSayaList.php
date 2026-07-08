<?php

namespace App\Livewire\Pages\Admin\Task;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskCommented;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class TaskSayaList extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public $activeTaskId = null;

    public $newComment = '';

    public $commentFile;

    // Filter periode (pola sama seperti Pengeluaran/Spending).
    public $bulan = '';

    public $tahun = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('view_task'), 403);

        // Default ke periode berjalan (seperti pengeluaran) — tidak diubah oleh open_task.
        $this->bulan = now()->month;
        $this->tahun = now()->year;

        // Buka langsung modal task jika datang dari klik notifikasi (?open_task=ID).
        // Filter tetap periode berjalan; modal memuat task langsung walau di luar periode.
        $id = request('open_task');
        if ($id && Task::visibleTo()->whereKey($id)->exists()) {
            $this->openTask($id);
        }
    }

    public function resetFilter(): void
    {
        $this->bulan = '';
        $this->tahun = '';
    }

    protected function daftarBulan(): array
    {
        return [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    }

    protected function daftarTahun(): array
    {
        $y = (int) now()->year;

        return range($y, $y - 5);
    }

    public function openTask($id): void
    {
        // visibleTo memastikan hanya task miliknya (kecuali admin).
        $task = Task::visibleTo()->findOrFail($id);
        $this->activeTaskId = $task->id;
        $this->reset(['newComment', 'commentFile']);
        $this->showModal = true;

        // Tandai komentar admin sebagai sudah dibaca karyawan -> badge hilang.
        TaskComment::where('task_id', $task->id)
            ->where('user_id', '!=', $task->user_id)
            ->whereNull('karyawan_read_at')
            ->update(['karyawan_read_at' => now()]);
    }

    public function mulaiKerjakan($id): void
    {
        $task = Task::visibleTo()->findOrFail($id);
        if ($task->isLocked()) {
            return;
        }
        $task->update(['progress' => 'dikerjakan']);
    }

    public function tandaiSelesai($id): void
    {
        $task = Task::visibleTo()->findOrFail($id);
        // Harus "dikerjakan" dulu — tak boleh langsung selesai dari "belum".
        if ($task->isLocked() || $task->progress !== 'dikerjakan') {
            return;
        }
        $task->update(['progress' => 'selesai', 'completed_at' => now()]);
        $this->dispatch('swal-success', message: 'Task ditandai selesai.');
    }

    public function addComment(): void
    {
        $this->validate([
            'newComment' => 'nullable|string|max:2000',
            'commentFile' => 'nullable|file|max:5120',
        ]);

        if (blank($this->newComment) && ! $this->commentFile) {
            return;
        }

        $task = Task::visibleTo()->findOrFail($this->activeTaskId);

        $path = null;
        $name = null;
        if ($this->commentFile) {
            $path = $this->commentFile->store('task_files', 'public');
            $name = $this->commentFile->getClientOriginalName();
        }

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body' => $this->newComment ?: null,
            'file_path' => $path,
            'file_name' => $name,
        ]);

        // Notifikasi ke admin (yang punya manage_task), kecuali penulis sendiri
        $admins = User::whereHas('role.permissions', fn ($q) => $q->where('name', 'manage_task'))
            ->where('id', '!=', auth()->id())
            ->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new TaskCommented($task, auth()->user()->name, true));
        }

        $this->reset(['newComment', 'commentFile']);
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $tasks = Task::visibleTo()
            ->with(['comments', 'category', 'label'])
            ->when($this->bulan, fn ($q) => $q->where('periode_bulan', $this->bulan))
            ->when($this->tahun, fn ($q) => $q->where('periode_tahun', $this->tahun))
            ->orderByRaw("CASE WHEN progress <> 'selesai' AND DATE(deadline_selesai) = CURDATE() THEN 0 ELSE 1 END")
            ->orderByRaw("FIELD(progress,'dikerjakan','belum','selesai')")
            ->latest()
            ->get();

        $activeTask = $this->activeTaskId
            ? Task::visibleTo()->with(['comments.user', 'attachments'])->find($this->activeTaskId)
            : null;

        return view('livewire.pages.admin.task.task-saya-list', [
            'tasks' => $tasks,
            'activeTask' => $activeTask,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
        ]);
    }
}
