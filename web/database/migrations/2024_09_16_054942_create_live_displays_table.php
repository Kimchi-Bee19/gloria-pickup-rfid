<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('live_displays', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->foreignId('mqtt_client_id')->constrained('mqtt_clients');
            $table->string('tag_regex_filter', 512)->nullable();
            $table->string('class_regex_filter', 512)->nullable();
            $table->enum('filter_mode', ['or', 'and'])->default('or');
            $table->boolean('is_enabled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_displays');
    }
};
