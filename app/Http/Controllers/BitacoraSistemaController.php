<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BitacoraSistema;

class BitacoraSistemaController extends Controller
{
    public function index(Request $request)
    {
        $bitacoras = BitacoraSistema::query()

            ->when($request->filled('buscar'), function ($query) use ($request) {

                $buscar = $request->buscar;

                $query->where(function ($q) use ($buscar) {

                    $q->where('usuario_nombre', 'LIKE', "%{$buscar}%")
                      ->orWhere('accion', 'LIKE', "%{$buscar}%")
                      ->orWhere('descripcion', 'LIKE', "%{$buscar}%")
                      ->orWhere('ip_equipo', 'LIKE', "%{$buscar}%")
                      ->orWhere('modulo', 'LIKE', "%{$buscar}%");

                });

            })

            ->when($request->filled('usuario'), function ($query) use ($request) {

                $query->where('usuario_nombre', 'LIKE', '%' . $request->usuario . '%');

            })

            ->when($request->filled('ip'), function ($query) use ($request) {

                $query->where('ip_equipo', 'LIKE', '%' . $request->ip . '%');

            })

            ->when($request->filled('modulo'), function ($query) use ($request) {

                $query->where('modulo', $request->modulo);

            })

            ->when($request->filled('fecha_desde'), function ($query) use ($request) {

                $query->whereDate('created_at', '>=', $request->fecha_desde);

            })

            ->when($request->filled('fecha_hasta'), function ($query) use ($request) {

                $query->whereDate('created_at', '<=', $request->fecha_hasta);

            })

            ->latest()

            ->paginate(20)

            ->withQueryString();

        return view('bitacora.index', compact('bitacoras'));
    }

    public function imprimir(Request $request)
    {
        $bitacoras = BitacoraSistema::query()

            ->when($request->filled('buscar'), function ($query) use ($request) {

                $buscar = $request->buscar;

                $query->where(function ($q) use ($buscar) {

                    $q->where('usuario_nombre', 'LIKE', "%{$buscar}%")
                      ->orWhere('accion', 'LIKE', "%{$buscar}%")
                      ->orWhere('descripcion', 'LIKE', "%{$buscar}%")
                      ->orWhere('ip_equipo', 'LIKE', "%{$buscar}%")
                      ->orWhere('modulo', 'LIKE', "%{$buscar}%");

                });

            })

            ->when($request->filled('usuario'), function ($query) use ($request) {

                $query->where('usuario_nombre', 'LIKE', '%' . $request->usuario . '%');

            })

            ->when($request->filled('ip'), function ($query) use ($request) {

                $query->where('ip_equipo', 'LIKE', '%' . $request->ip . '%');

            })

            ->when($request->filled('modulo'), function ($query) use ($request) {

                $query->where('modulo', $request->modulo);

            })

            ->when($request->filled('fecha_desde'), function ($query) use ($request) {

                $query->whereDate('created_at', '>=', $request->fecha_desde);

            })

            ->when($request->filled('fecha_hasta'), function ($query) use ($request) {

                $query->whereDate('created_at', '<=', $request->fecha_hasta);

            })

            ->latest()

            ->get();

        return view('bitacora.imprimir', compact('bitacoras'));
    }
}