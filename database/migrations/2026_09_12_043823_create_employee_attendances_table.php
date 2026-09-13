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
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_shift_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('attendance_date');
            $table->time('entry_time');
            $table->time('exit_time')->nullable();
            $table->string('registration_method', 20)->default('manual');
            $table->string('status', 20)->default('present');
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'attendance_date']);
            $table->index(['branch_id', 'attendance_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendances');
    }
};
