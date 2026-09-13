<?php

namespace App\Models;

use App\QualityCertificateStatus;
use Database\Factories\QualityCertificateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['purchase_order_id', 'number', 'issued_date', 'expires_date', 'status', 'notes'])]
class QualityCertificate extends Model
{
    /** @use HasFactory<QualityCertificateFactory> */
    use HasFactory;

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /** @return array<string, string|class-string> */
    protected function casts(): array
    {
        return ['issued_date' => 'date', 'expires_date' => 'date', 'status' => QualityCertificateStatus::class];
    }
}
