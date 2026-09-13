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
        Schema::create('equipment_maintenances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
            $table->date('scheduled_date')->nullable();
            $table->date('performed_date')->nullable();
            $table->text('description');
            $table->string('status')->default('pending');
            $table->decimal('cost', 11, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenances');
    }
};
