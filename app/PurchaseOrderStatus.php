<?php

namespace App;

enum PurchaseOrderStatus: string
{
    case Pending = 'pending';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Received => 'Recibida',
            self::Cancelled => 'Cancelada',
        };
    }
}
