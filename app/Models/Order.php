<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    // 1. Campos permitidos
    protected $fillable = [
        'sale_id',
        'client_id',
        'arrival_date',
        'service_name',
        'total_price',
        'advance_payment',
        'status',
        'details',
        'delivery_date',
    ];

    // 2. Casteo de datos: Tratamos delivery_date como fecha y hora completa
    protected $casts = [
        'delivery_date' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Todo pedido pertenece estrictamente a un ticket de venta financiero.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Un pedido pertenece a un cliente (aunque el campo puede ser null si es anónimo).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
