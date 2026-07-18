<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_mutasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sparepart_id')->constrained()->onDelete('cascade');
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->integer('jumlah');
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index('sparepart_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_mutasi');
    }
};
