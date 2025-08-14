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
        Schema::table('conversations', function (Blueprint $table) {
            // Add website_id column
            $table->unsignedBigInteger('website_id')->nullable()->after('bot_response');
            
            // Add foreign key constraint
            $table->foreign('website_id')->references('id')->on('rag_websites')->onDelete('set null');
            
            // Drop the old website_context column if it exists
            if (Schema::hasColumn('conversations', 'website_context')) {
                $table->dropColumn('website_context');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Drop foreign key and website_id column
            $table->dropForeign(['website_id']);
            $table->dropColumn('website_id');
            
            // Add back website_context column
            $table->string('website_context')->nullable();
        });
    }
};
