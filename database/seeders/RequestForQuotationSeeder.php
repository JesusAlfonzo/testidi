<?php

namespace Database\Seeders;

use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RequestForQuotationSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $users = User::role(['Superadmin', 'Supervisor', 'Logistica'])->get();
        
        if ($users->isEmpty()) {
            $users = User::all();
        }

        $rfqsData = [
            // RFQs en estado draft
            [
                'title' => 'Compra de reactivos para immunology',
                'description' => 'Reactivos necesarios para el laboratorio de immunology del siguiente trimestre',
                'status' => 'draft',
                'date_required' => now()->addDays(30),
                'delivery_deadline' => now()->addDays(45),
                'item_count' => 8,
            ],
            [
                'title' => 'Material de vidrio para almacenamiento',
                'description' => 'Tubos cryogénicos y crioviales para almacenamiento de muestras',
                'status' => 'draft',
                'date_required' => now()->addDays(15),
                'delivery_deadline' => now()->addDays(20),
                'item_count' => 5,
            ],
            [
                'title' => 'Consumibles para PCR',
                'description' => 'Puntas con filtro, microtubos y placas para PCR',
                'status' => 'draft',
                'date_required' => now()->addDays(10),
                'delivery_deadline' => now()->addDays(15),
                'item_count' => 6,
            ],

            // RFQs en estado sent
            [
                'title' => 'Kits de ELISA para diagnóstico',
                'description' => 'Kits ELISA para detección de enfermedades autoinmunes',
                'status' => 'sent',
                'date_required' => now()->addDays(20),
                'delivery_deadline' => now()->addDays(30),
                'item_count' => 4,
            ],
            [
                'title' => 'Equipos de centrifugación',
                'description' => 'Centrífuga refrigerada de alta velocidad',
                'status' => 'sent',
                'date_required' => now()->addDays(60),
                'delivery_deadline' => now()->addDays(90),
                'item_count' => 1,
            ],
            [
                'title' => 'Medios de cultivo especiales',
                'description' => 'Medios especializados para cultivo de células madre',
                'status' => 'sent',
                'date_required' => now()->addDays(25),
                'delivery_deadline' => now()->addDays(35),
                'item_count' => 7,
            ],
            [
                'title' => 'Sueros y reactivos para Western Blot',
                'description' => 'Anticuerpos primarios y secundarios para Western Blot',
                'status' => 'sent',
                'date_required' => now()->addDays(15),
                'delivery_deadline' => now()->addDays(25),
                'item_count' => 10,
            ],
            [
                'title' => 'Material plástico estéril para cultivo',
                'description' => 'Placas, pipetas y tubos estériles para cultivo celular',
                'status' => 'sent',
                'date_required' => now()->addDays(7),
                'delivery_deadline' => now()->addDays(14),
                'item_count' => 12,
            ],

            // RFQs en estado closed
            [
                'title' => 'Reactivos de biología molecular',
                'description' => 'Enzimas, tampones y nucleótidos para proyectos de investigación',
                'status' => 'closed',
                'date_required' => now()->subDays(5),
                'delivery_deadline' => now()->subDays(2),
                'item_count' => 9,
            ],
            [
                'title' => 'Equipos de medición pH',
                'description' => 'pH-metros de mesa y electrodos de repuesto',
                'status' => 'closed',
                'date_required' => now()->subDays(10),
                'delivery_deadline' => now()->subDays(5),
                'item_count' => 3,
            ],

            // RFQs en estado cancelled
            [
                'title' => 'Compra de microscopio (CANCELADO)',
                'description' => 'Microscopio de fluorescencia - Cancelado por cambio de prioridades',
                'status' => 'cancelled',
                'date_required' => now()->subDays(20),
                'delivery_deadline' => now()->subDays(10),
                'item_count' => 1,
            ],
            [
                'title' => 'Kits de secuenciación (CANCELADO)',
                'description' => 'Proyecto pospuesto indefinidamente',
                'status' => 'cancelled',
                'date_required' => now()->subDays(30),
                'delivery_deadline' => now()->subDays(20),
                'item_count' => 2,
            ],
        ];

        foreach ($rfqsData as $rfqData) {
            $user = $users->random();
            $status = $rfqData['status'];
            $itemCount = $rfqData['item_count'];
            
            $rfq = RequestForQuotation::create([
                'code' => RequestForQuotation::generateCode(),
                'title' => $rfqData['title'],
                'description' => $rfqData['description'],
                'date_required' => $rfqData['date_required'],
                'delivery_deadline' => $rfqData['delivery_deadline'],
                'status' => $status,
                'notes' => $status === 'cancelled' ? 'Cancelado por cambio de prioridades del proyecto' : null,
                'created_by' => $user->id,
            ]);

            $selectedProducts = $products->random($itemCount);
            foreach ($selectedProducts as $product) {
                RfqItem::create([
                    'rfq_id' => $rfq->id,
                    'product_id' => $product->id,
                    'quantity' => rand(5, 50),
                    'notes' => 'Cantidad mensual estimada',
                ]);
            }
        }

        $this->command->info('RFQs creados: ' . count($rfqsData));
    }
}
