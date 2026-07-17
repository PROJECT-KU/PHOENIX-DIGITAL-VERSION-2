<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Izin "Beri Task ke Bawahan" untuk fitur Task Saya. Idempoten agar aman
     * dijalankan di DB yang sudah ter-seed sebelumnya.
     */
    public function up(): void
    {
        if (! Permission::where('name', 'assign_task')->exists()) {
            Permission::create([
                'name' => 'assign_task',
                'display_name' => 'Beri Task ke Bawahan',
                'group' => 'task',
                'description' => 'Dapat memberi task kepada bawahan di Task Saya (butuh memiliki bawahan)',
            ]);
        }
    }

    public function down(): void
    {
        Permission::where('name', 'assign_task')->delete();
    }
};
