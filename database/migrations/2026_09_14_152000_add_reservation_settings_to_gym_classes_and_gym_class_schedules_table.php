<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gym_classes', function (Blueprint $table): void {
            $table->boolean('requires_premium')->default(false)->after('maximum_capacity');
        });

        Schema::table('gym_class_schedules', function (Blueprint $table): void {
            $table->unsignedInteger('maximum_capacity')->default(20)->after('end_time');
        });

        $maximumCapacities = DB::table('gym_classes')->pluck('maximum_capacity', 'id');

        DB::table('gym_class_schedules')
            ->orderBy('id')
            ->get(['id', 'gym_class_id'])
            ->each(function (object $schedule) use ($maximumCapacities): void {
                $maximumCapacity = $maximumCapacities->get($schedule->gym_class_id);

                if ($maximumCapacity !== null) {
                    DB::table('gym_class_schedules')
                        ->where('id', $schedule->id)
                        ->update(['maximum_capacity' => $maximumCapacity]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gym_class_schedules', function (Blueprint $table): void {
            $table->dropColumn('maximum_capacity');
        });

        Schema::table('gym_classes', function (Blueprint $table): void {
            $table->dropColumn('requires_premium');
        });
    }
};
