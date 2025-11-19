<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenido al SGCI-IDI</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        /* Estilos personalizados para replicar tu diseño original sin Tailwind */
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Source Sans Pro', sans-serif;
        }
        
        .bg-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Fondo con imagen borrosa */
        .bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('{{ asset('images/IDI1.webp') }}');
            background-size: cover;
            background-position: center;
            filter: blur(8px); /* El efecto blur-md de tailwind */
            z-index: 0;
        }

        /* Capa oscura */
        .bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* bg-black opacity-40 */
            z-index: 1;
        }

        /* Contenido principal encima de todo */
        .content-wrapper-custom {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
    </style>
</head>

<body class="layout-top-nav">

    <div class="bg-wrapper">
        <div class="bg-image"></div>
        <div class="bg-overlay"></div>

        <div class="content-wrapper-custom">
            
            <nav class="navbar navbar-expand-md navbar-light navbar-custom">
                <div class="container">
                    <a href="#" class="navbar-brand">
                        <span class="brand-text font-weight-bold text-dark">
                            <i class="fas fa-cubes text-primary mr-1"></i> SGCI-IDI
                        </span>
                    </a>
                    
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="btn btn-primary font-weight-bold">
                                Iniciar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="d-flex align-items-center flex-grow-1">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 text-center text-white">
                            
                            <h4 class="text-info font-weight-bold">
                                Instituto de Inmunología "Dr. Nicolás E. Bianco Colmenares"
                            </h4>

                            <h1 class="display-4 font-weight-bold mt-3">
                                Bienvenido al SGCI-IDI
                            </h1>

                            <p class="lead mt-3">
                                Sistema de Gestión y Control de Inventario
                            </p>

                            <p class="mt-4 text-light" style="font-size: 1.1rem;">
                                Una solución centralizada para la gestión eficiente de reactivos, materiales y equipos del instituto. Por favor, inicie sesión para acceder al panel de control.
                            </p>

                            <div class="mt-5">
                                <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5 shadow-lg font-weight-bold">
                                    Acceder al Sistema
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <footer class="main-footer bg-white text-center border-top-0 mt-auto">
                <div class="container">
                    <small class="text-muted">
                        &copy; {{ date('Y') }} Instituto de Inmunología "Dr. Nicolás E. Bianco Colmenares". Todos los derechos reservados.
                    </small>
                </div>
            </footer>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>