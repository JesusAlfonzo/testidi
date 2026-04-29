<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class HelpController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $role = $user->getRoleNames()->first() ?? 'solicitante';
        
        $modules = $this->getModulesByRole($role);
        
        return view('admin.help.index', compact('role', 'modules'));
    }
    
    public function show($section)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $role = $user->getRoleNames()->first() ?? 'solicitante';
        
        $allowedSections = $this->getAllowedSections($role);
        
        if (!in_array($section, $allowedSections)) {
            abort(403, 'Sección no disponible para su rol');
        }
        
        $content = $this->getSectionContent($section);
        $htmlContent = $this->markdownToHtml($content);
        
        $sectionTitles = [
            'general' => 'Manual General',
            'solicitante' => 'Mis Solicitudes',
            'logistica' => 'Rol Logística',
            'supervisor' => 'Rol Supervisor',
            'superadmin' => 'Rol Superadmin',
            'productos' => 'Gestión de Productos',
            'kits' => 'Kits de Productos',
            'entradas' => 'Entradas de Stock',
            'solicitudes' => 'Solicitudes',
            'maestros' => 'Módulos Maestros',
            'ordenes' => 'Órdenes de Compra',
            'rfq' => 'RFQ',
            'reportes' => 'Reportes',
            'usuarios' => 'Gestión de Usuarios',
            'roles' => 'Gestión de Roles',
            'auditoria' => 'Auditoría'
        ];
        
        $title = $sectionTitles[$section] ?? 'Ayuda';
    
        return view('admin.help.section', [
            'role' => $role,
            'section' => $section,
            'title' => $title,
            'content' => $htmlContent
        ]);
    }
    
    public function downloadPdf($section)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $role = $user->getRoleNames()->first() ?? 'solicitante';
        
        $allowedSections = $this->getAllowedSections($role);
        
        if (!in_array($section, $allowedSections)) {
            abort(403, 'Sección no disponible para su rol');
        }
        
        $content = $this->getSectionContent($section);
        
        $sectionTitles = [
            'general' => 'Manual General',
            'solicitante' => 'Guía Solicitante',
            'logistica' => 'Guía Logística',
            'supervisor' => 'Guía Supervisor',
            'superadmin' => 'Guía Superadmin'
        ];
        $title = $sectionTitles[$section] ?? 'Ayuda SGCI-IDI';
        
        // Generar HTML completo con estilos para PDF
        $html = $this->generatePdfHtml($title, $content);
        
        // Generar PDF usando DomPDF
        try {
            $pdf = PDF::loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            return $pdf->download("ayuda-{$section}.pdf");
        } catch (\Exception $e) {
            // Si falla, devolver HTML
            return response($html)->header('Content-Type', 'text/html');
        }
    }
    
    private function generatePdfHtml($title, $content)
    {
        $styledContent = $this->markdownToHtml($content);
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            color: #1d4ed8;
            font-size: 24px;
            margin: 0;
        }
        .header p {
            color: #666;
            font-size: 12px;
            margin: 5px 0 0 0;
        }
        h1 {
            font-size: 22px;
            color: #1d4ed8;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 8px;
            margin-top: 25px;
        }
        h2 {
            font-size: 18px;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
            padding-left: 10px;
            margin-top: 20px;
        }
        h3 {
            font-size: 15px;
            color: #1e3a8a;
            background-color: #e0e7ff;
            padding: 6px 10px;
            border-radius: 4px;
            margin-top: 15px;
        }
        p {
            text-align: justify;
            margin-bottom: 12px;
        }
        ul, ol {
            margin-left: 20px;
            margin-bottom: 12px;
        }
        li {
            margin-bottom: 5px;
        }
        strong {
            color: #1e3a8a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$title}</h1>
        <p>Sistema de Gestión de Compras e Inventario - SGCI-IDI</p>
    </div>
    <div class="content">
        {$styledContent}
    </div>
    <div class="footer">
        <p>Documento generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
HTML;
    }
    
    private function markdownToHtml($markdown)
    {
        $html = $markdown;
        
        // Convertir bloques de código
        $html = preg_replace('/```(\w*)\n([\s\S]*?)```/m', '<pre><code>$2</code></pre>', $html);
        
        // Encabezados
        $html = preg_replace('/^#### (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
        
        // Negritas y cursivas
        $html = preg_replace('/\*\*(.+?)\*\*\*/', '<strong><em>$1</em></strong>', $html);
        $html = preg_replace('/\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
        
        // Listas con guiones
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        
        // Listas con números
        $html = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $html);
        
        // Envolver listas consecutivas en <ul>
        $html = preg_replace('/(<li>.*<\/li>\n?)+/', '<ul>$0</ul>', $html);
        
        // Tablas - proceso más robusto
        $html = $this->convertTables($html);
        
        // Saltos de línea
        $html = nl2br($html);
        
        // Limpiar saltos de línea extra alrededor de bloques
        $html = preg_replace('/(<h[1-6]>.*<\/h[1-6]>)\s*(<br\s*\/?>)/i', '$1', $html);
        $html = preg_replace('/(<pre>.*<\/pre>)\s*(<br\s*\/?>)/si', '$1', $html);
        
        return $html;
    }
    
    private function convertTables($text)
    {
        // Pattern para capturar tablas markdown completas
        $pattern = '/(\|.+\|\n)+/';
        
        return preg_replace_callback($pattern, function($matches) {
            $table = trim($matches[0]);
            $lines = explode("\n", $table);
            
            if (count($lines) < 2) return $table;
            
            $html = '<table>';
            
            foreach ($lines as $index => $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Saltar líneas de separación (|---|---|)
                if (preg_match('/^[\|\-:\s]+$/', $line)) continue;
                
                // Limpiar | al inicio y final
                $line = trim($line, '|');
                $cells = explode('|', $line);
                
                $cells = array_map('trim', $cells);
                
                if ($index === 0) {
                    $html .= '<thead><tr>';
                    foreach ($cells as $cell) {
                        $html .= '<th>' . $cell . '</th>';
                    }
                    $html .= '</tr></thead><tbody>';
                } else {
                    $html .= '<tr>';
                    foreach ($cells as $cell) {
                        $html .= '<td>' . $cell . '</td>';
                    }
                    $html .= '</tr>';
                }
            }
            
            $html .= '</tbody></table>';
            return $html;
        }, $text);
    }
    
    private function getModulesByRole($role)
    {
        $modules = [
            'general' => [
                'title' => 'Manual General',
                'description' => 'Introducción y conceptos básicos del sistema',
                'icon' => 'fas fa-book',
                'section' => 'general'
            ]
        ];
        
        $roleModules = [
            'solicitante' => [
                'solicitudes' => [
                    'title' => 'Mis Solicitudes',
                    'description' => 'Cómo crear y seguir solicitudes',
                    'icon' => 'fas fa-file-alt',
                    'section' => 'solicitante'
                ]
            ],
            'logistica' => [
                'productos' => [
                    'title' => 'Gestión de Productos',
                    'description' => 'Crear, editar y administrar productos',
                    'icon' => 'fas fa-box',
                    'section' => 'productos'
                ],
                'kits' => [
                    'title' => 'Kits de Productos',
                    'description' => 'Crear y gestionar kits',
                    'icon' => 'fas fa-cubes',
                    'section' => 'kits'
                ],
                'entradas' => [
                    'title' => 'Entradas de Stock',
                    'description' => 'Registrar ingresos de mercancía',
                    'icon' => 'fas fa-truck-loading',
                    'section' => 'entradas'
                ],
                'solicitudes' => [
                    'title' => 'Aprobar Solicitudes',
                    'description' => 'Revisar y aprobar solicitudes',
                    'icon' => 'fas fa-check-circle',
                    'section' => 'aprobacion-solicitudes'
                ],
                'maestros' => [
                    'title' => 'Módulos Maestros',
                    'description' => 'Categorías, unidades, ubicaciones, marcas, proveedores',
                    'icon' => 'fas fa-cog',
                    'section' => 'maestros'
                ],
                'ordenes' => [
                    'title' => 'Órdenes de Compra',
                    'description' => 'Gestionar órdenes de compra',
                    'icon' => 'fas fa-shopping-cart',
                    'section' => 'ordenes-compra'
                ],
                'reportes' => [
                    'title' => 'Reportes',
                    'description' => 'Stock, movimientos y kardex',
                    'icon' => 'fas fa-chart-bar',
                    'section' => 'reportes'
                ]
            ],
            'supervisor' => [
                'productos' => [
                    'title' => 'Gestión de Productos',
                    'description' => 'Crear, editar y administrar productos',
                    'icon' => 'fas fa-box',
                    'section' => 'productos'
                ],
                'kits' => [
                    'title' => 'Kits de Productos',
                    'description' => 'Crear y gestionar kits',
                    'icon' => 'fas fa-cubes',
                    'section' => 'kits'
                ],
                'entradas' => [
                    'title' => 'Entradas de Stock',
                    'description' => 'Registrar ingresos de mercancía',
                    'icon' => 'fas fa-truck-loading',
                    'section' => 'entradas'
                ],
                'solicitudes' => [
                    'title' => 'Aprobar Solicitudes',
                    'description' => 'Revisar y aprobar solicitudes',
                    'icon' => 'fas fa-check-circle',
                    'section' => 'aprobacion-solicitudes'
                ],
                'maestros' => [
                    'title' => 'Módulos Maestros',
                    'description' => 'Categorías, unidades, ubicaciones, marcas, proveedores',
                    'icon' => 'fas fa-cog',
                    'section' => 'maestros'
                ],
                'ordenes' => [
                    'title' => 'Órdenes de Compra',
                    'description' => 'Gestionar órdenes de compra',
                    'icon' => 'fas fa-shopping-cart',
                    'section' => 'ordenes-compra'
                ],
                'rfq' => [
                    'title' => 'RFQ',
                    'description' => 'Solicitudes de cotización',
                    'icon' => 'fas fa-file-contract',
                    'section' => 'rfq'
                ],
                'reportes' => [
                    'title' => 'Reportes Avanzados',
                    'description' => 'Stock, movimientos, kardex y auditoría',
                    'icon' => 'fas fa-chart-line',
                    'section' => 'reportes-avanzados'
                ]
            ],
            'superadmin' => [
                'usuarios' => [
                    'title' => 'Gestión de Usuarios',
                    'description' => 'Crear, editar y eliminar usuarios',
                    'icon' => 'fas fa-users',
                    'section' => 'usuarios'
                ],
                'roles' => [
                    'title' => 'Gestión de Roles',
                    'description' => 'Administrar roles y permisos',
                    'icon' => 'fas fa-user-shield',
                    'section' => 'roles'
                ],
                'productos' => [
                    'title' => 'Gestión de Productos',
                    'description' => 'Crear, editar y administrar productos',
                    'icon' => 'fas fa-box',
                    'section' => 'productos'
                ],
                'kits' => [
                    'title' => 'Kits de Productos',
                    'description' => 'Crear y gestionar kits',
                    'icon' => 'fas fa-cubes',
                    'section' => 'kits'
                ],
                'entradas' => [
                    'title' => 'Entradas de Stock',
                    'description' => 'Registrar ingresos de mercancía',
                    'icon' => 'fas fa-truck-loading',
                    'section' => 'entradas'
                ],
                'solicitudes' => [
                    'title' => 'Aprobar Solicitudes',
                    'description' => 'Revisar y aprobar solicitudes',
                    'icon' => 'fas fa-check-circle',
                    'section' => 'aprobacion-solicitudes'
                ],
                'maestros' => [
                    'title' => 'Módulos Maestros',
                    'description' => 'Categorías, unidades, ubicaciones, marcas, proveedores',
                    'icon' => 'fas fa-cog',
                    'section' => 'maestros'
                ],
                'ordenes' => [
                    'title' => 'Órdenes de Compra',
                    'description' => 'Gestionar órdenes de compra',
                    'icon' => 'fas fa-shopping-cart',
                    'section' => 'ordenes-compra'
                ],
                'rfq' => [
                    'title' => 'RFQ',
                    'description' => 'Solicitudes de cotización',
                    'icon' => 'fas fa-file-contract',
                    'section' => 'rfq'
                ],
                'reportes' => [
                    'title' => 'Reportes Avanzados',
                    'description' => 'Stock, movimientos, kardex y auditoría',
                    'icon' => 'fas fa-chart-line',
                    'section' => 'reportes-avanzados'
                ],
                'auditoria' => [
                    'title' => 'Auditoría',
                    'description' => 'Ver log de actividades',
                    'icon' => 'fas fa-history',
                    'section' => 'auditoria'
                ]
            ]
        ];
        
        return array_merge($modules, $roleModules[$role] ?? []);
    }
    
    private function getAllowedSections($role)
    {
        $allSections = ['general'];
        
        $roleSections = [
            'solicitante' => ['general', 'solicitante'],
            'logistica' => ['general', 'logistica', 'productos', 'kits', 'entradas', 'solicitudes', 'maestros', 'ordenes', 'reportes'],
            'supervisor' => ['general', 'supervisor', 'productos', 'kits', 'entradas', 'solicitudes', 'maestros', 'ordenes', 'rfq', 'reportes-avanzados'],
            'superadmin' => ['general', 'superadmin', 'productos', 'kits', 'entradas', 'solicitudes', 'maestros', 'ordenes', 'rfq', 'reportes-avanzados', 'usuarios', 'roles', 'auditoria']
        ];
        
        return $roleSections[$role] ?? ['general'];
    }
    
    private function getSectionContent($section)
    {
        $docsPath = base_path('docs/usuario');
        
        $sectionFiles = [
            'general' => 'manual-general.md',
            'solicitante' => 'rol-solicitante.md',
            'logistica' => 'rol-logistica.md',
            'supervisor' => 'rol-supervisor.md',
            'superadmin' => 'rol-superadmin.md'
        ];
        
        if (isset($sectionFiles[$section])) {
            $filePath = $docsPath . '/' . $sectionFiles[$section];
            if (file_exists($filePath)) {
                return file_get_contents($filePath);
            }
        }
        
        return '<h1>Sección en desarrollo</h1><p>Esta sección de ayuda aún no está disponible.</p>';
    }
}
