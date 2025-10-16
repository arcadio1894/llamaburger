<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        // Para select2 (devuelve JSON)
        $clientes = Cliente::orderBy('nombre')->get(['id','nombre','tipo_doc','num_doc']);
        return response()->json($clientes);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'nombre'   => 'required|string|max:120',
            'tipo_doc' => 'nullable|in:DNI,RUC',
            'num_doc'  => 'nullable|string|max:15',
        ]);

        $cliente = Cliente::create($data);

        return response()->json([
            'ok' => true,
            'cliente' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'tipo_doc' => $cliente->tipo_doc,
                'num_doc' => $cliente->num_doc,
                'display' => $cliente->display_name,
            ]
        ]);
    }
}
