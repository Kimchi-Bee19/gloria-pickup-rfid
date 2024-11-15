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
        Schema::table('students', function (Blueprint $table) {
            $table->string('internal_id', 16)->nullable()->change();
            $table->string('full_name', 256)->nullable()->change();
            $table->string('call_name', 64)->nullable()->change();
            $table->string('class', 32)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('internal_id', 16)->nullable(false)->change();
            $table->string('full_name', 256)->nullable(false)->change();
            $table->string('call_name', 64)->nullable(false)->change();
            $table->string('class', 32)->nullable(false)->change();
        });
    }
};
