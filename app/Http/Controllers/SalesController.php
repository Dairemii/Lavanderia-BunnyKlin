<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SalesService;

class SalesController extends Controller
{
    public function __construct(protected SalesService $ventaService)
    {
    }

    public function store(Request $request)
    {
        // Validamos que venga el carrito y el total
        $request->validate([
            'total'       => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
            'detalles'    => 'required|array|min:1',
        ]);

        try {
            $venta = $this->ventaService->procesarVenta($request->all());

            return response()->json([
                'success' => true,
                'venta'   => $venta
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function apiHistorial()
    {
        // Traemos las ventas con sus detalles, ordenadas de la más reciente a la más antigua
        $ventas = \App\Models\Sale::with('items')->latest()->get();

        return response()->json($ventas);
    }

    public function destroy($id)
    {
        try {
            $this->ventaService->eliminarVenta($id);

            return response()->json([
                'success' => true,
                'message' => 'Venta eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroyBulk(Request $request)
    {
        // Validamos que nos manden un arreglo de IDs y que existan en la tabla sales
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:sales,id'
        ]);

        try {
            $this->ventaService->eliminarVentasMasivas($request->ids);

            return response()->json([
                'success' => true,
                'message' => 'Ventas eliminadas correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
