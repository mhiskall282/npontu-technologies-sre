<?php

declare(strict_types=1);

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
        Schema::table('shift_handovers', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('signed_at');
            $table->foreignId('accepted_by_id')->nullable()->after('accepted_at')->constrained('users')->nullOnDelete();
            $table->text('acceptance_remarks')->nullable()->after('accepted_by_id');

            $table->index('accepted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_handovers', function (Blueprint $table) {
            $table->dropIndex(['accepted_at']);
            $table->dropForeign(['accepted_by_id']);
            $table->dropColumn(['accepted_at', 'accepted_by_id', 'acceptance_remarks']);
        });
    }
};
