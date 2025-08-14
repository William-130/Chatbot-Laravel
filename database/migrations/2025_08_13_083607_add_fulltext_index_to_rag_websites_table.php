<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rag_websites', function (Blueprint $table) {
            // Add fulltext index for better search performance
            DB::statement('ALTER TABLE rag_websites ADD FULLTEXT search_index (name, description, content)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rag_websites', function (Blueprint $table) {
            // Drop the fulltext index
            DB::statement('ALTER TABLE rag_websites DROP INDEX search_index');
        });
    }
};
