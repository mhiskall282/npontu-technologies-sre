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
        Schema::create('shift_handovers', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('shift', ['morning', 'afternoon', 'night'])->default('morning');
            $table->foreignId('outgoing_lead_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('incoming_lead_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('summary');
            $table->text('incidents')->nullable();
            $table->unsignedInteger('pending_tasks_count')->default(0);
            $table->unsignedInteger('completed_tasks_count')->default(0);
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index(['date', 'shift']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_handovers');
    }
};
