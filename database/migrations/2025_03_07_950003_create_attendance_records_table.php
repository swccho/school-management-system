<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('attendance_session_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_academic_assignment_id')->nullable();
            $table->string('attendance_status');
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->boolean('is_late')->default(false);
            $table->string('reason')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('student_academic_assignment_id')->references('id')->on('student_academic_assignments')->nullOnDelete();
            $table->index('school_id');
            $table->index('attendance_session_id');
            $table->index('student_id');
            $table->index('attendance_status');
            $table->unique(['attendance_session_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
