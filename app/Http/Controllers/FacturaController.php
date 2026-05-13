<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FacturacionService;

class FacturaController extends Controller
{
    protected $facturacion;

    public function __construct(FacturacionService $facturacion)
    {
        $this->facturacion = $facturacion;
    }

    public function create()
    {
        return view('factura.crear'); 
    }

    public function facturar(Request $request)
    {
        // 1. Validaciones
        $request->validate([
            'venta_data'     => 'required|string',
            'legal_name'     => 'required|string|min:3',
            'tax_id'         => 'required|string',
            'tax_system'     => 'required',
            'use_cfdi'       => 'required', 
            'payment_method' => 'required', 
            'payment_form'   => 'required', 
            'email'          => 'required|email',
            'zip'            => 'required|digits:5',
        ]);

        // 2. Decodificamos la venta
        $venta = json_decode($request->venta_data);

        // CORRECCIÓN: Validamos 'items' en lugar de 'detalles' según tu estructura de BD
        if (!$venta || !isset($venta->items)) {
            return back()->withErrors(['error' => 'No has seleccionado una venta válida o la venta no tiene productos.'])->withInput();
        }

        // 3. Lógica para CFDI 4.0 y RFC Genérico
        $taxSystem = $request->tax_id === 'XAXX010101000' ? '616' : $request->tax_system;
        $useCfdi   = $request->tax_id === 'XAXX010101000' ? 'S01' : $request->use_cfdi;

        $cliente = [
            "legal_name" => strtoupper($request->legal_name),
            "tax_id"     => $request->tax_id,
            "tax_system" => $taxSystem,
            "use"        => $useCfdi,
            "email"      => $request->email,
            "address"    => ["zip" => $request->zip]
        ];

        // --- NUEVO: MAPEADOR DE CLAVES SAT ---
        $clavesSat = [
            'App\Models\Supply'       => '47131800', // Suministros
            'App\Models\Service'      => '91111502', // Lavandería
            'App\Models\Subscription' => '93161700', // Suscripciones
        ];

        // 4. Mapeamos los items usando la información de la imagen image_583811.png
        $items = [];
        foreach ($venta->items as $item) {
            // Buscamos la clave según el tipo, si no existe usamos lavandería por defecto
            $productKey = $clavesSat[$item->item_type] ?? '91111502';

            $items[] = [
                "quantity" => $item->quantity,
                "product" => [
                    "description" => $item->name_snapshot, // Nombre guardado en la venta
                    "product_key" => $productKey,        // Clave dinámica
                    "price"       => $item->price_snapshot, // Precio guardado en la venta
                    "tax_included" => true
                ]
            ];
        }

        try {
            // 5. Ejecutamos la facturación
            $factura = $this->facturacion->crearFactura(
                $cliente, 
                $items, 
                $request->payment_form, 
                $request->payment_method
            );
            
            $pdfUrl = $factura->files->pdf ?? "https://www.facturapi.io/v2/invoices/{$factura->id}/pdf";
            
            return redirect()->route('factura.crear')->with(
                'success', 
                '¡Factura creada con éxito! <a href="' . $pdfUrl . '" target="_blank" style="color: blue; text-decoration:underline; font-weight: bold;">Ver PDF de la Factura</a>'
            );

        } catch (\Exception $e) {
            return redirect()->route('factura.crear')
                ->withErrors(['error' => 'Error de Facturapi: ' . $e->getMessage()])
                ->withInput();
        }
    }
}