<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('prioritas')->default('normal')->after('tanggal_selesai');
            $table->date('estimasi_selesai')->nullable()->after('prioritas');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['prioritas', 'estimasi_selesai']);
        });
    }
};
