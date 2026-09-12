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
        Schema::create('third_party_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('third_party_id')->constrained('commercial_partners')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->decimal('base_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['third_party_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('third_party_items');
    }
};
