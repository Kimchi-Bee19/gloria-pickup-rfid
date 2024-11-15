<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_identities', function (Blueprint $table) {
            // Expand tag_id to 32 bytes
            $table->binary('tag_id', 32)->change();

            // Drop auth_check column
            $table->dropColumn('auth_check');
        });

        Schema::table('vehicle_identities', function (Blueprint $table) {
            // Expand tag_id to 32 bytes
            $table->binary('tag_id', 32)->change();

            // Drop auth_check column
            $table->dropColumn('auth_check');
        });
    }

    public function down(): void
    {
        Schema::table('student_identities', function (Blueprint $table) {
            $table->binary('tag_id', 7)->change();
            $table->binary('auth_check', 64);
        });

        Schema::table('vehicle_identities', function (Blueprint $table) {
            $table->binary('tag_id', 12)->change();
            $table->binary('auth_check', 64);
        });
    }
};
