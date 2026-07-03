<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_number')->unique();
            $table->date('tanggal');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('type_hp');
            $table->text('kerusakan');
            $table->decimal('pengeluaran', 15, 2)->default(0);
            $table->decimal('jasa', 15, 2)->default(0);
            $table->decimal('kebutuhan_pribadi', 15, 2)->default(0);
            $table->decimal('keuntungan', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->string('bukti_nota')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
