<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    // Campos que permitimos llenar masivamente (Mass Assignment)
    protected $fillable = [
        'name',
        'phone',
        'subscription_id',
        'end_subscription',
        'rfc',
        'razon_social',
        'codigo_postal',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'ciudad',
        'estado',
    ];

    // Casteo de datos: Le decimos a Laravel que trate este campo como un objeto Carbon (Fecha)
    protected $casts = [
        'end_subscription' => 'date',
    ];

    // --- ACCESOR (Campo virtual) ---
    // Te permite usar $client->has_active_subscription en tu frontend o controladores
    public function getHasActiveSubscriptionAttribute(): bool
    {
        return $this->end_subscription && $this->end_subscription->isFuture();
    }

    // --- RELACIONES ---

    /**
     * Un cliente puede tener una suscripción asociada.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Un cliente puede tener muchas ventas (historial de tickets).
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Un cliente puede tener muchos pedidos operativos en el sistema.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
