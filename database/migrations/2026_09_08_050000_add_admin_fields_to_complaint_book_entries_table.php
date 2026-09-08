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
        Schema::table('complaint_book_entries', function (Blueprint $table) {
            $table->text('admin_notes')->nullable()->after('status');
            $table->timestamp('attended_at')->nullable()->after('admin_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaint_book_entries', function (Blueprint $table) {
            $table->dropColumn(['admin_notes', 'attended_at']);
        });
    }
};
