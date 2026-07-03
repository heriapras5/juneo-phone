<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE services MODIFY COLUMN status ENUM('menunggu','dikerjakan','selesai','diambil','batal') DEFAULT 'menunggu'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE services MODIFY COLUMN status ENUM('menunggu','dikerjakan','selesai','diambil') DEFAULT 'menunggu'");
        }
    }
};
