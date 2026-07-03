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
        DB::table('service_items')->where('kerusakan', 'not like', '[%')->get()->each(function ($item) {
            DB::table('service_items')->where('id', $item->id)->update([
                'kerusakan' => json_encode([$item->kerusakan])
            ]);
        });
    }

    public function down(): void
    {
        DB::table('service_items')->where('kerusakan', 'like', '[%')->get()->each(function ($item) {
            $decoded = json_decode($item->kerusakan, true);
            $first = is_array($decoded) ? ($decoded[0] ?? '') : $item->kerusakan;
            DB::table('service_items')->where('id', $item->id)->update([
                'kerusakan' => $first
            ]);
        });
    }
};
