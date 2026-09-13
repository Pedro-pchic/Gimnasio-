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
        Schema::create('referrals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('referrer_client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('referred_client_id')->constrained('clients')->restrictOnDelete();
            $table->string('status')->default('pending');
            $table->decimal('reward_amount', 10, 2)->default(100);
            $table->string('reward_status')->default('pending');
            $table->timestamps();

            $table->unique('referred_client_id');
            $table->index(['referrer_client_id', 'reward_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
