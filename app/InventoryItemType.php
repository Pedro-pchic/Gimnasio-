<?php

namespace App;

enum InventoryItemType: string
{
    case Equipment = 'equipment';
    case Product = 'product';
    case Supply = 'supply';

    public function label(): string
    {
        return match ($this) {
            self::Equipment => 'Equipo',
            self::Product => 'Producto',
            self::Supply => 'Insumo',
        };
    }
}
