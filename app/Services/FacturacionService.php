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

    public function crearFactura(array $cliente, array $items, $formaPago, string $metodoPago)
    {
        $usoCFDI = $cliente['use'] ?? 'S01'; 
        unset($cliente['use']);

        $formaPagoLimpia = sprintf("%02d", (int)$formaPago);

        try {
            $resultado = $this->facturapi->Invoices->create([
                "customer"       => $cliente,
                "items"          => $items,
                "payment_form"   => $formaPagoLimpia,
                "payment_method" => $metodoPago,
                "use"            => $usoCFDI,
                "type"           => "I",
            ]);

            // Verificamos que realmente devolvió algo con ID

            //Intentar comentar para ver si Hostgator no tiene problemas con la respuesta de Facturapi, ya que a veces devuelve un objeto sin ID pero con la factura creada. Si esto causa problemas, se puede ajustar la lógica para manejar ambos casos.
            if (!$resultado || !isset($resultado->id)) {
                throw new \Exception("Facturapi no devolvió una factura válida. Respuesta: " . json_encode($resultado));
            }

            return $resultado;

        } catch (\Exception $e) {
            throw new \Exception("Error en Facturapi: " . $e->getMessage());
        }
    }
}