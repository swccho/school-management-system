<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('academic_session_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->date('attendance_date');
            $table->unsignedBigInteger('taken_by')->nullable();
            $table->string('status')->default('final');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->cascadeOnDelete();
            $table->foreign('class_id')->references('id')->on('school_classes')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
            $table->foreign('taken_by')->references('id')->on('users')->nullOnDelete();
            $table->index('school_id');
            $table->index('academic_session_id');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('attendance_date');
            $table->index('taken_by');
            $table->index('status');
            $table->unique(
                ['academic_session_id', 'class_id', 'section_id', 'attendance_date'],
                'att_sessions_session_class_section_date_uniq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
