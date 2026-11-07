<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexName = 'biaya_harian_tanggal_kategori_unique';
        if (Schema::hasIndex('biaya_harian', $indexName)) {
            Schema::table('biaya_harian', function ($table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }

        $newIndex = 'biaya_harian_tanggal_kategori_index';
        if (!Schema::hasIndex('biaya_harian', $newIndex)) {
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
