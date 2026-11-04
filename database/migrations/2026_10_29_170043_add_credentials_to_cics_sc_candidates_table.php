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
        Schema::table('cics_sc_candidates', function (Blueprint $table) {
            $table->text('education')->nullable();
            $table->text('experience')->nullable();
            $table->text('awards')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cics_sc_candidates', function (Blueprint $table) {
            $table->dropColumn(['education', 'experience', 'awards']);
        });
    }
};
