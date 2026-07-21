<?php

namespace App\Livewire\Pages\Admin\PenyelesaianTask;

use App\Actions\Finance\SyncCashFlowAction;
use App\Actions\Gaji\BonusTaskPeriodeAction;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskCategoryLabel;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskReopened;
use App\Support\PeriodeGaji;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class PenyelesaianTaskList extends Component
{
    use WithFileUploads;

    public $bulan;

    public $tahun;

    public $budget = '';

    public array $preview = [];

    // Modal task (create/edit)
    public bool $showTaskModal = false;

    public $editingTaskId = null;

    public $editingGroupId = null;

    // Multi-penerima: 1 task bisa di-assign ke banyak karyawan (fan-out per orang).
    public $t_user_ids = [];

    public $t_nama = '';

    public $t_deskripsi = '';

    // Kategori & label (label = sub-status per kategori, mis. bug/improvement).
    public $t_category_id = '';

    public $t_label_id = '';

    public $newCategoryName = '';

    public $newLabelName = '';

    public $t_bobot = 'sedang';

    public $t_deadline_mulai = '';

    public $t_deadline_selesai = '';

    // Lampiran task (bisa banyak gambar/file). $newFiles = staging tiap kali pilih,
    // lalu digabung ke $t_files agar bisa menumpuk (pilih berkali-kali).
    public $t_files = [];

    public $newFiles = [];

    // Modal komentar
    public bool $showCommentModal = false;

    public $activeTaskId = null;

    public $newComment = '';

    public $commentFile;

    // Modal "Buka Kembali" (revisi task terkunci) — admin saja.
    public bool $showReopenModal = false;

    public $reopenTaskId = null;

    public $reopen_alasan = '';

    public $reopen_label_id = '';

    public $reopen_deadline = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('manage_task'), 403);

        // Buka langsung modal komentar jika datang dari klik notifikasi (?open_task=ID)
        $openTask = ($id = request('open_task')) ? Task::find($id) : null;

        // Default ke PERIODE GAJI berjalan (bukan bulan kalender): pada tgl 21+
        // keduanya berbeda dan task difiling per periode gaji, jadi task
        // berdeadline 21 Jul (periode Agustus) tak akan tampil bila default Juli.
        $periodeKini = PeriodeGaji::dariTanggal(now());
        $this->bulan = $openTask->periode_bulan ?? $periodeKini['bulan'];
        $this->tahun = $openTask->periode_tahun ?? $periodeKini['tahun'];
        $this->loadBudget();

        if ($openTask) {
            $this->openComments($openTask->id); // set periode + buka modal + recompute
        } else {
            $this->recompute();
        }
    }

    protected function loadBudget(): void
    {
        $pool = BonusTaskPeriodeAction::pool((int) $this->bulan, (int) $this->tahun);
        $this->budget = $pool ? number_format($pool, 0, ',', '.') : '';
    }

    public function updatedBulan(): void
    {
        $this->loadBudget();
        $this->recompute();
    }

    public function updatedTahun(): void
    {
        $this->loadBudget();
        $this->recompute();
    }

    public function updatedBudget(): void
    {
        Setting::set(
            BonusTaskPeriodeAction::settingKey((int) $this->bulan, (int) $this->tahun),
            (int) preg_replace('/[^0-9]/', '', (string) $this->budget)
        );
        $this->recompute();
    }

    // ===== Task CRUD =====
    public function openCreateTask(): void
    {
        $this->reset(['editingTaskId', 'editingGroupId', 't_user_ids', 't_nama', 't_deskripsi', 't_files', 'newFiles',
            't_category_id', 't_label_id', 'newCategoryName', 'newLabelName']);
        $this->t_bobot = 'sedang';
        $this->t_deadline_mulai = now()->toDateString();
        $this->t_deadline_selesai = now()->addDays(7)->toDateString();
        $this->showTaskModal = true;
    }

    public function openEditTask($id): void
    {
        $task = Task::findOrFail($id);
        $group = Task::where('group_id', $task->group_id)->get();

        $this->editingTaskId = $task->id;
        $this->editingGroupId = $task->group_id;
        $this->t_user_ids = $group->pluck('user_id')->map(fn ($v) => (string) $v)->values()->all();
        $this->t_nama = $task->nama;
        $this->t_deskripsi = $task->deskripsi;
        $this->t_category_id = $task->task_category_id ?? '';
        $this->t_label_id = $task->task_category_label_id ?? '';
        $this->newCategoryName = '';
        $this->newLabelName = '';
        $this->t_bobot = $task->bobot;
        $this->t_deadline_mulai = $task->deadline_mulai?->toDateString();
        $this->t_deadline_selesai = $task->deadline_selesai?->toDateString();
        $this->t_files = [];
        $this->newFiles = [];
        $this->showTaskModal = true;
    }

    // Setiap kali file dipilih, gabungkan ke daftar terkumpul agar bisa menumpuk.
    public function updatedNewFiles(): void
    {
        $this->validate(
            ['newFiles.*' => 'file|max:2048'],
            ['newFiles.*.max' => 'Ukuran file maksimal 2 MB (batas server).', 'newFiles.*.file' => 'Berkas tidak valid.']
        );

        foreach ((array) $this->newFiles as $f) {
            if ($f) {
                $this->t_files[] = $f;
            }
        }
        $this->newFiles = [];
    }

    public function removeNewFile($index): void
    {
        if (isset($this->t_files[$index])) {
            unset($this->t_files[$index]);
            $this->t_files = array_values($this->t_files);
        }
    }

    public function removeAttachment($id): void
    {
        $att = \App\Models\TaskAttachment::find($id);
        if ($att) {
            if ($att->path && \Illuminate\Support\Facades\Storage::disk('public')->exists($att->path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($att->path);
            }
            $att->delete();
        }
    }

    // ===== Kategori & Label =====
    // Saat kategori berganti, reset label karena daftarnya milik kategori lain.
    public function updatedTCategoryId(): void
    {
        $this->t_label_id = '';
        $this->newLabelName = '';
    }

    public function addCategory(): void
    {
        $this->validate(
            ['newCategoryName' => 'required|string|max:100|unique:task_categories,nama'],
            [],
            ['newCategoryName' => 'nama kategori']
        );

        $cat = TaskCategory::create(['nama' => trim($this->newCategoryName)]);
        $this->t_category_id = $cat->id;
        $this->t_label_id = '';
        $this->newCategoryName = '';
    }

    public function deleteCategory($id): void
    {
        // Label ikut terhapus (cascade); task lama jadi tanpa kategori (nullOnDelete).
        TaskCategory::whereKey($id)->delete();

        if ((string) $this->t_category_id === (string) $id) {
            $this->t_category_id = '';
            $this->t_label_id = '';
        }
    }

    public function addLabel(): void
    {
        if (blank($this->t_category_id) || $this->t_category_id === '__new__') {
            return;
        }

        $this->validate(
            ['newLabelName' => ['required', 'string', 'max:100',
                Rule::unique('task_category_labels', 'nama')->where('task_category_id', $this->t_category_id)]],
            [],
            ['newLabelName' => 'nama label']
        );

        $label = TaskCategoryLabel::create([
            'task_category_id' => $this->t_category_id,
            'nama' => trim($this->newLabelName),
        ]);
        $this->t_label_id = $label->id;
        $this->newLabelName = '';
    }

    public function deleteLabel($id): void
    {
        TaskCategoryLabel::whereKey($id)->delete();

        if ((string) $this->t_label_id === (string) $id) {
            $this->t_label_id = '';
        }
    }

    public function saveTask(): void
    {
        $this->validate([
            't_user_ids' => ['required', 'array', 'min:1'],
            't_user_ids.*' => 'exists:users,id',
            't_nama' => 'required|string|max:200',
            't_bobot' => 'required|in:ringan,sedang,berat',
            't_deadline_mulai' => 'required|date',
            't_deadline_selesai' => 'required|date|after_or_equal:t_deadline_mulai',
            't_files.*' => 'nullable|file|max:2048',
        ], [
            't_files.*.max' => 'Ukuran file maksimal 2 MB (batas server).',
        ], [
            't_user_ids' => 'karyawan',
            't_nama' => 'nama task',
            't_deadline_selesai' => 'deadline selesai',
        ]);

        $akhir = Carbon::parse($this->t_deadline_selesai);
        $categoryId = ($this->t_category_id && $this->t_category_id !== '__new__') ? $this->t_category_id : null;
        $labelId = ($categoryId && $this->t_label_id) ? $this->t_label_id : null;

        // Periode gaji mengikuti siklus 21 s/d 20 (mis. deadline 25 Jun → periode Juli).
        $periodeTask = PeriodeGaji::dariTanggal($akhir);

        $shared = [
            'periode_bulan' => $periodeTask['bulan'],
            'periode_tahun' => $periodeTask['tahun'],
            'nama' => $this->t_nama,
            'deskripsi' => $this->t_deskripsi,
            'task_category_id' => $categoryId,
            'task_category_label_id' => $labelId,
            'bobot' => $this->t_bobot,
            'deadline_mulai' => $this->t_deadline_mulai,
            'deadline_selesai' => $this->t_deadline_selesai,
            // Catat pembuat untuk tampilan nama di Task Saya. assigned_by tetap
            // NULL (konvensi task admin) agar rantai kelola bawahan tak berubah.
            'created_by' => auth()->id(),
        ];

        $userIds = array_values(array_unique(array_map('intval', $this->t_user_ids)));

        $storedFiles = [];
        foreach ((array) $this->t_files as $file) {
            if ($file) {
                $storedFiles[] = ['path' => $file->store('task_files', 'public'), 'name' => $file->getClientOriginalName()];
            }
        }

        DB::transaction(function () use ($shared, $userIds, $storedFiles) {
            if ($this->editingTaskId) {
                $this->updateGroup($shared, $userIds, $storedFiles);
            } else {
                $this->createGroup($shared, $userIds, $storedFiles);
            }
        });

        $this->reset('t_files');
        $this->showTaskModal = false;
        $this->recompute();
    }

    private function createGroup(array $shared, array $userIds, array $storedFiles): void
    {
        $groupId = (string) Str::uuid();
        foreach ($userIds as $uid) {
            $task = Task::create($shared + ['group_id' => $groupId, 'user_id' => $uid]);
            $this->attachFilesTo($task, $storedFiles);
            $task->karyawan?->notify(new TaskAssigned($task));
            $task->update(['assigned_notified_at' => now()]);
        }
        $this->dispatch('swal-success', message: 'Task dibuat & dikirim ke '.count($userIds).' karyawan.');
    }

    private function updateGroup(array $shared, array $userIds, array $storedFiles): void
    {
        $existing = Task::where('group_id', $this->editingGroupId)->get();
        if ($existing->isEmpty()) {
            return;
        }
        $byUser = $existing->keyBy('user_id');

        foreach ($userIds as $uid) {
            if ($t = $byUser->get($uid)) {
                $t->update($shared);
            } else {
                $t = Task::create($shared + ['group_id' => $this->editingGroupId, 'user_id' => $uid]);
                $t->karyawan?->notify(new TaskAssigned($t));
                $t->update(['assigned_notified_at' => now()]);
            }
            $this->attachFilesTo($t, $storedFiles);
        }

        foreach ($existing as $t) {
            if (! in_array((int) $t->user_id, $userIds, true)) {
                $t->delete();
            }
        }
        $this->dispatch('swal-success', message: 'Task grup berhasil diperbarui.');
    }

    /** @param  array<array{path:string,name:string}>  $storedFiles */
    private function attachFilesTo(Task $task, array $storedFiles): void
    {
        foreach ($storedFiles as $f) {
            \App\Models\TaskAttachment::create([
                'task_id' => $task->id,
                'uploaded_by' => auth()->id(),
                'path' => $f['path'],
                'name' => $f['name'],
            ]);
        }
    }

    public function deleteTask($id): void
    {
        Task::whereKey($id)->delete();
        $this->dispatch('swal-success', message: 'Task dihapus.');
        $this->recompute();
    }

    // ===== Buka Kembali (revisi task terkunci) =====
    public function openReopen($id): void
    {
        $task = Task::findOrFail($id);
        $this->reopenTaskId = $task->id;
        $this->reopen_alasan = '';
        $this->reopen_label_id = $task->task_category_label_id ?? '';
        // Default deadline baru: 7 hari dari hari ini (revisi biasanya butuh waktu baru).
        $this->reopen_deadline = now()->addDays(7)->toDateString();
        $this->showReopenModal = true;
    }

    public function bukaKembali(): void
    {
        $this->validate([
            'reopen_alasan' => 'required|string|max:2000',
            'reopen_deadline' => 'required|date',
        ], [], [
            'reopen_alasan' => 'alasan revisi',
            'reopen_deadline' => 'deadline baru',
        ]);

        $task = Task::findOrFail($this->reopenTaskId);
        $deadline = Carbon::parse($this->reopen_deadline);

        // Label baru hanya diterima bila memang milik kategori task ini.
        $labelId = null;
        if ($this->reopen_label_id) {
            $labelId = TaskCategoryLabel::where('id', $this->reopen_label_id)
                ->where('task_category_id', $task->task_category_id)
                ->value('id');
        }

        // Periode gaji mengikuti siklus 21 s/d 20 (mis. deadline 25 Jun → periode Juli).
        $periodeReopen = PeriodeGaji::dariTanggal($deadline);

        $task->update([
            'progress' => 'dikerjakan',
            'completed_at' => null,
            'deadline_selesai' => $this->reopen_deadline,
            'periode_bulan' => $periodeReopen['bulan'],
            'periode_tahun' => $periodeReopen['tahun'],
            'task_category_label_id' => $labelId,
            // Buka lagi peluang notifikasi deadline/overdue untuk putaran revisi ini.
            'deadline_notified_at' => null,
            'overdue_notified_at' => null,
        ]);

        // Catat alasan sebagai komentar admin bertipe "revisi" (di thread grup).
        TaskComment::create([
            'task_id' => $task->id,
            'group_id' => $task->group_id,
            'user_id' => auth()->id(),
            'body' => $this->reopen_alasan,
            'type' => 'revisi',
        ]);
        // Notifikasi revisi khusus -> bell tampil ikon & teks berbeda dari komentar biasa.
        $task->karyawan?->notify(new TaskReopened($task, auth()->user()->name, $this->reopen_alasan));

        $this->showReopenModal = false;
        $this->reset(['reopenTaskId', 'reopen_alasan', 'reopen_label_id', 'reopen_deadline']);
        $this->dispatch('swal-success', message: 'Task dibuka kembali untuk revisi.');
        $this->recompute();
    }

    // ===== Komentar (admin membalas) =====
    public function openComments($taskId): void
    {
        $this->activeTaskId = $taskId;
        $this->reset(['newComment', 'commentFile']);
        $this->showCommentModal = true;

        // Tandai komentar karyawan (semua sub-task grup) sebagai dibaca admin.
        $task = Task::find($taskId);
        if ($task) {
            $groupTaskIds = Task::where('group_id', $task->group_id)->pluck('id');
            TaskComment::where('group_id', $task->group_id)
                ->whereNull('admin_read_at')
                ->update(['admin_read_at' => now()]);

            // Selaraskan bell: tandai notifikasi seluruh sub-task grup ini dibaca.
            auth()->user()->unreadNotifications()
                ->whereIn('data->task_id', $groupTaskIds->all())
                ->update(['read_at' => now()]);
        }

        // Beri tahu komponen lonceng agar hitung ulang real-time (tanpa refresh).
        $this->dispatch('notifs-read');

        $this->recompute();
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

        $task = Task::findOrFail($this->activeTaskId);

        $path = null;
        $name = null;
        if ($this->commentFile) {
            $path = $this->commentFile->store('task_files', 'public');
            $name = $this->commentFile->getClientOriginalName();
        }

        $body = $this->newComment ?: null;

        TaskComment::create([
            'task_id' => $task->id,
            'group_id' => $task->group_id,
            'user_id' => auth()->id(),
            'body' => $body,
            'file_path' => $path,
            'file_name' => $name,
        ]);

        // Notifikasi ke seluruh pihak terkait (penerima + pemberi + atasan pemberi
        // + admin lain) + @mention via sumber tunggal — konsisten dengan Task Saya.
        app(\App\Actions\Task\NotifyTaskCommentAction::class)->execute($task, auth()->user(), $body);

        $this->reset(['newComment', 'commentFile']);
    }

    /** Pin/lepas-pin komentar grup (bisa banyak pin). */
    public function togglePin($commentId): void
    {
        $task = Task::findOrFail($this->activeTaskId);
        $c = TaskComment::where('group_id', $task->group_id)->find($commentId);
        if ($c) {
            $c->update(['pinned_at' => $c->pinned_at ? null : now()]);
        }
    }

    public function recompute(): void
    {
        $this->preview = app(BonusTaskPeriodeAction::class)->distribusi((int) $this->bulan, (int) $this->tahun);
    }

    public function terapkan(BonusTaskPeriodeAction $action, SyncCashFlowAction $sync): void
    {
        $jumlah = $action->terapkan((int) $this->bulan, (int) $this->tahun, $sync);
        $this->recompute();
        $this->dispatch('swal-success', message: "Bonus penyelesaian task diterapkan ke {$jumlah} draft gaji.");
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

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $activeTask = $this->activeTaskId
            ? Task::with(['groupComments.user.role.permissions', 'karyawan', 'attachments'])->find($this->activeTaskId)
            : null;

        // Nama depan penerima grup untuk @mention di composer komentar.
        $chatMembers = $activeTask
            ? User::whereIn('id', Task::where('group_id', $activeTask->group_id)->pluck('user_id')->all())
                ->pluck('name')
                ->map(fn ($n) => Str::of($n)->trim()->explode(' ')->first())
                ->filter()->unique()->values()->all()
            : [];

        $editAttachments = $this->editingGroupId
            ? \App\Models\TaskAttachment::whereIn('task_id', Task::where('group_id', $this->editingGroupId)->pluck('id'))
                ->latest()->get()->unique('path')->values()
            : collect();

        $reopenTask = $this->reopenTaskId
            ? Task::with('category.labels')->find($this->reopenTaskId)
            : null;

        $categories = TaskCategory::orderBy('nama')->get();
        $categoryLabels = ($this->t_category_id && $this->t_category_id !== '__new__')
            ? TaskCategoryLabel::where('task_category_id', $this->t_category_id)->orderBy('nama')->get()
            : collect();

        // Ukuran tiap grup (jumlah penerima) untuk badge "grup" di baris task.
        $groupSizes = Task::query()->toBase()
            ->selectRaw('group_id, COUNT(*) as c')
            ->groupBy('group_id')
            ->pluck('c', 'group_id')
            ->all();

        return view('livewire.pages.admin.penyelesaian-task.penyelesaian-task-list', [
            'rows' => $this->preview['rows'] ?? [],
            'groupSizes' => $groupSizes,
            'editAttachments' => $editAttachments,
            'reopenTask' => $reopenTask,
            'categories' => $categories,
            'categoryLabels' => $categoryLabels,
            'ringkasan' => [
                'pool' => $this->preview['pool'] ?? 0,
                'terpakai' => $this->preview['terpakai'] ?? 0,
                'sisa' => $this->preview['sisa'] ?? 0,
                'lockedBonus' => $this->preview['lockedBonus'] ?? 0,
            ],
            'users' => User::orderBy('name')->get(['id', 'name']),
            'activeTask' => $activeTask,
            'chatMembers' => $chatMembers,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
        ]);
    }
}
