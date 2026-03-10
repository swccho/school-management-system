<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('notice_attachments', function (Blueprint $table) {
            $table->index('notice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_attachments');
    }
};
