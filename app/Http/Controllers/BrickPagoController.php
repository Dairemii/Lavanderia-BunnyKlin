<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MercadoPagoService;

class BrickPagoController extends Controller
{
    protected $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    // Muestra la vista con el formulario de pago
    public function mostrarFormulario()
    {
        return view('pagos.brick-form');
    }

    // Procesa el pago enviado desde el Brick
    public function procesarPago(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'payment_method_id' => 'required',
            'installments' => 'required',
            'transaction_amount' => 'required|numeric',
            'email' => 'required|email'
        ]);

        $paymentData = [
            'transaction_amount' => $request->transaction_amount,
            'token' => $request->token,
            'description' => 'Servicio de Lavandería',
            'installments' => (int) $request->installments,
            'payment_method_id' => $request->payment_method_id,
            'payer' => [
                'email' => $request->email,
                'identification' => [
                    'type' => 'DNI', // o 'CURP' según tu país
                    'number' => $request->doc_number ?? '12345678'
                ]
            ]
        ];

        $resultado = $this->mercadoPagoService->crearPago($paymentData);

        if ($resultado['status'] === 'approved') {
            return redirect()->route('pago.exito')->with('success', 'Pago aprobado');
        } elseif ($resultado['status'] === 'pending') {
            return redirect()->route('pago.pendiente');
        } else {
            return redirect()->route('pago.fallo')->with('error', $resultado['status_detail'] ?? 'Error');
        }
    }
}