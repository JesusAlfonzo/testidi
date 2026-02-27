<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $suppliers = [
            // Proveedores de Reactivos
            ['name' => 'Sigma-Aldrich México', 'tax_id' => 'SAM-001', 'email' => 'mexico@sigmaaldrich.com', 'phone' => '55-1234-5678', 'contact_person' => 'Ing. Roberto Sánchez'],
            ['name' => 'Merck México', 'tax_id' => 'MMX-002', 'email' => 'ventas@merck.mx', 'phone' => '55-2345-6789', 'contact_person' => 'Dra. Ana López'],
            ['name' => 'Thermo Fisher Scientific', 'tax_id' => 'TFS-003', 'email' => 'ordersmx@thermofisher.com', 'phone' => '55-3456-7890', 'contact_person' => 'Lic. Carlos Martínez'],
            ['name' => 'Bio-Rad Laboratories', 'tax_id' => 'BRL-004', 'email' => 'bio-rad@mx.bio-rad.com', 'phone' => '55-4567-8901', 'contact_person' => 'Ing. Patricia González'],
            ['name' => 'Agilent Technologies', 'tax_id' => 'AGT-005', 'email' => 'mexico@agilent.com', 'phone' => '55-5678-9012', 'contact_person' => 'Mtro. Fernando Rodríguez'],
            ['name' => 'QIAGEN México', 'tax_id' => 'QMX-006', 'email' => 'ventas.mx@qiagen.com', 'phone' => '55-6789-0123', 'contact_person' => 'Lic. María Hernández'],
            ['name' => 'Promega Corporation', 'tax_id' => 'PMC-007', 'email' => 'mexico@promega.com', 'phone' => '55-7890-1234', 'contact_person' => 'Dr. José Luis Torres'],
            ['name' => 'Roche Diagnostics', 'tax_id' => 'RDC-008', 'email' => 'servicio.cliente@roche.com', 'phone' => '55-8901-2345', 'contact_person' => 'Dra. Laura Jiménez'],
            ['name' => 'Abbott Laboratories', 'tax_id' => 'ABL-009', 'email' => 'mexico@abbott.com', 'phone' => '55-9012-3456', 'contact_person' => 'Ing. David Morales'],
            ['name' => 'Beckman Coulter México', 'tax_id' => 'BCM-010', 'email' => 'beckmex@beckman.com', 'phone' => '55-0123-4567', 'contact_person' => 'Lic. Sandra Ramírez'],

            // Proveedores de Material de Vidrio
            ['name' => 'Corning México', 'tax_id' => 'CMX-011', 'email' => 'ventas@corning.com.mx', 'phone' => '55-1111-2222', 'contact_person' => 'Ing. Alejandro Cruz'],
            ['name' => 'Schott AG México', 'tax_id' => 'SAG-012', 'email' => 'mexico@schott.com', 'phone' => '55-2222-3333', 'contact_person' => 'Dra. Isabel Flores'],
            ['name' => 'VWR International', 'tax_id' => 'VWR-013', 'email' => 'vwr.mexico@vwr.com', 'phone' => '55-3333-4444', 'contact_person' => 'Lic. Ricardo Núñez'],
            ['name' => 'Fisher Scientific México', 'tax_id' => 'FSM-014', 'email' => 'orders.fishermx@thermofisher.com', 'phone' => '55-4444-5555', 'contact_person' => 'Ing. Gabriela Peña'],
            ['name' => 'Kimble Chase', 'tax_id' => 'KCM-015', 'email' => 'ventas@kimble.mx', 'phone' => '55-5555-6666', 'contact_person' => 'Mtro. Eduardo López'],

            // Proveedores de Material Plástico
            ['name' => 'Corning Costar México', 'tax_id' => 'CCM-016', 'email' => 'costar.mx@corning.com', 'phone' => '55-6666-7777', 'contact_person' => 'Lic. Andrea Herrera'],
            ['name' => 'BD Biosciences', 'tax_id' => 'BDB-017', 'email' => 'bdfacs.mx@bd.com', 'phone' => '55-7777-8888', 'contact_person' => 'Dr. Miguel Ángel Soto'],
            ['name' => 'Greiner Bio-One', 'tax_id' => 'GBO-018', 'email' => 'ventas.mx@greiner-bio.com', 'phone' => '55-8888-9999', 'contact_person' => 'Ing. Carmen Delgado'],
            ['name' => 'Eppendorf México', 'tax_id' => 'EPM-019', 'email' => 'eppendorf.mx@eppendorf.com', 'phone' => '55-9999-0000', 'contact_person' => 'Lic. Luis Fernando Mendoza'],
            ['name' => 'Gilson Inc. México', 'tax_id' => 'GIM-020', 'email' => 'ventas.mx@gilson.com', 'phone' => '52-1111-1111', 'contact_person' => 'Dra. Norma Alicia Reyes'],

            // Proveedores de Equipos
            ['name' => 'Eppendorf México', 'tax_id' => 'EPM2-021', 'email' => 'soporte@eppendorf.mx', 'phone' => '52-2222-2222', 'contact_person' => 'Ing. Jorge Alberto Campos'],
            ['name' => 'Mettler Toledo México', 'tax_id' => 'MTM-022', 'email' => 'ventas.mx@mt.com', 'phone' => '52-3333-3333', 'contact_person' => 'Lic. Luz María Aguirre'],
            ['name' => 'Thermo Scientific México', 'tax_id' => 'TSM-023', 'email' => 'Thermo.mx@thermofisher.com', 'phone' => '52-4444-4444', 'contact_person' => 'Dr. Sergio Iván Romero'],
            ['name' => 'Beckman Coulter', 'tax_id' => 'BC2-024', 'email' => 'beckman.mx@beckman.com', 'phone' => '52-5555-5555', 'contact_person' => 'Ing. Vanessa Pérez'],
            ['name' => 'Sartorius México', 'tax_id' => 'SRM-025', 'email' => 'ventas.mx@sartorius.com', 'phone' => '52-6666-6666', 'contact_person' => 'Lic. Omar González'],

            // Proveedores de Kits de Diagnóstico
            ['name' => 'Roche Diagnóstica', 'tax_id' => 'RCD-026', 'email' => 'roche.dx.mx@roche.com', 'phone' => '52-7777-7777', 'contact_person' => 'Dra. Adriana Fuentes'],
            ['name' => 'Abbott Diagnostics', 'tax_id' => 'ADI-027', 'email' => 'abbott.dx.mx@abbott.com', 'phone' => '52-8888-8888', 'contact_person' => 'Ing. César Urrutia'],
            ['name' => 'Siemens Healthineers', 'tax_id' => 'SHM-028', 'email' => 'mexico.health@siemens.com', 'phone' => '52-9999-9999', 'contact_person' => 'Lic. Teresa Estrada'],
            ['name' => 'Ortho Clinical Diagnostics', 'tax_id' => 'OCD-029', 'email' => 'ocd.mexico@orthoclinical.com', 'phone' => '55-0000-1111', 'contact_person' => 'Dr. Ernesto Vázquez'],
            ['name' => 'BioMérieux México', 'tax_id' => 'BMX-030', 'email' => 'biomerieux.mx@biomerieux.com', 'phone' => '55-0000-2222', 'contact_person' => 'Ing. Patricia Mercado'],

            // Proveedores de Equipamiento Menor
            ['name' => 'LabPro México', 'tax_id' => 'LPM-031', 'email' => 'ventas@labpro.com.mx', 'phone' => '55-0000-3333', 'contact_person' => 'Lic. Hugo Chávez'],
            ['name' => 'Scientific Suppliers', 'tax_id' => 'SSU-032', 'email' => 'info@scientificsuppliers.mx', 'phone' => '55-0000-4444', 'contact_person' => 'Dra. Marisol Luna'],
            ['name' => 'LabMarket México', 'tax_id' => 'LMX-033', 'email' => 'ventas@labmarket.com.mx', 'phone' => '55-0000-5555', 'contact_person' => 'Ing. Francisco Javier Salazar'],
            ['name' => 'Equipos Lab México', 'tax_id' => 'ELM-034', 'email' => 'contacto@equiposlab.mx', 'phone' => '55-0000-6666', 'contact_person' => 'Lic.Beatriz Adriana Ibarra'],
            ['name' => 'Biosuministros SA de CV', 'tax_id' => 'BSA-035', 'email' => 'ventas@biosuministros.com.mx', 'phone' => '55-0000-7777', 'contact_person' => 'Dr. Gabriel Ocampo'],

            // Proveedores de Consumibles Generales
            ['name' => 'JGBEq SA de CV', 'tax_id' => 'JGB-036', 'email' => 'jgbeq@jgbeq.com.mx', 'phone' => '55-1111-0000', 'contact_person' => 'Ing. Armando Casas'],
            ['name' => 'LabCenter', 'tax_id' => 'LCN-037', 'email' => 'labcenter@labcenter.com.mx', 'phone' => '55-1111-1111', 'contact_person' => 'Lic. Sofía Calderón'],
            ['name' => 'Proquisa Diagnósticos', 'tax_id' => 'PQD-038', 'email' => 'proquisa@proquisa.com.mx', 'phone' => '55-1111-2222', 'contact_person' => 'Dra. Xóchitl Montserrat Alvarado'],
            ['name' => 'Diagomédica', 'tax_id' => 'DGM-039', 'email' => 'ventas@diagomedica.com.mx', 'phone' => '55-1111-3333', 'contact_person' => 'Ing. Israel Mendoza'],
            ['name' => 'Innolab México', 'tax_id' => 'ILM-040', 'email' => 'info@innolab.mx', 'phone' => '55-1111-4444', 'contact_person' => 'Lic. Yolanda Cruz'],

            // Proveedores Internacionales
            ['name' => 'Cellvis Technologies', 'tax_id' => 'CVT-041', 'email' => 'orders@cellvis.com', 'phone' => '+1-800-123-4567', 'contact_person' => 'John Smith'],
            ['name' => 'GenScript USA', 'tax_id' => 'GSU-042', 'email' => 'ordering@genscript.com', 'phone' => '+1-877-436-7278', 'contact_person' => 'Mary Johnson'],
            ['name' => 'Takara Bio USA', 'tax_id' => 'TBU-043', 'email' => 'orders@takarabio.com', 'phone' => '+1-800-662-2561', 'contact_person' => 'David Lee'],
            ['name' => 'New England Biolabs', 'tax_id' => 'NEB-044', 'email' => 'orders@neb.com', 'phone' => '+1-800-632-7767', 'contact_person' => 'Jennifer White'],
            ['name' => 'Illumina Inc.', 'tax_id' => 'ILU-045', 'email' => 'sales@illumina.com', 'phone' => '+1-800-809-4567', 'contact_person' => 'Robert Brown'],

            // Más proveedores locales
            ['name' => 'Inversiones LABC', 'tax_id' => 'ILC-046', 'email' => 'ventas@inversioneslabc.mx', 'phone' => '55-2222-1111', 'contact_person' => 'Ing. Ernesto Fuentes'],
            ['name' => 'Proveedora de Laboratorios del Norte', 'tax_id' => 'PLN-047', 'email' => 'pln@proveedora-laboratorios.com', 'phone' => '81-1234-5678', 'contact_person' => 'Lic. Daniel Garza'],
            ['name' => 'Bioquimia SA de CV', 'tax_id' => 'BQM-048', 'email' => 'bioquimia@bioquimia.com.mx', 'phone' => '33-1234-5678', 'contact_person' => 'Dra. Rosario Angélica'],
            ['name' => 'Suministros Analíticos', 'tax_id' => 'SAN-049', 'email' => 'suministros@suministrosanaliticos.mx', 'phone' => '55-3333-1111', 'contact_person' => 'Ing. Alejandro Briseño'],
            ['name' => 'LabDiagnósticos del Pacífico', 'tax_id' => 'LDP-050', 'email' => 'labdiagnosticos@pacifico.com.mx', 'phone' => '33-9876-5432', 'contact_person' => 'Lic. Cristina López'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create([
                'name' => $supplier['name'],
                'tax_id' => $supplier['tax_id'],
                'email' => $supplier['email'],
                'phone' => $supplier['phone'],
                'contact_person' => $supplier['contact_person'],
                'address' => 'Av. Principal #' . rand(100, 9999) . ', Col. Industrial, Ciudad de México',
                'user_id' => $user->id,
            ]);
        }
    }
}
