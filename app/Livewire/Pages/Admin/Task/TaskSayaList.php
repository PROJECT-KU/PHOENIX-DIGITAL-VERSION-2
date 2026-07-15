<?php

namespace App\Livewire\Pages\Admin\Task;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskCategory;
use App\Models\TaskCategoryLabel;
use App\Models\TaskComment;
use App\Models\TaskCommentRead;
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

class TaskSayaList extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    // Modal diskusi grup (komentar dipusatkan di level folder/grup).
    public bool $showGroupChat = false;

    public $activeTaskId = null;

    public $newComment = '';

    public $commentFile;

    // Filter periode (pola sama seperti Pengeluaran/Spending).
    public $bulan = '';

    public $tahun = '';

    // Mode periode (sama seperti Cashflow): 'kalender' (1 s/d akhir bulan) atau
    // 'siklus20' (tgl 20 bulan terpilih s/d tgl 19 bulan berikutnya).
    public $modePeriode = 'kalender';

    // ===== Modal beri/edit task ke bawahan (khusus atasan ber-izin assign_task) =====
    public bool $showTaskModal = false;

    public $editingTaskId = null;

    // Grup yang sedang diedit (semua sub-task berbagi group_id).
    public $editingGroupId = null;

    // Multi-penerima: 1 task bisa di-assign ke banyak orang (fan-out per orang).
    public $t_user_ids = [];

    public $t_nama = '';

    public $t_deskripsi = '';

    public $t_category_id = '';

    public $t_label_id = '';

    // Untuk tambah kategori/label baru dari popup picker.
    public $newCategoryName = '';

    public $newLabelName = '';

    public $t_bobot = 'sedang';

    public $t_deadline_mulai = '';

    public $t_deadline_selesai = '';

    // Lampiran task: $newFiles = staging tiap pilih, digabung ke $t_files agar menumpuk.
    public $t_files = [];

    public $newFiles = [];

    // ===== Modal "Buka Kembali" (revisi task terkunci) — pemberi task saja =====
    public bool $showReopenModal = false;

    public $reopenTaskId = null;

    public $reopen_alasan = '';

    public $reopen_label_id = '';

    public $reopen_deadline = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermission('view_task'), 403);

        // Default ke periode berjalan (seperti pengeluaran) — tidak diubah oleh open_task.
        $this->bulan = now()->month;
        $this->tahun = now()->year;

        // Buka langsung dari klik notifikasi bell (?open_task=ID).
        // Task GRUP -> langsung ke Diskusi Grup (kolom komentar). Task solo -> detail
        // (yang komentarnya inline). Filter tetap periode berjalan.
        $id = request('open_task');
        if ($id && ($task = Task::visibleTo()->whereKey($id)->first())) {
            $isGroup = Task::where('group_id', $task->group_id)->count() > 1;
            if ($isGroup) {
                $this->openGroupChat($task->group_id);
            } else {
                $this->openTask($id);
            }
        }
    }

    public function resetFilter(): void
    {
        $this->bulan = '';
        $this->tahun = '';
        $this->modePeriode = 'kalender';
    }

    /**
     * Apakah filter memakai siklus 20-19 (butuh bulan terpilih). Sama seperti Cashflow.
     */
    protected function usesSiklus(): bool
    {
        return $this->modePeriode === 'siklus20' && ! empty($this->bulan);
    }

    /**
     * Rentang siklus untuk bulan/tahun terpilih:
     * [mulai = tgl 20 bulan terpilih, akhirEksklusif = tgl 20 bulan berikutnya].
     * Jadi mencakup tgl 20 bulan terpilih s/d tgl 19 bulan berikutnya (persis Cashflow).
     *
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    protected function siklusRange(): array
    {
        $tahun = (int) ($this->tahun ?: now()->year);
        $mulai = Carbon::create($tahun, (int) $this->bulan, 20)->startOfDay();
        $akhirEksklusif = $mulai->copy()->addMonthNoOverflow();

        return [$mulai, $akhirEksklusif];
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
        $this->showGroupChat = false;

        // Hanya task SOLO yang menampilkan komentar inline di detail -> tandai dibaca.
        // Task GRUP: komentar ada di "Diskusi Grup", jadi membuka sub-card (untuk
        // progres) TIDAK menandai diskusi dibaca — hanya openGroupChat yang menandai.
        $isSolo = Task::where('group_id', $task->group_id)->count() <= 1;
        if ($isSolo) {
            $this->markGroupSeen($task);
        }
    }

    /** Buka diskusi grup (komentar dipusatkan di folder). */
    public function openGroupChat($groupId): void
    {
        // Anchor: sub-task milik saya di grup ini, atau sub-task pertama yang terlihat.
        $task = Task::visibleTo()->where('group_id', $groupId)->where('user_id', auth()->id())->first()
            ?? Task::visibleTo()->where('group_id', $groupId)->firstOrFail();

        $this->activeTaskId = $task->id;
        $this->reset(['newComment', 'commentFile']);
        $this->showGroupChat = true;
        $this->showModal = false;

        $this->markGroupSeen($task);
    }

    /** Tandai diskusi grup sudah dilihat: status baca + bersihkan notifikasi grup. */
    protected function markGroupSeen(Task $task): void
    {
        TaskCommentRead::updateOrCreate(
            ['group_id' => $task->group_id, 'user_id' => auth()->id()],
            ['task_id' => $task->id, 'last_read_at' => now()]
        );

        $groupTaskIds = Task::where('group_id', $task->group_id)->pluck('id')->all();
        auth()->user()->unreadNotifications()
            ->whereIn('data->task_id', $groupTaskIds)
            ->update(['read_at' => now()]);

        // Beri tahu komponen lonceng agar hitung ulang real-time (tanpa refresh).
        $this->dispatch('notifs-read');
    }

    public function mulaiKerjakan($id): void
    {
        $task = Task::visibleTo()->findOrFail($id);
        // Hanya pemilik task yang boleh mengubah progres (bukan atasan pemberi).
        if ($task->user_id !== auth()->id() || $task->isLocked()) {
            return;
        }
        $task->update(['progress' => 'dikerjakan']);
    }

    public function tandaiSelesai($id): void
    {
        $task = Task::visibleTo()->findOrFail($id);
        // Hanya pemilik task, harus "dikerjakan" dulu — tak boleh langsung selesai.
        if ($task->user_id !== auth()->id() || $task->isLocked() || $task->progress !== 'dikerjakan') {
            return;
        }
        // Bonus ikut periode gaji saat task BENAR-BENAR selesai, bukan deadline-nya.
        // Mis. deadline 10 Jun tapi selesai 30 Jun -> masuk gaji Juli (21 Jun–20 Jul),
        // sebab gaji Juni sudah dibayar 20 Jun & bonusnya akan terkunci di 0.
        $selesaiPada = now();
        $periode = PeriodeGaji::dariTanggal($selesaiPada);

        $task->update([
            'progress' => 'selesai',
            'completed_at' => $selesaiPada,
            'periode_bulan' => $periode['bulan'],
            'periode_tahun' => $periode['tahun'],
        ]);
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

        $body = $this->newComment ?: null;

        TaskComment::create([
            'task_id' => $task->id,
            'group_id' => $task->group_id,
            'user_id' => auth()->id(),
            'body' => $body,
            'file_path' => $path,
            'file_name' => $name,
        ]);

        $this->notifyComment($task, $body);

        $this->reset(['newComment', 'commentFile']);
    }

    /** Pin/lepas-pin komentar (grup) — bisa banyak pin agar tak tenggelam. */
    public function togglePin($commentId): void
    {
        $task = Task::visibleTo()->findOrFail($this->activeTaskId);

        $c = TaskComment::where('group_id', $task->group_id)->find($commentId);
        if ($c) {
            $c->update(['pinned_at' => $c->pinned_at ? null : now()]);
        }
    }

    /**
     * Kirim notifikasi komentar ke seluruh pihak yang terlibat, mengikuti rantai
     * hierarki, kecuali penulis komentar:
     *  - Penerima task (user_id) + pemberi (assigned_by) + SEMUA atasan pemberi
     *    (rantai ke atas) → tautan ke halaman "Task Saya".
     *  - Admin ber-izin manage_task → tautan ke "Penyelesaian Task".
     * Kedua himpunan dibuat saling lepas (admin tidak dobel).
     */
    protected function notifyComment(Task $task, ?string $body = null): void
    {
        app(\App\Actions\Task\NotifyTaskCommentAction::class)->execute($task, auth()->user(), $body);
    }

    // ===== Beri task ke bawahan (atasan ber-izin assign_task) =====

    /** Task hanya bisa diberikan ke user yang ada di downline si atasan. */
    protected function assignableIds(): array
    {
        return auth()->user()->bawahanIds();
    }

    /**
     * ID pemberi task yang boleh SAYA kelola (edit/hapus/reopen): diri saya
     * sendiri + seluruh bawahan saya. Jadi atasan bisa mengelola task yang
     * diberikan oleh siapa pun di bawahnya. Task admin (assigned_by NULL) tak masuk.
     *
     * @return array<int>
     */
    protected function manageableGiverIds(): array
    {
        $u = auth()->user();

        return array_merge([$u->id], $u->bawahanIds());
    }

    /** Ambil task yang boleh saya kelola atau 403/404. */
    protected function findManageableTask($id): Task
    {
        return Task::whereIn('assigned_by', $this->manageableGiverIds())->findOrFail($id);
    }

    protected function ensureCanAssign(): void
    {
        abort_unless(auth()->user()?->canAssignTask(), 403);
    }

    public function openCreateTask(): void
    {
        $this->ensureCanAssign();

        $this->reset(['editingTaskId', 'editingGroupId', 't_user_ids', 't_nama', 't_deskripsi', 't_files', 'newFiles',
            't_category_id', 't_label_id', 'newCategoryName', 'newLabelName']);
        $this->t_bobot = 'sedang';
        $this->t_deadline_mulai = now()->toDateString();
        $this->t_deadline_selesai = now()->addDays(7)->toDateString();
        $this->showTaskModal = true;
    }

    public function openEditTask($id): void
    {
        $this->ensureCanAssign();

        // Boleh mengedit task yang diberikan oleh diri sendiri ATAU bawahannya.
        $task = $this->findManageableTask($id);

        // Edit seluruh GRUP: muat semua sub-task (semua penerima) dalam grup ini.
        $group = Task::where('group_id', $task->group_id)->get();

        $this->editingTaskId = $task->id;
        $this->editingGroupId = $task->group_id;
        $this->t_user_ids = $group->pluck('user_id')->map(fn ($v) => (string) $v)->values()->all();
        $this->t_nama = $task->nama;
        $this->t_deskripsi = $task->deskripsi;
        $this->t_category_id = $task->task_category_id ?? '';
        $this->t_label_id = $task->task_category_label_id ?? '';
        $this->t_bobot = $task->bobot;
        $this->t_deadline_mulai = $task->deadline_mulai?->toDateString();
        $this->t_deadline_selesai = $task->deadline_selesai?->toDateString();
        $this->t_files = [];
        $this->newFiles = [];
        $this->showTaskModal = true;
    }

    // Ganti kategori -> reset label (daftar label milik kategori lain).
    public function updatedTCategoryId(): void
    {
        $this->t_label_id = '';
    }

    // ===== Kelola Kategori & Label dari popup picker (tambah/hapus) =====
    public function addCategory(): void
    {
        $this->ensureCanAssign();

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
        $this->ensureCanAssign();

        // Label ikut terhapus (cascade); task lama jadi tanpa kategori (nullOnDelete).
        TaskCategory::whereKey($id)->delete();

        if ((string) $this->t_category_id === (string) $id) {
            $this->t_category_id = '';
            $this->t_label_id = '';
        }
    }

    public function addLabel(): void
    {
        $this->ensureCanAssign();

        if (blank($this->t_category_id)) {
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
        $this->ensureCanAssign();

        TaskCategoryLabel::whereKey($id)->delete();

        if ((string) $this->t_label_id === (string) $id) {
            $this->t_label_id = '';
        }
    }

    // Tiap file dipilih, gabungkan ke daftar terkumpul agar bisa menumpuk.
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
        $this->ensureCanAssign();

        $att = TaskAttachment::whereHas('task', fn ($q) => $q->whereIn('assigned_by', $this->manageableGiverIds()))->find($id);
        if (! $att || ! $att->task) {
            return;
        }

        // Lampiran disalin ke tiap sub-task grup -> hapus semua salinannya sekaligus.
        $groupTaskIds = Task::where('group_id', $att->task->group_id)->pluck('id');
        TaskAttachment::whereIn('task_id', $groupTaskIds)->where('path', $att->path)->delete();

        if ($att->path && \Illuminate\Support\Facades\Storage::disk('public')->exists($att->path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($att->path);
        }
    }

    public function saveTask(): void
    {
        $this->ensureCanAssign();

        $this->validate([
            't_user_ids' => ['required', 'array', 'min:1'],
            't_user_ids.*' => ['exists:users,id', function ($attr, $value, $fail) {
                if (! in_array((int) $value, $this->assignableIds(), true)) {
                    $fail('Anda hanya bisa memberi task kepada bawahan Anda.');
                }
            }],
            't_nama' => 'required|string|max:200',
            't_bobot' => 'required|in:ringan,sedang,berat',
            't_deadline_mulai' => 'required|date',
            't_deadline_selesai' => 'required|date|after_or_equal:t_deadline_mulai',
            't_files.*' => 'nullable|file|max:2048',
        ], [
            't_files.*.max' => 'Ukuran file maksimal 2 MB (batas server).',
        ], [
            't_user_ids' => 'penerima',
            't_nama' => 'nama task',
            't_deadline_selesai' => 'deadline selesai',
        ]);

        $akhir = Carbon::parse($this->t_deadline_selesai);
        $categoryId = $this->t_category_id ?: null;
        $labelId = ($categoryId && $this->t_label_id) ? $this->t_label_id : null;

        // Task baru belum selesai -> periode dipatok dari deadline sbg perkiraan,
        // nanti ditandai ulang dari tanggal selesai saat tandaiSelesai().
        $periodeTask = PeriodeGaji::dariTanggal($akhir);

        // Field bersama untuk semua sub-task dalam grup.
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
        ];

        $userIds = array_values(array_unique(array_map('intval', $this->t_user_ids)));

        // Simpan file sekali; di-link ke tiap sub-task saat fan-out.
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
    }

    /** Buat grup baru: satu sub-task per penerima, berbagi group_id. */
    private function createGroup(array $shared, array $userIds, array $storedFiles): void
    {
        $groupId = (string) Str::uuid();

        foreach ($userIds as $uid) {
            $task = Task::create($shared + [
                'group_id' => $groupId,
                'user_id' => $uid,
                'assigned_by' => auth()->id(),
            ]);
            $this->attachFilesTo($task, $storedFiles);
            $task->karyawan?->notify(new TaskAssigned($task));
            $task->update(['assigned_notified_at' => now()]);
        }

        $this->dispatch('swal-success', message: 'Task dibuat & dikirim ke '.count($userIds).' penerima.');
    }

    /** Update grup: ubah field bersama, tambah penerima baru, hapus yang dibuang. */
    private function updateGroup(array $shared, array $userIds, array $storedFiles): void
    {
        $existing = Task::where('group_id', $this->editingGroupId)
            ->whereIn('assigned_by', $this->manageableGiverIds())
            ->get();
        if ($existing->isEmpty()) {
            return;
        }

        $byUser = $existing->keyBy('user_id');

        foreach ($userIds as $uid) {
            if ($t = $byUser->get($uid)) {
                $t->update($shared); // progres & completed_at tidak diubah
            } else {
                $t = Task::create($shared + [
                    'group_id' => $this->editingGroupId,
                    'user_id' => $uid,
                    'assigned_by' => auth()->id(),
                ]);
                $t->karyawan?->notify(new TaskAssigned($t));
                $t->update(['assigned_notified_at' => now()]);
            }
            $this->attachFilesTo($t, $storedFiles);
        }

        // Penerima yang dihapus dari daftar -> sub-task-nya dihapus.
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
            TaskAttachment::create([
                'task_id' => $task->id,
                'uploaded_by' => auth()->id(),
                'path' => $f['path'],
                'name' => $f['name'],
            ]);
        }
    }

    public function deleteTask($id): void
    {
        $this->ensureCanAssign();

        // Boleh menghapus task yang diberikan oleh diri sendiri ATAU bawahannya.
        Task::whereIn('assigned_by', $this->manageableGiverIds())->whereKey($id)->delete();
        $this->dispatch('swal-success', message: 'Task dihapus.');
    }

    /** Hapus seluruh grup (semua sub-task/penerima) sekaligus. */
    public function deleteGroup($groupId): void
    {
        $this->ensureCanAssign();

        Task::where('group_id', $groupId)
            ->whereIn('assigned_by', $this->manageableGiverIds())
            ->delete();
        $this->dispatch('swal-success', message: 'Seluruh grup task dihapus.');
    }

    // ===== Buka Kembali (revisi task terkunci) — hanya untuk task yang IA berikan =====
    public function openReopen($id): void
    {
        $this->ensureCanAssign();

        $task = $this->findManageableTask($id);
        $this->reopenTaskId = $task->id;
        $this->reopen_alasan = '';
        $this->reopen_label_id = $task->task_category_label_id ?? '';
        // Default deadline baru: 7 hari dari hari ini (revisi biasanya butuh waktu baru).
        $this->reopen_deadline = now()->addDays(7)->toDateString();
        $this->showReopenModal = true;
        // Tutup modal detail bila terbuka agar tidak bertumpuk.
        $this->showModal = false;
    }

    public function bukaKembali(): void
    {
        $this->ensureCanAssign();

        $this->validate([
            'reopen_alasan' => 'required|string|max:2000',
            'reopen_deadline' => 'required|date',
        ], [], [
            'reopen_alasan' => 'alasan revisi',
            'reopen_deadline' => 'deadline baru',
        ]);

        $task = $this->findManageableTask($this->reopenTaskId);
        $deadline = Carbon::parse($this->reopen_deadline);

        // Label baru hanya diterima bila memang milik kategori task ini.
        $labelId = null;
        if ($this->reopen_label_id) {
            $labelId = TaskCategoryLabel::where('id', $this->reopen_label_id)
                ->where('task_category_id', $task->task_category_id)
                ->value('id');
        }

        // Dibuka lagi -> completed_at dihapus, jadi periode kembali dipatok dari
        // deadline baru; akan ditandai ulang dari tanggal selesai putaran ini.
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

        // Catat alasan sebagai komentar bertipe "revisi" (ditandai badge di thread grup).
        TaskComment::create([
            'task_id' => $task->id,
            'group_id' => $task->group_id,
            'user_id' => auth()->id(),
            'body' => $this->reopen_alasan,
            'type' => 'revisi',
        ]);
        // Notifikasi revisi khusus ke bawahan pemilik task.
        $task->karyawan?->notify(new TaskReopened($task, auth()->user()->name, $this->reopen_alasan));

        $this->showReopenModal = false;
        $this->reset(['reopenTaskId', 'reopen_alasan', 'reopen_label_id', 'reopen_deadline']);
        $this->dispatch('swal-success', message: 'Task dibuka kembali untuk revisi.');
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $tasks = Task::visibleTo()
            ->with(['groupComments', 'category', 'label', 'pemberi', 'karyawan'])
            ->when($this->usesSiklus(), function ($q) {
                // Siklus 20-19: filter berdasarkan tanggal deadline_selesai.
                [$mulai, $akhir] = $this->siklusRange();
                $q->whereDate('deadline_selesai', '>=', $mulai->toDateString())
                    ->whereDate('deadline_selesai', '<', $akhir->toDateString());
            }, function ($q) {
                // Kalender: berdasarkan bulan/tahun periode (dari deadline_selesai).
                $q->when($this->bulan, fn ($qq) => $qq->where('periode_bulan', $this->bulan))
                    ->when($this->tahun, fn ($qq) => $qq->where('periode_tahun', $this->tahun));
            })
            ->orderByRaw("CASE WHEN progress <> 'selesai' AND DATE(deadline_selesai) = CURDATE() THEN 0 ELSE 1 END")
            ->orderByRaw("FIELD(progress,'dikerjakan','belum','selesai')")
            ->latest()
            ->get();

        $activeTask = $this->activeTaskId
            ? Task::visibleTo()->with(['groupComments.user', 'attachments', 'pemberi', 'karyawan'])->find($this->activeTaskId)
            : null;

        // Task solo (grup beranggota 1) menampilkan diskusi inline; grup di folder.
        $activeIsSolo = $activeTask
            ? Task::where('group_id', $activeTask->group_id)->count() <= 1
            : true;

        // Nama depan tiap penerima grup untuk fitur @mention di chat grup.
        $chatMembers = $activeTask
            ? User::whereIn('id', Task::where('group_id', $activeTask->group_id)->pluck('user_id')->all())
                ->pluck('name')
                ->map(fn ($n) => Str::of($n)->trim()->explode(' ')->first())
                ->filter()->unique()->values()->all()
            : [];

        // Data untuk modal beri task (hanya relevan bila boleh assign).
        $canAssign = auth()->user()?->canAssignTask() ?? false;
        $bawahan = $canAssign
            ? User::whereIn('id', $this->assignableIds())->orderBy('name')->get(['id', 'name'])
            : collect();
        $categories = $canAssign ? TaskCategory::orderBy('nama')->get() : collect();
        $categoryLabels = ($canAssign && $this->t_category_id)
            ? TaskCategoryLabel::where('task_category_id', $this->t_category_id)->orderBy('nama')->get()
            : collect();
        $editAttachments = $this->editingGroupId
            ? TaskAttachment::whereIn('task_id', Task::where('group_id', $this->editingGroupId)->pluck('id'))
                ->latest()->get()->unique('path')->values()
            : collect();

        $reopenTask = $this->reopenTaskId
            ? Task::with('category.labels')->whereIn('assigned_by', $canAssign ? $this->manageableGiverIds() : [])->find($this->reopenTaskId)
            : null;

        // Pemberi task yang boleh saya kelola (untuk memunculkan tombol di kartu/modal).
        $manageGiverIds = $canAssign ? $this->manageableGiverIds() : [];

        // Status baca komentar per user PER GROUP (untuk badge "komentar baru" kartu).
        $reads = TaskCommentRead::where('user_id', auth()->id())
            ->whereIn('group_id', $tasks->pluck('group_id'))
            ->pluck('last_read_at', 'group_id')
            ->all();

        return view('livewire.pages.admin.task.task-saya-list', [
            'tasks' => $tasks,
            'activeTask' => $activeTask,
            'activeIsSolo' => $activeIsSolo,
            'chatMembers' => $chatMembers,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
            'canAssign' => $canAssign,
            'bawahan' => $bawahan,
            'categories' => $categories,
            'categoryLabels' => $categoryLabels,
            'editAttachments' => $editAttachments,
            'reopenTask' => $reopenTask,
            'reads' => $reads,
            'manageGiverIds' => $manageGiverIds,
        ]);
    }
}
