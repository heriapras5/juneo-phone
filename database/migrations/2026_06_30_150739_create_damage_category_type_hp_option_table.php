<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('damage_category_type_hp_option', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damage_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('type_hp_option_id')->constrained()->cascadeOnDelete();
            $table->unique(['damage_category_id', 'type_hp_option_id'], 'dc_type_hp_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_category_type_hp_option');
    }
};
