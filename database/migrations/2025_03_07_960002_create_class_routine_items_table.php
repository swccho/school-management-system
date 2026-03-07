<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_routine_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('class_routine_id');
            $table->string('day_of_week');
            $table->unsignedTinyInteger('period_no');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->string('room_label')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::table('class_routine_items', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('class_routine_id')->references('id')->on('class_routines')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
            $table->index('school_id');
            $table->index('class_routine_id');
            $table->index('day_of_week');
            $table->index('subject_id');
            $table->index('teacher_id');
            $table->unique(
                ['class_routine_id', 'day_of_week', 'period_no'],
                'cri_routine_day_period_uniq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_routine_items');
    }
};
