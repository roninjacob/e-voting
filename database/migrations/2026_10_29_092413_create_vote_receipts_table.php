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
        Schema::create('vote_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('election_id')->constrained()->restrictOnDelete();
            $table->foreignId('voter_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('block_id')->constrained()->restrictOnDelete();
            $table->uuid('receipt_reference')->unique();
            $table->string('payload_hash', 64);
            $table->string('status', 20)->default('confirmed');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->unique(['election_id', 'voter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_receipts');
    }
};
