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
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->index();
            $table->foreign('book_id')->references('id')->on('books');
            $table->foreignId('member_id')->index();
            $table->foreign('member_id')->references('id')->on('members');
            $table->timestamp('borrowed_at')->index();
            $table->timestamp('returned_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};