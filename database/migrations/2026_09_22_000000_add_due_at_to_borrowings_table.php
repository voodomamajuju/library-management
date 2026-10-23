<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->timestamp('due_at')->nullable()->index();
        });

        // Backfill existing records: due_at defaults to borrowed_at + 14 days.
        DB::statement('UPDATE borrowings SET due_at = DATE_ADD(borrowed_at, INTERVAL 14 DAY) WHERE due_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });
    }
};