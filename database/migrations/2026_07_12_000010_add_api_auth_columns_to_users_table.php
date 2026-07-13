<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('verify_token')->nullable();
            $table->string('reset_password_token')->nullable();
            $table->string('otp_secret')->nullable();
            $table->boolean('is_otp_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['verify_token', 'reset_password_token', 'otp_secret', 'is_otp_enabled']);
        });
    }
};
