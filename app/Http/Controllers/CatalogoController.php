<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CatalogoService;

class CatalogoController extends Controller
{
    public function __construct(protected CatalogoService $catalogoService)
    {
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'name'   => 'required|string|max:100',
            'price'   => 'required|numeric|min:0',
        ]);

        try {
            $item = $this->catalogoService->guardarElemento($request->all());

            return response()->json([
                'success' => true,
                'item'    => $item
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'       => 'required|integer',
            'category' => 'required|string',
            'name'     => 'required|string|max:100',
            'price'    => 'required|numeric|min:0',
        ]);

        try {
            $item = $this->catalogoService->actualizarElemento($request->all());

            return response()->json([
                'success' => true,
                'item'    => $item
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
