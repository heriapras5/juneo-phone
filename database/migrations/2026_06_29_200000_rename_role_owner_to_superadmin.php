<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->change();
        });

        User::where('role', 'owner')->update(['role' => 'superadmin']);
    }

    public function down(): void
    {
        User::where('role', 'superadmin')->update(['role' => 'owner']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->change();
        });
    }
};
