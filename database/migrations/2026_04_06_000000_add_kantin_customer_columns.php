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
                if (! Schema::hasColumn('pesanan', 'iduser')) {
                    $table->unsignedBigInteger('iduser')->nullable()->after('idpesanan');
                }

                if (! Schema::hasColumn('pesanan', 'snap_token')) {
                    $table->string('snap_token', 255)->nullable()->after('status_bayar');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                if (Schema::hasColumn('pesanan', 'snap_token')) {
                    $table->dropColumn('snap_token');
                }

                if (Schema::hasColumn('pesanan', 'iduser')) {
                    $table->dropColumn('iduser');
                }
            });
        }
    }
};