@extends('adminlte::page')

@section('title', 'Ayuda - ' . $title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-question-circle"></i> {{ $title }}</h1>
        <a href="{{ route('admin.help.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-book-reader"></i> {{ $title }}
                    </h3>
                </div>
                <div class="card-body" style="background-color: #fafafa;">
                    <div class="help-content" style="font-size: 16px; line-height: 1.8;">
                        {!! $content !!}
                    </div>
                </div>
                <div class="card-footer" style="background-color: #f0f0f0;">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('admin.help.download', $section) }}" class="btn btn-primary">
                                <i class="fas fa-file-pdf"></i> Descargar PDF
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('admin.help.index') }}" class="btn btn-default">
                                <i class="fas fa-home"></i> Volver al Inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .help-content h1 {
            font-size: 28px;
            color: #1d4ed8;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .help-content h2 {
            font-size: 22px;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
            padding-left: 15px;
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .help-content h3 {
            font-size: 18px;
            color: #1e3a8a;
            background-color: #e0e7ff;
            padding: 8px 12px;
            border-radius: 5px;
            margin-top: 20px;
            margin-bottom: 12px;
        }
        .help-content p {
            margin-bottom: 15px;
            text-align: justify;
        }
        .help-content ul, .help-content ol {
            margin-left: 25px;
            margin-bottom: 15px;
        }
        .help-content li {
            margin-bottom: 8px;
        }
        .help-content strong {
            color: #1e3a8a;
        }
        .help-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .help-content table thead tr {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }
        .help-content table th, .help-content table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .help-content table th {
            font-weight: bold;
        }
        .help-content table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .help-content table tbody tr:hover {
            background-color: #e8f4fd;
        }
        .help-content hr {
            border: none;
            border-top: 2px solid #3b82f6;
            margin: 30px 0;
        }
        .help-content .badge {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 3px;
        }
        .help-content code {
            background-color: #f1f5f9;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #dc2626;
        }
    </style>
@stop
