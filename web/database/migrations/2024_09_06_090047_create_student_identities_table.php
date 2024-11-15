<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('student_identities', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['nfc'])->default('nfc');
            $table->binary('tag_id', 7)->index();
            $table->binary('auth_check', 64);
            $table->string('notes', 256);

            $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_identities');
    }
};
