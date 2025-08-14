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
        Schema::table('rag_websites', function (Blueprint $table) {
            // Rename scraped_content to content
            $table->renameColumn('scraped_content', 'content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rag_websites', function (Blueprint $table) {
            // Rename content back to scraped_content
            $table->renameColumn('content', 'scraped_content');
        });
    }
};
