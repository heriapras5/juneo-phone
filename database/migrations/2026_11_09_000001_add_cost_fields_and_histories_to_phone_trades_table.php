<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_trades', function (Blueprint $table) {
            $table->string('sumber')->default('langsung')->after('kondisi');
            $table->decimal('biaya_perbaikan', 15, 2)->default(0)->after('harga_beli');
        });

        Schema::create('phone_trade_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_trade_id')->constrained('phone_trades')->cascadeOnDelete();
            $table->string('tipe'); // dibuat, status, update
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['phone_trade_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_trade_histories');

        Schema::table('phone_trades', function (Blueprint $table) {
            $table->dropColumn(['sumber', 'biaya_perbaikan']);
        });
    }
};
