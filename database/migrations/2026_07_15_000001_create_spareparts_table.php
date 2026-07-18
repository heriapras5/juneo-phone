<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->string('kategori');
            $table->string('type_hp')->nullable();
            $table->integer('stok')->default(0);
            $table->decimal('harga_beli', 12, 2)->nullable();
            $table->string('toko')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('kategori');
            $table->index('toko');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
