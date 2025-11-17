@php
    // Mantener la lógica PHP original para las rutas
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

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    {{-- Google Font (Inter) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Definiciones de Bootstrap/AdminLTE esenciales para el formulario */
        .form-control {
            display: block;
            width: 100%;
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }
        .is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + .75rem);
        }
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: .25rem;
            font-size: 80%;
            color: #dc3545;
        }
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
        .input-group {
            position: relative;
        }
        .input-group-append {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            display: flex;
            align-items: center;
            padding-right: 0.75rem;
            pointer-events: none; /* Crucial para hacer clic a través del ícono */
        }
        /* Estilos Icheck-primary para compatibilidad con "Remember Me" */
        .icheck-primary input:checked + label::before {
            background-color: #007bff; /* Color primario */
            border-color: #007bff;
        }
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>

    {{-- iCheck Bootstrap (para el "Remember Me") --}}
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
</head>
<body class="bg-gray-50">

    <div class="flex min-h-screen">

        {{-- LADO IZQUIERDO: IMAGEN DE FONDO --}}
        <div class="hidden lg:block lg:w-1/2 bg-cover bg-center relative" 
             style="background-image: url('{{ asset('images/IDI1.webp') }}');">
            
            {{-- Capa oscura sobre la imagen --}}
            <div class="absolute inset-0 bg-black opacity-30"></div>

            {{-- Contenido de texto centrado --}}
            <div class="absolute inset-0 flex items-center justify-center p-12">
                <div class="text-white text-center">
                    <h1 class="text-5xl font-extrabold mb-4 drop-shadow-lg">SGCI-IDI</h1>
                    <p class="text-xl font-medium drop-shadow-md">Gestión eficiente de inventario científico.</p>
                </div>
            </div>
        </div>

        {{-- LADO DERECHO: FORMULARIO DE LOGIN --}}
        <div class="flex flex-col justify-center items-center w-full lg:w-1/2 p-4 sm:p-8 lg:p-16">
            
            <div class="w-full max-w-md bg-white p-8 sm:p-10 shadow-2xl rounded-xl">
                
                {{-- Título y subtítulo --}}
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ __('adminlte::adminlte.sign_in') }} al SGCI-IDI
                    </h2>
                    <p class="text-gray-500 mt-2">
                        {{ __('adminlte::adminlte.login_message') }}
                    </p>
                </div>

                {{-- Formulario --}}
                <form action="{{ $loginUrl }}" method="post">
                    @csrf

                    {{-- Campo Email --}}
                    <div class="mb-4">
                        <div class="input-group">
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" autofocus>

                            <div class="input-group-append">
                                <span class="fas fa-envelope text-gray-400 {{ config('adminlte.classes_auth_icon', '') }}"></span>
                            </div>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Campo Password --}}
                    <div class="mb-4">
                        <div class="input-group">
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="{{ __('adminlte::adminlte.password') }}">

                            <div class="input-group-append">
                                <span class="fas fa-lock text-gray-400 {{ config('adminlte.classes_auth_icon', '') }}"></span>
                            </div>

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Fila de "Remember Me" y Botón de Sign In --}}
                    <div class="flex justify-between items-center mb-6">
                        <div class="w-1/2">
                            <div class="icheck-primary" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                <label for="remember" class="text-sm">
                                    {{ __('adminlte::adminlte.remember_me') }}
                                </label>
                            </div>
                        </div>

                        <div class="w-1/2 flex justify-end">
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition duration-150 shadow-md">
                                <span class="fas fa-sign-in-alt mr-1"></span>
                                {{ __('adminlte::adminlte.sign_in') }}
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Enlaces de "Olvidé Contraseña" y "Registro" --}}
                <div class="mt-6 text-center text-sm">
                    {{-- Password reset link --}}
                    @if($passResetUrl)
                        <p class="my-2">
                            <a href="{{ $passResetUrl }}" class="text-blue-600 hover:text-blue-800">
                                {{ __('adminlte::adminlte.i_forgot_my_password') }}
                            </a>
                        </p>
                    @endif
            </div>
        </div>
    </div>
</body>
</html>