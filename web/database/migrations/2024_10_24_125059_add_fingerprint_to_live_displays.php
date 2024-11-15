<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('live_displays', function (Blueprint $table) {
            $table->binary('fingerprint', 32)->unique();
        });
    }

    public function down(): void
    {
        Schema::table('live_displays', function (Blueprint $table) {
            $table->dropColumn('fingerprint');
        });
    }
};
