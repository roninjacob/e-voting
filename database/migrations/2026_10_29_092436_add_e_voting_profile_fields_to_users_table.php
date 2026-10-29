<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('student_id', 50)->nullable()->unique()->after('id');
            $table->string('role', 20)->default('voter')->index()->after('password');
            $table->foreignId('block_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->boolean('is_verified')->default(false)->after('block_id');
            $table->boolean('is_active')->default(true)->after('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('block_id');
            $table->dropColumn(['student_id', 'role', 'is_verified', 'is_active']);
        });
    }
};
