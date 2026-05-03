<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                if (! Schema::hasColumn('pesanan', 'guest_id')) {
                    $table->string('guest_id', 50)->nullable()->after('external_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                if (Schema::hasColumn('pesanan', 'guest_id')) {
                    $table->dropColumn('guest_id');
                }
            });
        }
    }
};