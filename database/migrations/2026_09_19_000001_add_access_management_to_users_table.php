<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamp('password_set_at')->nullable();
        });

        Schema::create('invitation_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        DB::table('users')->update([
            'is_admin' => true,
            'password_set_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_tokens');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['is_admin', 'is_active', 'invitation_sent_at', 'password_set_at']);
        });
    }
};
