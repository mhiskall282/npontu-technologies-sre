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
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])
                ->default('medium')
                ->after('category');
            $table->string('sla_time', 10)
                ->nullable()
                ->after('priority');
            $table->boolean('is_pinned')
                ->default(false)
                ->after('is_active');

            $table->index('priority');
            $table->index('is_pinned');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('incident_ticket', 50)
                ->nullable()
                ->after('remark');
            $table->boolean('is_escalated')
                ->default(false)
                ->after('incident_ticket');

            $table->index('is_escalated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['priority']);
            $table->dropIndex(['is_pinned']);
            $table->dropColumn(['priority', 'sla_time', 'is_pinned']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['is_escalated']);
            $table->dropColumn(['incident_ticket', 'is_escalated']);
        });
    }
};
