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
                if (! Schema::hasColumn('pesanan', 'external_id')) {
                    $table->string('external_id', 100)->nullable()->after('status_bayar');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                if (Schema::hasColumn('pesanan', 'external_id')) {
                    $table->dropColumn('external_id');
                }
            });
        }
    }
};
