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
        Schema::create('gym_class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('gym_class_schedule_id')->constrained()->restrictOnDelete();
            $table->date('enrollment_date');
            $table->string('status')->default('enrolled');
            $table->timestamps();

            $table->unique(['client_id', 'gym_class_schedule_id', 'enrollment_date']);
            $table->index(['gym_class_schedule_id', 'enrollment_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_class_enrollments');
    }
};
