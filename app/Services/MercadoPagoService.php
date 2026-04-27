<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MercadoPagoService
{
    protected $accessToken;

    public function __construct()
    {
        $this->accessToken = env('MERCADOPAGO_ACCESS_TOKEN');
    }

    public function crearPreferencia(array $data): array
    {
        $url = 'https://api.mercadopago.com/checkout/preferences';

        $response = Http::withToken($this->accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($url, $data);

        // Si hay error, mostramos todo para depurar
        if ($response->failed()) {
            echo '<pre>';
            echo '<h2>Error al crear preferencia de Mercado Pago</h2>';
            echo '<strong>Status HTTP:</strong> ' . $response->status() . '<br><br>';
            echo '<strong>Respuesta de la API:</strong><br>';
            print_r($response->json());
            echo '<hr>';
            echo '<strong>Datos enviados:</strong><br>';
            print_r($data);
            echo '</pre>';
            exit;
        }

        return $response->json();
    }
}