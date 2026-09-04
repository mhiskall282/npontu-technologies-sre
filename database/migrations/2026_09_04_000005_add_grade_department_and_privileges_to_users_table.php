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
        Schema::table('users', function (Blueprint $table) {
            $table->string('grade', 10)->default('L2')->after('role');
            $table->string('department', 100)->default('Core Operations (NOC)')->after('grade');
            $table->json('privileges')->nullable()->after('department');

            $table->index('grade');
            $table->index('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['grade']);
            $table->dropIndex(['department']);
            $table->dropColumn(['grade', 'department', 'privileges']);
        });
    }
};
