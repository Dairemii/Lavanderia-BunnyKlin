<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Point\PointClient;
use Exception;

class TerminalController extends Controller
{
    public function __construct()
    {
        // Token de Producción
        MercadoPagoConfig::setAccessToken("APP_USR-3611189794742413-041809-56f3d298a20d175ac8db7489fd3eef13-409289088");
    }

    public function cobrarEnTerminal(Request $request)
    {
        $request->validate(['total' => 'required|numeric|min:5']);

        try {
            $client = new PointClient();
            $deviceId = "NEWLAND_N950__N950NCCB05478475"; 

            $montoCentavos = (int) ($request->total * 100);

            $paymentIntentRequest = [
                "amount" => $montoCentavos, 
                "additional_info" => [
                    "external_reference" => 'BK-' . time(),
                    "print_on_terminal" => true 
                ]
            ];

            $response = $client->createPaymentIntent($deviceId, $paymentIntentRequest);

            return response()->json([
                'success' => true,
                'payment_intent_id' => $response->id
            ]);

        } catch (Exception $e) {
            // Logueamos el error en storage/logs/laravel.log por si acaso
            \Log::error("Error Point MP: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'No se pudo iniciar el cobro: ' . $e->getMessage()
            ], 200); // Forzamos 200 para que el JS lea el JSON de error
        }
    }

    public function verificarEstado($id)
    {
        try {
            $client = new PointClient();
            $response = $client->getPaymentIntent($id);

            // Verificamos si la respuesta tiene el status esperado
            if (isset($response->status)) {
                return response()->json([
                    'success' => true,
                    'status' => $response->status
                ]);
            }

            return response()->json(['success' => false, 'status' => 'UNKNOWN']);

        } catch (Exception $e) {
            \Log::error("Error Polling MP: " . $e->getMessage());
            
            return response()->json([
                'success' => false, 
                'error' => 'Error de conexión temporal',
                'status' => 'RETRY' // Pedimos al JS que intente de nuevo en lugar de cancelar
            ], 200);
        }
    }
}