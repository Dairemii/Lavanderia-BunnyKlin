<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    // Carga las ventas de la base de datos para el Historial
    public function index()
    {
        $ventas = Venta::latest()->get()->map(function($venta) {
            return [
                'id' => $venta->id,
                'folio' => $venta->folio,
                'fecha' => $venta->created_at->format('d/m/Y H:i'),
                'metodo' => $venta->metodo_pago,
                'total' => (float)$venta->total,
                'detalles' => $venta->productos // Mapeamos para que coincida con tu JS
            ];
        });

        return view('pages.historial', [
            'title' => 'Historial de Ventas',
            'ventas' => $ventas
        ]);
    }

    // Guarda la venta enviada desde el POS
    public function store(Request $request)
    {
        try {
            $venta = Venta::create([
                'folio' => 'BK-' . strtoupper(substr(uniqid(), -6)), // Genera folio tipo BK-A1B2C3
                'total' => $request->total,
                'metodo_pago' => $request->metodo_pago,
                'productos' => $request->productos // El array que manda el POS
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Venta guardada correctamente',
                'folio' => $venta->folio
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}