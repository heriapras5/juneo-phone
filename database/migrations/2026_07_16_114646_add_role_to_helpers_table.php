<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('helpers', function (Blueprint $table) {
            $table->string('role')->default('teknisi')->after('nama');
        });

        DB::table('helpers')->where('nama', 'Nur')->update(['role' => 'helper']);
    }

    public function down(): void
    {
        Schema::table('helpers', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
