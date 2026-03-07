<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mark_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('exam_subject_config_id');
            $table->string('name');
            $table->string('component_type')->default('theory');
            $table->unsignedInteger('marks');
            $table->unsignedInteger('pass_marks')->nullable();
            $table->unsignedTinyInteger('sort_order')->nullable();
            $table->timestamps();
        });

        Schema::table('mark_components', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('exam_subject_config_id')->references('id')->on('exam_subject_configs')->cascadeOnDelete();
            $table->index('school_id');
            $table->index('exam_subject_config_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_components');
    }
};
