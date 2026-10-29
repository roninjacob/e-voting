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
        Schema::table('elections', function (Blueprint $table): void {
            $table->string('scope', 20)->default('block')->after('block_id')->index();
            $table->foreignId('block_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table): void {
            $table->foreignId('block_id')->nullable(false)->change();
            $table->dropIndex(['scope']);
            $table->dropColumn('scope');
        });
    }
};
