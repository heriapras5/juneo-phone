<?php

use App\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->string('teknisi')->nullable()->after('keterangan');
        });

        foreach (Service::with('serviceItems')->whereNotNull('teknisi')->cursor() as $service) {
            if ($service->serviceItems->isNotEmpty()) {
                foreach ($service->serviceItems as $item) {
                    $item->update(['teknisi' => $service->teknisi]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->dropColumn('teknisi');
        });
    }
};
