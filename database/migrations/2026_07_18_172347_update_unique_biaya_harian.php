<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexName = 'biaya_harian_tanggal_kategori_unique';
        $exists = DB::select("SHOW INDEX FROM biaya_harian WHERE Key_name = ?", [$indexName]);
        if (!empty($exists)) {
            Schema::table('biaya_harian', function ($table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }

        $newIndex = 'biaya_harian_tanggal_kategori_index';
        $existsNew = DB::select("SHOW INDEX FROM biaya_harian WHERE Key_name = ?", [$newIndex]);
        if (empty($existsNew)) {
            Schema::table('biaya_harian', function ($table) {
                $table->index(['tanggal', 'kategori']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('biaya_harian', function ($table) {
            $table->dropIndex(['tanggal', 'kategori']);
            $table->unique(['tanggal', 'kategori']);
        });
    }
};
