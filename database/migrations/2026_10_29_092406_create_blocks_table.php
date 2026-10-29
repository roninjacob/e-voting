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
        Schema::create('blocks', function (Blueprint $table): void {
            $table->id();
            $table->string('program', 100)->default('BSIT');
            $table->string('section', 20);
            $table->unsignedTinyInteger('year_level');
            $table->unsignedTinyInteger('semester')->default(1);
            $table->string('track', 10)->nullable();
            $table->timestamps();

            $table->unique(['program', 'section', 'year_level', 'semester', 'track'], 'blocks_identity_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
