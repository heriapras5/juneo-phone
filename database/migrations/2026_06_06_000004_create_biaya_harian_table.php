<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biaya_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kategori'); // bank_jago, fee_edi, fee_nur, lainnya
            $table->string('nama')->nullable();
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['tanggal', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya_harian');
    }
};
