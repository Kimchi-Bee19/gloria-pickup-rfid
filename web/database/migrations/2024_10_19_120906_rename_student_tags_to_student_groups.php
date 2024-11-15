<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('student_tags', 'student_groups');
        Schema::rename('student_tag_mappings', 'student_group_mappings');

        Schema::table('student_group_mappings', function (Blueprint $table) {
            $table->renameColumn('tag_id', 'group_id');
        });
    }

    public function down(): void
    {
        Schema::rename('student_groups', 'student_tags');
        Schema::rename('student_group_mappings', 'student_tag_mappings');

        Schema::table('student_tag_mappings', function (Blueprint $table) {
            $table->renameColumn('group_id', 'tag_id');
        });
    }
};
