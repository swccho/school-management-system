<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('academic_session_id')->nullable();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('homework', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('school_classes')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->index(['teacher_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework');
    }
};
