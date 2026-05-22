<?php

namespace App\Http\Controllers;

use App\Models\NotificacionSistema;
use Illuminate\Http\Request;

class NotificacionSistemaController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        $notificaciones = NotificacionSistema::where(function ($query) use ($usuario) {
                $query->where('usuario_id', $usuario->id)
                      ->orWhere('rol_destino', $usuario->rol);
            })
            ->latest()
            ->paginate(15);

        return view('notificaciones.index', compact('notificaciones'));
    }

    public function recientes()
    {
        $usuario = auth()->user();

        $notificaciones = NotificacionSistema::where(function ($query) use ($usuario) {
                $query->where('usuario_id', $usuario->id)
                      ->orWhere('rol_destino', $usuario->rol);
            })
            ->latest()
            ->take(5)
            ->get();

        $noLeidas = NotificacionSistema::where(function ($query) use ($usuario) {
                $query->where('usuario_id', $usuario->id)
                      ->orWhere('rol_destino', $usuario->rol);
            })
            ->where('leida', false)
            ->count();

        return response()->json([
            'no_leidas' => $noLeidas,
            'notificaciones' => $notificaciones,
        ]);
    }

        public function abrir($id)
    {
        $usuario = auth()->user();

        $notificacion = NotificacionSistema::where('id', $id)
            ->where(function ($query) use ($usuario) {
                $query->where('usuario_id', $usuario->id)
                    ->orWhere('rol_destino', $usuario->rol);
            })
            ->firstOrFail();

        if (!$notificacion->leida) {
            $notificacion->update([
                'leida' => true,
                'leida_en' => now(),
            ]);
        }

        return redirect($notificacion->url ?? route('notificaciones.index'));
    }


        public function marcarTodasLeidas()
    {
        $usuario = auth()->user();

        NotificacionSistema::where(function ($query) use ($usuario) {
                $query->where('usuario_id', $usuario->id)
                    ->orWhere('rol_destino', $usuario->rol);
            })
            ->where('leida', false)
            ->update([
                'leida' => true,
                'leida_en' => now(),
            ]);

        return response()->json([
            'success' => true
        ]);
    }


}