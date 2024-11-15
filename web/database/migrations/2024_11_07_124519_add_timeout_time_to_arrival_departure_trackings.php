<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arrival_departure_trackings', function (Blueprint $table) {
            $table->timestamp('timeout_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('arrival_departure_trackings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('timeout_at');
        });
    }
};
