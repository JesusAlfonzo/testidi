<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenido al SGCI-IDI</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="text-gray-800 antialiased">

    <div class="relative min-h-screen">

        <div class="absolute inset-0 z-0 blur-md"
            style="background-image: url('{{ asset('images/IDI1.webp') }}'); background-size: cover; background-position: center;">
        </div>

        <div class="absolute inset-0 z-10 bg-black opacity-40"></div>

        <div class="relative z-20 flex flex-col min-h-screen">

            <nav class="bg-white shadow-md">
                <div class="container mx-auto max-w-6xl px-4 py-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <span class="inline-block p-2 bg-blue-600 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-6 4h.01M12 11h.01M14 11h.01M16 11h.01M12 15h.01M14 15h.01M16 15h.01M10 19l-4-4m0 0l4-4m-4 4h18">
                                    </path>
                                </svg>
                            </span>
                            <span class="text-xl font-bold text-gray-900">SGCI-IDI</span>
                        </div>

                        <a href="{{ route('login') }}"
                            class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors duration-200">
                            Iniciar Sesión
                        </a>
                    </div>
                </div>
            </nav>

            <main class="flex-grow flex items-center">
                <div class="container mx-auto max-w-6xl px-4">
                    <div class="text-center">

                        <h2 class="text-lg font-semibold text-blue-300">
                            Instituto de Inmunología "Dr. Nicolás E. Bianco Colmenares"
                        </h2>

                        <h1 class="mt-2 text-4xl md:text-5xl font-extrabold text-white tracking-tight">
                            Bienvenido al SGCI-IDI
                        </h1>

                        <p class="mt-4 text-xl md:text-2xl text-gray-200">
                            Sistema de Gestión y Control de Inventario
                        </p>

                        <p class="mt-6 max-w-2xl mx-auto text-lg text-gray-300">
                            Una solución centralizada para la gestión eficiente de reactivos, materiales y equipos del
                            instituto. Por favor, inicie sesión para acceder al panel de control.
                        </p>

                        <div class="mt-10">
                            <a href="{{ route('login') }}"
                                class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold text-lg shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors duration-200">
                                Acceder al Sistema
                            </a>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="bg-white border-t border-gray-200">
                <div class="container mx-auto max-w-6xl px-4 py-6">
                    <p class="text-center text-sm text-gray-500">
                        &copy; {{ date('Y') }} Instituto de Inmunología "Dr. Nicolás E. Bianco Colmenares". Todos
                        los derechos reservados.
                    </p>
                </div>
            </footer>

        </div>
    </div>
</body>

</html>
