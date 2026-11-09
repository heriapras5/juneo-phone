<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus tabel lama jika ada, buat baru dengan struktur lengkap
        if (Schema::hasTable('phone_trades')) {
            Schema::dropIfExists('phone_trades');
        }

        Schema::create('phone_trades', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->nullable()->unique(); // Kode unik: JB-0001
            
            // Data HP
            $table->string('type_hp');
            $table->string('brand')->nullable();
            $table->string('storage')->nullable();
            $table->string('warna')->nullable();
            $table->enum('kondisi', ['baru', 'bekas', 'minus'])->default('bekas');
            $table->string('imei')->nullable();
            $table->text('kelengkapan')->nullable();
            
            // Tracking Pembelian - Dari mana beli dan kapan
            $table->date('tanggal_masuk');
            $table->decimal('harga_beli', 15, 2);
            $table->string('penjual')->nullable(); // Siapa yang jual ke Juneo Phone
            
            // Tracking Penjualan - Dijual ke siapa dan kapan
            $table->date('tanggal_keluar')->nullable();
            $table->decimal('harga_jual', 15, 2)->nullable();
            $table->string('pembeli')->nullable(); // Siapa yang beli dari Juneo Phone
            
            // Status
            $table->enum('status', ['stok', 'terjual'])->default('stok');
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
            
            $table->index('status');
            $table->index('tanggal_masuk');
            $table->index('tanggal_keluar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_trades');
    }
};
