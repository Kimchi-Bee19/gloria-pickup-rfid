<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arrival_departure_trackings', function (Blueprint $table) {
            $table->renameColumn("pinned", "is_pinned");
        });
    }

    public function down(): void
    {
        Schema::table('arrival_departure_trackings', function (Blueprint $table) {
            $table->renameColumn("is_pinned", "pinned");
        });
    }
};
