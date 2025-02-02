<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehicle_identities', function (Blueprint $table) {
            $table->binary('auth_check', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_identities', function (Blueprint $table) {
            $table->dropColumn('auth_check');
        });
    }
};
