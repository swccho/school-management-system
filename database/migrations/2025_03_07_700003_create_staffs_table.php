<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_id');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('religion')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('joining_date')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('employee_type');
            $table->string('photo_path')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('staffs', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('designation_id')->references('id')->on('designations')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('staff_departments')->nullOnDelete();
            $table->index('school_id');
            $table->index('user_id');
            $table->index('designation_id');
            $table->index('department_id');
            $table->index('employee_type');
            $table->index('status');
            $table->unique(['school_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
