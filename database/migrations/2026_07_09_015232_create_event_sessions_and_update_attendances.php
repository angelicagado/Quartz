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
        // 1. Create event_sessions table
        Schema::create('event_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Day 1 Morning"
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('requires_checkout')->default(false); // If true, requires check-in AND check-out
            $table->timestamps();
        });

        // 2. Update attendances table
        Schema::table('attendances', function (Blueprint $table) {
            // Drop scan_type column. Since SQLite doesn't fully support dropColumn elegantly sometimes, Laravel 11 handles it.
            $table->dropColumn('scan_type');
            
            // Add session and type
            $table->foreignId('event_session_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->default('check_in'); // check_in or check_out
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['event_session_id']);
            $table->dropColumn('event_session_id');
            $table->dropColumn('type');
            $table->string('scan_type')->default('one-time');
        });

        Schema::dropIfExists('event_sessions');
    }
};
