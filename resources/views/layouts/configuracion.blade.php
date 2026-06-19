@extends('layouts.master')

@section('content')

<style>
    .config-wrapper {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 24px;
    }

    .config-sidebar {
        background: rgba(255,255,255,0.96);
        border-radius: 18px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.22);
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: 95px;
    }

    .config-sidebar-header {
        background: linear-gradient(135deg, #1f3a56, #2d4f73);
        color: white;
        padding: 22px;
    }

    .config-menu {
        padding: 12px;
    }

    .config-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 12px;
        color: #1f3a56;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 6px;
        transition: all .2s ease;
    }

    .config-menu a:hover {
        background: #eef3f8;
        transform: translateX(4px);
    }

    .config-menu a.active {
        background: #1f3a56;
        color: white;
    }

    @media(max-width: 992px) {
        .config-wrapper {
            grid-template-columns: 1fr;
        }

        .config-sidebar {
            position: relative;
            top: 0;
        }
    }
</style>

<div class="config-wrapper">

    <aside class="config-sidebar">

        <div class="config-sidebar-header">
            <h5 class="fw-bold mb-1">⚙️ Ajustes</h5>
            <small>Administración general de SIPERH</small>
        </div>

        <div class="config-menu">

    @if(in_array(auth()->user()->rol, ['superadmin', 'rrhh']))

        <a href="{{ route('configuracion.inicio') }}"
           class="{{ request()->routeIs('configuracion.inicio') ? 'active' : '' }}">
            🏠 Panel general
        </a>

        <a href="{{ route('usuarios.index') }}"
           class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            👥 Usuarios y roles
        </a>

        <a href="{{ route('practicantes.index') }}"
        class="{{ request()->routeIs('practicantes.*') ? 'active' : '' }}">
            🎓 Practicantes
        </a>

        <a href="{{ route('correcciones-saldos.create') }}"
           class="{{ request()->routeIs('correcciones-saldos.*') ? 'active' : '' }}">
            🛠 Corrección de saldos
        </a>

        <a href="{{ route('dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            🚦 Control de vencimientos
        </a>

    @endif

    @if(auth()->user()->rol === 'superadmin')

        <a href="{{ route('bitacora.index') }}"
           class="{{ request()->routeIs('bitacora.*') ? 'active' : '' }}">
            📘 Bitácora del sistema
        </a>

    @endif

    <a href="{{ route('manuales.index') }}"
    class="{{ request()->routeIs('manuales.*') ? 'active' : '' }}">
    📚 Manuales
    </a>

    <a href="{{ route('configuracion.acerca') }}"
       class="{{ request()->routeIs('configuracion.acerca') ? 'active' : '' }}">
        ℹ️ Acerca de SIPERH
    </a>

</div>

    </aside>

    <main>
        @yield('config-content')
    </main>

</div>

@endsection