<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'total' => 'required|numeric',
            'metodo_pago' => 'required|string',
            'detalles' => 'required|array',
        ]);

        try {
            $venta = DB::transaction(function () use ($request) {

                // 1. Crear la cabecera (el folio se autogenera gracias al boot() del modelo)
                $venta = Venta::query()->create([
                    'total' => $request->total,
                    'metodo_pago' => $request->metodo_pago,
                    'cliente_id' => null, // Por ahora nulo
                ]);

                // Mapeamos las categorías de JS a los alias del MorphMap
                $mapCategorias = [
                    'services' => 'servicio',
                    'products' => 'insumo',
                    'subscriptions' => 'suscripcion',
                ];

                // 2. Guardar los detalles (el snapshot de la venta)
                foreach ($request->detalles as $item) {
                    VentaItem::query()->create([
                        'venta_id' => $venta->id,
                        'item_type' => $mapCategorias[$item['category']],
                        'item_id' => $item['id'],
                        'nombre_snapshot' => $item['name'],
                        'precio_snapshot' => $item['price'],
                        'cantidad' => $item['quantity'],
                        'subtotal' => $item['price'] * $item['quantity'],
                    ]);

                    // Aquí en el futuro podrías restar el stock si el type es 'insumo'
                }

                return $venta;
            });

            return response()->json([
                'success' => true,
                'venta' => $venta
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
