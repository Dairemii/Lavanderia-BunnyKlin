<?php

namespace App\Services;

use Facturapi\Facturapi;

class FacturacionService
{
    protected $facturapi;

    public function __construct()
    {
        // Instanciamos Facturapi con la llave que pusimos en el .env
        $this->facturapi = new Facturapi(config('services.facturapi.key'));
    }

    public function crearFactura(array $datosCliente, array $items, string $metodoPago)
    {
        try {
            return $this->facturapi->Invoices->create([
                "customer" => $datosCliente,
                "items" => $items,
                "payment_form" => $metodoPago,
                //"dispatch" => true // Esto la envía por correo automáticamente
            ]);
        } catch (\Exception $e) {
            // Aquí atrapamos si el SAT rechaza algo o si la API falla
            throw new \Exception("Error en Facturapi: " . $e->getMessage());
        }
    }
}