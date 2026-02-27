<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseQuote;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $suppliers = Supplier::all();
        $approvedQuotes = PurchaseQuote::where('status', 'approved')->get();
        $users = User::role(['Superadmin', 'Supervisor', 'Logistica'])->get();
        
        if ($users->isEmpty()) {
            $users = User::all();
        }

        $ordersData = [
            // Órdenes en estado draft
            ['status' => 'draft', 'has_quote' => false, 'item_count' => 4],
            ['status' => 'draft', 'has_quote' => true, 'item_count' => 5],
            ['status' => 'draft', 'has_quote' => false, 'item_count' => 3],

            // Órdenes en estado issued
            ['status' => 'issued', 'has_quote' => true, 'item_count' => 6, 'received_partial' => false],
            ['status' => 'issued', 'has_quote' => true, 'item_count' => 4, 'received_partial' => true],
            ['status' => 'issued', 'has_quote' => false, 'item_count' => 7, 'received_partial' => false],
            ['status' => 'issued', 'has_quote' => true, 'item_count' => 5, 'received_partial' => true],
            ['status' => 'issued', 'has_quote' => false, 'item_count' => 3, 'received_partial' => false],

            // Órdenes en estado completed
            ['status' => 'completed', 'has_quote' => true, 'item_count' => 4],
            ['status' => 'completed', 'has_quote' => true, 'item_count' => 6],
            ['status' => 'completed', 'has_quote' => false, 'item_count' => 5],
            ['status' => 'completed', 'has_quote' => true, 'item_count' => 3],

            // Órdenes en estado cancelled
            ['status' => 'cancelled', 'has_quote' => false, 'item_count' => 2],
            ['status' => 'cancelled', 'has_quote' => true, 'item_count' => 4],
        ];

        foreach ($ordersData as $orderData) {
            $user = $users->random();
            $status = $orderData['status'];
            $itemCount = $orderData['item_count'];

            $orderDataCreate = [
                'code' => PurchaseOrder::generateCode(),
                'date_issued' => now()->subDays(rand(1, 45)),
                'delivery_date' => now()->addDays(rand(10, 30)),
                'delivery_address' => 'Av. Universidad #' . rand(100, 9999) . ', Ciudad de México',
                'currency' => 'USD',
                'exchange_rate' => 17.15,
                'status' => $status === 'cancelled' ? 'draft' : $status,
                'terms' => 'NET ' . rand(15, 60),
                'notes' => 'Orden de compra de prueba',
                'created_by' => $user->id,
            ];

            if ($orderData['has_quote'] && $approvedQuotes->isNotEmpty()) {
                $quote = $approvedQuotes->random();
                $orderDataCreate['purchase_quote_id'] = $quote->id;
                $orderDataCreate['supplier_id'] = $quote->supplier_id;
            } else {
                $orderDataCreate['supplier_id'] = $suppliers->random()->id;
            }

            $order = PurchaseOrder::create($orderDataCreate);

            $selectedProducts = $products->random($itemCount);
            $subtotal = 0;
            foreach ($selectedProducts as $product) {
                $quantity = rand(5, 50);
                $unitCost = rand(100, 5000) / 100;
                $totalCost = $quantity * $unitCost;
                $subtotal += $totalCost;

                $quantityReceived = 0;
                if ($status === 'completed') {
                    $quantityReceived = $quantity;
                } elseif (isset($orderData['received_partial']) && $orderData['received_partial']) {
                    $quantityReceived = rand(1, $quantity - 1);
                }

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $quantity,
                    'quantity_received' => $quantityReceived,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total' => $subtotal,
            ]);

            // Aplicar estado final
            if ($status === 'issued') {
                $order->update([
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
            } elseif ($status === 'cancelled') {
                $order->update(['status' => 'cancelled']);
            }
        }

        $this->command->info('Órdenes de compra creadas: ' . count($ordersData));
    }
}
