<?php

namespace App;

enum InventoryItemStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Maintenance => 'En mantenimiento',
            self::Inactive => 'Inactivo',
        };
    }
}
