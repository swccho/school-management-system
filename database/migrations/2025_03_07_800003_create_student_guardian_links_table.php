<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardian_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('guardian_id');
            $table->string('relationship_label')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_receive_sms')->default(true);
            $table->boolean('can_receive_email')->default(true);
            $table->boolean('can_login')->default(false);
            $table->timestamps();
        });

        Schema::table('student_guardian_links', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('guardian_id')->references('id')->on('student_guardians')->cascadeOnDelete();
            $table->index('school_id');
            $table->index('student_id');
            $table->index('guardian_id');
            $table->unique(['student_id', 'guardian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardian_links');
    }
};
