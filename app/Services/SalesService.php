<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class SalesService
{
    public function procesarVenta(array $datos)
    {
        return DB::transaction(function () use ($datos) {

            // Creamos la venta. El modelo 'Sale' autogenerará el folio 'reference' en su método boot()
            $sale = Sale::query()->create([
                'total'          => $datos['total'],
                'payment_method' => $datos['metodo_pago'],
                // 'client_id'   => null
            ]);

            $mapaModelos = [
                'services'      => \App\Models\Service::class,
                'supplies'      => \App\Models\Supply::class,
                'subscriptions' => \App\Models\Subscription::class,
            ];

            foreach ($datos['detalles'] as $item) {
                $modeloClase = $mapaModelos[$item['category']] ?? null;

                if (!$modeloClase) {
                    throw new \Exception("Categoría de producto no reconocida: " . $item['category']);
                }

                $sale->items()->create([
                    'item_type'      => $modeloClase,
                    'item_id'        => $item['id'],
                    'name_snapshot'  => $item['name'],
                    'price_snapshot' => $item['price'],
                    'quantity'       => $item['quantity'],
                    'subtotal'       => $item['price'] * $item['quantity'],
                ]);
            }

            return $sale;
        });
    }

    public function eliminarVenta(int $id)
    {
        $venta = Sale::query()->findOrFail($id);
        // Al eliminar el modelo, la BD se encarga de los sale_items en cascada
        return $venta->delete();
    }

    public function eliminarVentasMasivas(array $ids)
    {
        return Sale::query()->whereIn('id', $ids)->delete();
    }

}
