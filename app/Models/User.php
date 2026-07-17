<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'profile_photo',
        'status',
        'failed_login_attempts',
    ];

    /**
     * Akun terblokir (mis. setelah 3x gagal login). Hanya admin fitur karyawan
     * yang dapat mengaktifkannya kembali.
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blokir';
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // relation role user
    public function detail(): HasOne
    {
        return $this->hasOne(EmployeeDetail::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // Langganan Web Push (satu user bisa punya banyak perangkat/browser)
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role->name === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role->name, $roles);
    }

    /**
     * Profil karyawan sudah lengkap? (rekening, tanggal lahir, no HP, alamat).
     * Dipakai gerbang akses fitur.
     */
    public function profileComplete(): bool
    {
        $d = $this->detail;

        return $d
            && filled($d->nomor_rekening)
            && filled($d->tanggal_lahir)
            && filled($d->phone)
            && filled($d->alamat);
    }

    /** Apakah hari ini (tanggal & bulan) adalah ulang tahun karyawan? */
    public function isBirthday(): bool
    {
        $tgl = $this->detail?->tanggal_lahir;
        if (! $tgl) {
            return false;
        }

        return $tgl->format('m-d') === now()->format('m-d');
    }

    /**
     * Semua karyawan yang berulang tahun HARI INI (cocok tanggal & bulan,
     * tahun diabaikan). Dipakai dashboard agar seluruh karyawan ikut melihat
     * ucapan untuk siapa pun yang berulang tahun, bukan hanya yang bersangkutan.
     */
    public static function ulangTahunHariIni()
    {
        return self::whereHas('detail', function ($q) {
            $q->whereNotNull('tanggal_lahir')
                ->whereMonth('tanggal_lahir', now()->month)
                ->whereDay('tanggal_lahir', now()->day);
        })
            ->orderBy('name')
            ->get();
    }

    // Check permission
    public function hasPermission(string $permission): bool
    {
        return $this->role && $this->role->hasPermission($permission);
    }

    // Check any permission
    public function hasAnyPermission(array $permissions): bool
    {
        if (! $this->role) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    // Check all permissions
    public function hasAllPermissions(array $permissions): bool
    {
        if (! $this->role) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scope data: apakah user boleh melihat SELURUH data modul ini
     * (mis. semua gaji karyawan), atau hanya miliknya sendiri.
     *
     * Konvensi: permission "view_all_{modul}".
     * - admin/finance  -> punya view_all_xxx  -> lihat semua
     * - karyawan       -> tidak punya         -> hanya data sendiri
     */
    public function canViewAll(string $module): bool
    {
        return $this->hasPermission('view_all_'.$module);
    }

    /** Atasan langsung user ini (dari employee_details.atasan_id). */
    public function atasan(): ?User
    {
        return $this->detail?->atasan;
    }

    /**
     * ID seluruh bawahan (downline) secara rekursif — bawahan langsung
     * maupun tak langsung. Aman dari siklus (setiap id hanya diproses sekali).
     *
     * @return array<int>
     */
    protected ?array $bawahanIdsCache = null;

    public function bawahanIds(): array
    {
        if ($this->bawahanIdsCache !== null) {
            return $this->bawahanIdsCache;
        }

        $all = [];
        $frontier = [$this->id];

        while ($frontier) {
            $children = EmployeeDetail::whereIn('atasan_id', $frontier)
                ->pluck('user_id')
                ->all();

            $frontier = [];
            foreach ($children as $childId) {
                if (! in_array($childId, $all, true)) {
                    $all[] = $childId;
                    $frontier[] = $childId;
                }
            }
        }

        return $this->bawahanIdsCache = $all;
    }

    /**
     * ID seluruh atasan (rantai ke atas) dari user ini — atasan langsung
     * hingga puncak. Aman dari siklus. Dipakai untuk notifikasi berantai.
     *
     * @return array<int>
     */
    public function atasanIds(): array
    {
        $ids = [];
        $currentId = $this->detail?->atasan_id;

        while ($currentId && ! in_array($currentId, $ids, true)) {
            $ids[] = $currentId;
            $currentId = EmployeeDetail::where('user_id', $currentId)->value('atasan_id');
        }

        return $ids;
    }

    /**
     * Boleh memberi task lewat "Task Saya"? Butuh izin assign_task DAN
     * benar-benar punya bawahan (mis. Fajar tanpa bawahan tidak bisa).
     */
    public function canAssignTask(): bool
    {
        return $this->hasPermission('assign_task') && count($this->bawahanIds()) > 0;
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo && file_exists(public_path($this->profile_photo))) {
            return asset($this->profile_photo);
        }

        // Default avatar using initials
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    public function isOnline()
    {
        $lastActivity = DB::table('sessions')
            ->where('user_id', $this->id)
            ->orderBy('last_activity', 'desc')
            ->value('last_activity');

        if (! $lastActivity) {
            return false;
        }

        return Carbon::createFromTimestamp($lastActivity)
            ->gt(Carbon::now()->subMinutes(1));
    }

    public function lastSeen(): ?Carbon
    {
        return $this->last_seen_at;
    }

    public function getOnlineAttribute(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subSeconds(10));
    }

    public function getLastSeenDiffAttribute(): ?string
    {
        return $this->last_seen_at
            ? $this->last_seen_at->diffForHumans()
            : null;
    }
}
