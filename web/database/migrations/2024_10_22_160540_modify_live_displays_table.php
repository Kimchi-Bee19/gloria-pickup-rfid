<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('live_displays', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mqtt_client_id');
            $table->renameColumn('tag_regex_filter', 'group_regex_filter');
        });
    }

    public function down(): void
    {
        Schema::table('live_displays', function (Blueprint $table) {
            $table->renameColumn('group_regex_filter', 'tag_regex_filter');
            $table->foreignId('mqtt_client_id')->constrained('mqtt_clients');
        });
    }
};
