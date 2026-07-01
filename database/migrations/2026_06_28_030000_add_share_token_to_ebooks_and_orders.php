<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->string('share_token', 16)->nullable()->unique()->after('id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('share_token', 16)->nullable()->unique()->after('order_number');
        });

        // Backfill token untuk data yang sudah ada
        foreach (DB::table('ebooks')->whereNull('share_token')->pluck('id') as $id) {
            DB::table('ebooks')->where('id', $id)->update(['share_token' => Str::random(10)]);
        }
        foreach (DB::table('orders')->whereNull('share_token')->pluck('id') as $id) {
            DB::table('orders')->where('id', $id)->update(['share_token' => Str::random(10)]);
        }
    }

    public function down(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropColumn('share_token');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('share_token');
        });
    }
};
