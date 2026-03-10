<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('username')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('username');
            $table->timestamp('last_login_at')->nullable()->after('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropForeign(['school_id']);
            $table->dropColumn(['school_id', 'phone', 'username', 'avatar', 'last_login_at']);
        });
    }
};
