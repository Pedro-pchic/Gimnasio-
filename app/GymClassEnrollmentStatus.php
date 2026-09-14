<?php

namespace App;

enum GymClassEnrollmentStatus: string
{
    case Enrolled = 'enrolled';
    case Attended = 'attended';
    case Cancelled = 'cancelled';
    case Absent = 'absent';

    public function label(): string
    {
        return match ($this) {
            self::Enrolled => 'Reservada',
            self::Attended => 'Asistió',
            self::Cancelled => 'Cancelada',
            self::Absent => 'Ausente',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function capacityBlockingValues(): array
    {
        return [self::Enrolled->value, self::Attended->value, self::Absent->value];
    }
}
