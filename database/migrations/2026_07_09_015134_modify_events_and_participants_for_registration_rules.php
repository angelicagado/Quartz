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
        // 1. Modifying events table
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('registration_start_date')->nullable()->after('end_time');
            $table->dateTime('registration_end_date')->nullable()->after('registration_start_date');
        });

        // The easiest way across databases is to change the column type to string or drop and recreate.
        // Let's change registration_type to string and enforce valid values at the model level.
        Schema::table('events', function (Blueprint $table) {
            $table->string('registration_type')->default('open')->change();
        });

        // 2. Modifying event_participants table
        Schema::table('event_participants', function (Blueprint $table) {
            // Also change status to string to easily add 'pending'
            $table->string('status')->default('registered')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('registration_start_date');
            $table->dropColumn('registration_end_date');
        });
    }
};
