@php
    // Mantener la lógica PHP original para las rutas (INTACTA)
    $loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
    $registerUrl = View::getSection('register_url') ?? config('adminlte.register_url', 'register');
    $passResetUrl = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset');

    if (config('adminlte.use_route_url', false)) {
        $loginUrl = $loginUrl ? route($loginUrl) : '';
        $registerUrl = $registerUrl ? route($registerUrl) : '';
        $passResetUrl = $passResetUrl ? route($passResetUrl) : '';
    } else {
        $loginUrl = $loginUrl ? url($loginUrl) : '';
        $registerUrl = $registerUrl ? url($registerUrl) : '';
        $passResetUrl = $passResetUrl ? url($passResetUrl) : '';
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SGCI-IDI | Iniciar Sesión</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        /* Estilos para el Layout de Pantalla Dividida */
        body, html {
            height: 100%;
            margin: 0;
        }

        .login-split-screen {
            height: 100vh; /* Ocupa toda la altura */
            overflow: hidden;
        }

        /* Columna Izquierda: Imagen */
        .bg-split-image {
            background-image: url('{{ asset('images/IDI1.webp') }}');
            background-size: cover;
            background-position: center;
            position: relative;
            min-height: 100%;
        }

        .bg-overlay {
            background-color: rgba(0, 0, 0, 0.4); /* Capa oscura */
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Columna Derecha: Formulario */
        .login-form-container {
            background-color: #f8f9fa; /* Gris muy claro (bg-gray-50) */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .login-card-custom {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
    </style>
</head>
<body>

    <div class="row no-gutters login-split-screen">
        
        <div class="col-lg-6 d-none d-lg-block bg-split-image">
            <div class="bg-overlay">
                <div class="text-center text-white px-5">
                    <h1 class="font-weight-bold display-4" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">SGCI-IDI</h1>
                    <p class="lead font-weight-bold" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
                        Gestión eficiente de inventario científico.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12 login-form-container">
            
            <div class="login-card-custom">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-body p-5">

                        {{-- Título --}}
                        <div class="text-center mb-4">
                            <h3 class="text-dark font-weight-bold">
                                {{ __('adminlte::adminlte.sign_in') }} al SGCI-IDI
                            </h3>
                            <p class="text-muted">
                                {{ __('adminlte::adminlte.login_message') }}
                            </p>
                        </div>

                        {{-- Formulario --}}
                        <form action="{{ $loginUrl }}" method="post">
                            @csrf

                            {{-- Campo Email --}}
                            <div class="input-group mb-3">
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" autofocus>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                                    </div>
                                </div>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            {{-- Campo Password --}}
                            <div class="input-group mb-3">
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                       placeholder="{{ __('adminlte::adminlte.password') }}">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                                    </div>
                                </div>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            {{-- Fila de Remember Me y Botón --}}
                            <div class="row align-items-center mb-3">
                                <div class="col-6">
                                    <div class="icheck-primary" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label for="remember">
                                            {{ __('adminlte::adminlte.remember_me') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6 text-right">
                                    <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm">
                                        <span class="fas fa-sign-in-alt mr-1"></span>
                                        {{ __('adminlte::adminlte.sign_in') }}
                                    </button>
                                </div>
                            </div>

                        </form>

                        {{-- Enlaces Footer --}}
                        @if($passResetUrl)
                            <div class="text-center mt-4">
                                <a href="{{ $passResetUrl }}" class="text-primary">
                                    {{ __('adminlte::adminlte.i_forgot_my_password') }}
                                </a>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>