<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Subscription;
use App\Models\Supply;

class CatalogoService
{
    /**
     * Centraliza la creación de cualquier elemento del catálogo.
     */
    public function guardarElemento(array $datos)
    {
        return match ($datos['category']) {
            'services' => Service::query()->create([
                'name'          => $datos['name'],
                'price'         => $datos['price'],
                'description'   => $datos['description'] ?? null,
                'is_active'     => true
            ]),

            'supplies'      => Supply::query()->create([
                'name'      => $datos['name'],
                'price'     => $datos['price'],
                'stock'     => $datos['stock'] ?? 0,
                'unit'      => $datos['unit'] ?? 'Pza',
                'is_active' => true
            ]),

            'subscriptions' => Subscription::query()->create([
                'name'              => $datos['name'],
                'price'             => $datos['price'],
                'duration_months'   => $datos['duration_months'] ?? 1,
                'description'       => $datos['description'] ?? null,
                'is_active'         => true
            ]),

            default => throw new \Exception('Categoría no válida'),
        };
    }

    public function actualizarElemento(array $datos)
    {
        $id = $datos['id'];

        return match ($datos['category']) {
            'services' => tap(Service::query()->findOrFail($id), function ($service) use ($datos) {
                $service->update([
                    'name'        => $datos['name'],
                    'price'       => $datos['price'],
                    'description' => $datos['description'] ?? null,
                ]);
            }),

            'supplies' => tap(Supply::query()->findOrFail($id), function ($supply) use ($datos) {
                $supply->update([
                    'name'  => $datos['name'],
                    'price' => $datos['price'],
                    'stock' => $datos['stock'] ?? 0,
                    'unit'  => $datos['unit'] ?? 'Pza',
                ]);
            }),

            'subscriptions' => tap(Subscription::query()->findOrFail($id), function ($subscription) use ($datos) {
                $subscription->update([
                    'name'            => $datos['name'],
                    'price'           => $datos['price'],
                    'duration_months' => $datos['duration_months'] ?? 1,
                    'description'     => $datos['description'] ?? null,
                ]);
            }),

            default => throw new \Exception('Categoría no válida'),
        };
    }

}
