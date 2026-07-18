<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpers', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('warna')->nullable()->comment('CSS class untuk badge');
            $table->timestamps();
        });

        DB::table('helpers')->insert([
            ['nama' => 'Adit', 'warna' => 'bg-gray-100 text-gray-600 border-gray-200'],
            ['nama' => 'Edi', 'warna' => 'bg-blue-100 text-blue-700 border-blue-200'],
            ['nama' => 'Nur', 'warna' => 'bg-purple-100 text-purple-700 border-purple-200'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('helpers');
    }
};
