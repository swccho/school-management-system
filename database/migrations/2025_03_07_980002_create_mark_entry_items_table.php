<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mark_entry_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('mark_entry_id');
            $table->unsignedBigInteger('mark_component_id')->nullable();
            $table->unsignedInteger('obtained_marks')->default(0);
            $table->timestamps();
        });

        Schema::table('mark_entry_items', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('mark_entry_id')->references('id')->on('mark_entries')->cascadeOnDelete();
            $table->foreign('mark_component_id')->references('id')->on('mark_components')->nullOnDelete();
            $table->index('school_id');
            $table->index('mark_entry_id');
            $table->index('mark_component_id');
            $table->unique(['mark_entry_id', 'mark_component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_entry_items');
    }
};
