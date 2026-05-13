<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Subscription;
use App\Services\ClientService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(protected ClientService $clientService)
    {
    }

    // Carga inicial para AlpineJS
    public function apiInit()
    {
        // Traemos a los clientes con su relación de suscripción cargada
        $clients = Client::with('subscription')->latest()->get();

        // Traemos solo los planes activos
        $subscriptions = Subscription::query()->where('is_active', true)->get();

        return response()->json([
            'clients' => $clients,
            'subscriptions' => $subscriptions
        ]);
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'name'             => 'required|string|max:100',
            'phone'            => 'nullable|string|max:20',
            'subscription_id'  => 'nullable|exists:subscriptions,id',
            'start_subscription' => 'nullable|date',
            'wantsBilling'     => 'boolean',
            'rfc'              => 'nullable|string|max:14',
            'razon_social'     => 'nullable|string|max:255',
            'uso_cfdi'         => 'nullable|string|max:10',
            'regimen_fiscal'   => 'nullable|string|max:10',
            'codigo_postal'    => 'nullable|string|max:5',
        ]);

        $client = $this->clientService->guardarCliente($datosValidados);
        $client->load('subscription'); // Cargamos la relación para devolverla al frontend

        return response()->json(['success' => true, 'client' => $client]);
    }

    public function update(Request $request, Client $client)
    {
        $datosValidados = $request->validate([
            'name'             => 'required|string|max:100',
            'phone'            => 'nullable|string|max:20',
            'subscription_id'  => 'nullable|exists:subscriptions,id',
            'end_subscription' => 'nullable|date',
            'wantsBilling'     => 'boolean',
            'rfc'              => 'nullable|string|max:14',
            'razon_social'     => 'nullable|string|max:255',
            'uso_cfdi'         => 'nullable|string|max:10',
            'regimen_fiscal'   => 'nullable|string|max:10',
            'codigo_postal'    => 'nullable|string|max:5',
        ]);

        $client = $this->clientService->guardarCliente($datosValidados, $client);
        $client->load('subscription');

        return response()->json(['success' => true, 'client' => $client]);
    }

    public function destroy(Client $client)
    {
        $this->clientService->eliminarCliente($client);
        return response()->json(['success' => true]);
    }
}
