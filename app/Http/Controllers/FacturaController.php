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
    //Codigo para mostrar los datos que manda a facturapi    
    //dd($request->all());
        // 1. Validamos los campos. 
        // Nota: 'venta_data' debe ser un string (el JSON de AlpineJS)
        $request->validate([
            'venta_data' => 'required|string',
            'legal_name' => 'required|string|min:3',
            'tax_id'     => 'required|string',
            'tax_system' => 'required',
            'email'      => 'required|email',
            'zip'        => 'required|digits:5',
        ]);

        // 2. Decodificamos la venta enviada desde el cliente
        $venta = json_decode($request->venta_data);

        // Seguridad: Si el JSON es inválido o no tiene servicios, regresamos error
        if (!$venta || !isset($venta->detalles)) {
            return back()->withErrors(['error' => 'No has seleccionado una venta válida o la venta no tiene productos.'])->withInput();
        }

        // 3. Mapeamos los datos del cliente
        $taxSystem = $request->tax_id === 'XAXX010101000' ? '616' : $request->tax_system;

        $cliente = [
            "legal_name" => $request->legal_name,
            "tax_id"     => $request->tax_id,
            "tax_system" => $taxSystem,
            "email"      => $request->email,
            "address"    => ["zip" => $request->zip]
        ];

        // 4. Creamos los items para Facturapi recorriendo los servicios de la venta
        $items = [];
        foreach ($venta->detalles as $item) {
            $items[] = [
                "quantity" => $item->quantity,
                "product" => [
                    "description" => $item->name,
                    "product_key" => "91111502", // Clave SAT Lavandería
                    "price"       => $item->price,
                    "tax_included" => true
                ]
            ];
        }

        // Si tu venta NO trae detalles y solo quieres facturar el total global:
        /*
        $items[] = [
            "quantity" => 1,
            "product" => [
                "description" => "Servicio de Lavandería Folio: " . $venta->folio,
                "product_key" => "91111502",
                "price"       => $venta->total,
                "tax_included" => true
            ]
        ];
        */

        try {
            // 5. Ejecutamos la facturación
            // Llamamos al servicio (usando '01' Efectivo o '03' Transferencia como default)
            $factura = $this->facturacion->crearFactura($cliente, $items, '03');
            
            // Obtenemos la URL del PDF (con fallback por si el objeto no trae la propiedad)
            // En lugar de la URL del dashboard, usamos la URL de descarga directa
            $pdfUrl = "https://www.facturapi.io/invoices/{$factura->id}";
            
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