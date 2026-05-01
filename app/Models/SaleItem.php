<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SaleItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sale_id',
        'name_snapshot',
        'price_snapshot',
        'quantity',
        'subtotal'
    ];

    /**
     * Relación con la cabecera del ticket
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación Polimórfica (Obtiene el Servicio, Insumo o Suscripción)
     */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
