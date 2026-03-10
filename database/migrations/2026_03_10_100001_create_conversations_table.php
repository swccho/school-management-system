<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('type')->default('direct');
            $table->timestamps();
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
