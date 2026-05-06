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
}
