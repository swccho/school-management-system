<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subject_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedInteger('full_marks');
            $table->unsignedInteger('pass_marks');
            $table->unsignedInteger('theory_marks')->nullable();
            $table->unsignedInteger('practical_marks')->nullable();
            $table->unsignedInteger('oral_marks')->nullable();
            $table->boolean('has_practical')->default(false);
            $table->unsignedTinyInteger('sort_order')->nullable();
            $table->timestamps();
        });

        Schema::table('exam_subject_configs', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('class_id')->references('id')->on('school_classes')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->index('school_id');
            $table->index('exam_id');
            $table->index('class_id');
            $table->index('subject_id');
            $table->unique(['exam_id', 'class_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subject_configs');
    }
};
