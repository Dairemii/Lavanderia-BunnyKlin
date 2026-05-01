<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    protected $fillable = ['client_id', 'total', 'payment_method'];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    /**
     * Relación con el detalle del ticket
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
 * Autogenerar el folio antes de guardar en base de datos
 */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (! $sale->reference) {
                DB::transaction(function () use ($sale) {
                    // Agregamos query() para iniciar la cadena del Builder
                    $lastSale = static::query()
                        ->lockForUpdate()
                        ->latest('id')
                        ->first();

                    $nextNumber = 1;

                    if ($lastSale) {
                        $lastNumber = intval(str_replace('BK-', '', $lastSale->reference));
                        $nextNumber = $lastNumber + 1;
                    }

                    $sale->reference = 'BK-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                });
            }
        });
    }

    /**
     * @param array<string,mixed> $array
     */
    public static function create(array $array)
    {
    }
}
