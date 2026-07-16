<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * QR codes are now rendered on demand from `qr_token`, so the stored
     * SVG path is no longer needed.
     */
    public function up(): void
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropColumn('qr_code_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->string('qr_code_path')->nullable()->after('status');
        });
    }
};
