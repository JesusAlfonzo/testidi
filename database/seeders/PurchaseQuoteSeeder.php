<?php

namespace Database\Seeders;

use App\Models\PurchaseQuote;
use App\Models\PurchaseQuoteItem;
use App\Models\RequestForQuotation;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseQuoteSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $suppliers = Supplier::all();
        $rfqs = RequestForQuotation::whereIn('status', ['sent', 'closed'])->get();
        $users = User::role(['Superadmin', 'Supervisor', 'Logistica'])->get();
        
        if ($users->isEmpty()) {
            $users = User::all();
        }

        $quotesData = [
            // Cotizaciones en estado pending
            ['status' => 'pending', 'rfq' => true, 'supplier_type' => 'registered', 'item_count' => 3],
            ['status' => 'pending', 'rfq' => true, 'supplier_type' => 'registered', 'item_count' => 5],
            ['status' => 'pending', 'rfq' => true, 'supplier_type' => 'temp', 'item_count' => 4],
            ['status' => 'pending', 'rfq' => false, 'supplier_type' => 'registered', 'item_count' => 6],
            ['status' => 'pending', 'rfq' => false, 'supplier_type' => 'temp', 'item_count' => 2],

            // Cotizaciones en estado selected
            ['status' => 'selected', 'rfq' => true, 'supplier_type' => 'registered', 'item_count' => 4],
            ['status' => 'selected', 'rfq' => true, 'supplier_type' => 'registered', 'item_count' => 7],
            ['status' => 'selected', 'rfq' => true, 'supplier_type' => 'temp', 'item_count' => 3],

            // Cotizaciones en estado approved
            ['status' => 'approved', 'rfq' => true, 'supplier_type' => 'registered', 'item_count' => 5, 'converted' => false],
            ['status' => 'approved', 'rfq' => true, 'supplier_type' => 'registered', 'item_count' => 8, 'converted' => false],
            ['status' => 'approved', 'rfq' => true, 'supplier_type' => 'temp', 'item_count' => 4, 'converted' => false],
            ['status' => 'approved', 'rfq' => true, 'supplier_type' => 'registered', 'item_count' => 6, 'converted' => true],
            ['status' => 'approved', 'rfq' => false, 'supplier_type' => 'registered', 'item_count' => 3, 'converted' => true],

            // Cotizaciones en estado rejected
            ['status' => 'rejected', 'rfq' => true, 'supplier_type' => 'registered', 'item_count' => 4, 'rejection_reason' => 'Precio superior al presupuesto'],
            ['status' => 'rejected', 'rfq' => true, 'supplier_type' => 'temp', 'item_count' => 5, 'rejection_reason' => 'Proveedor sin referencias'],

            // Cotizaciones en estado converted
            ['status' => 'converted', 'rfq' => true, 'supplier_type' => 'registered', 'item_count' => 6],
            ['status' => 'converted', 'rfq' => true, 'supplier_type' => 'registered', 'item_count' => 4],
            ['status' => 'converted', 'rfq' => false, 'supplier_type' => 'registered', 'item_count' => 3],
        ];

        foreach ($quotesData as $quoteData) {
            $user = $users->random();
            $status = $quoteData['status'];
            $itemCount = $quoteData['item_count'];
            $supplierType = $quoteData['supplier_type'];
            $converted = $quoteData['converted'] ?? false;

            $quoteDataCreate = [
                'code' => PurchaseQuote::generateCode(),
                'supplier_reference' => 'REF-' . strtoupper(uniqid()),
                'date_issued' => now()->subDays(rand(1, 30)),
                'valid_until' => now()->addDays(rand(10, 30)),
                'delivery_date' => now()->addDays(rand(15, 45)),
                'currency' => 'USD',
                'exchange_rate' => 17.15,
                'status' => $status,
                'user_id' => $user->id,
                'notes' => 'Cotización de prueba para testing',
            ];

            if ($quoteData['rfq'] && $rfqs->isNotEmpty()) {
                $rfq = $rfqs->random();
                $quoteDataCreate['rfq_id'] = $rfq->id;
            }

        if ($supplierType === 'registered') {
                if ($suppliers->isNotEmpty()) {
                    $quoteDataCreate['supplier_id'] = $suppliers->random()->id;
                } else {
                    $this->command->warn('No suppliers found, skipping quote creation');
                    continue;
                }
            } else {
                if ($suppliers->isNotEmpty()) {
                    $quoteDataCreate['supplier_id'] = $suppliers->random()->id;
                } else {
                    $this->command->warn('No suppliers found, skipping quote creation');
                    continue;
                }
                $quoteDataCreate['supplier_name_temp'] = 'Proveedor Temporal ' . rand(1, 999);
                $quoteDataCreate['supplier_email_temp'] = 'temp' . rand(1, 999) . '@example.com';
                $quoteDataCreate['supplier_phone_temp'] = '55-' . rand(1000, 9999) . '-' . rand(1000, 9999);
            }

            if ($status === 'selected') {
                $quoteDataCreate['status'] = 'pending';
                $quote = PurchaseQuote::create($quoteDataCreate);
                $quote->update(['status' => 'selected']);
            } elseif ($status === 'approved') {
                $quoteDataCreate['status'] = 'pending';
                $quote = PurchaseQuote::create($quoteDataCreate);
                $quote->update([
                    'status' => 'selected',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
                $quote->update(['status' => 'approved']);
            } elseif ($status === 'rejected') {
                $quoteDataCreate['status'] = 'pending';
                $quote = PurchaseQuote::create($quoteDataCreate);
                $quote->update(['status' => 'rejected', 'rejection_reason' => $quoteData['rejection_reason']]);
            } elseif ($status === 'converted') {
                $quoteDataCreate['status'] = 'approved';
                $quote = PurchaseQuote::create($quoteDataCreate);
                $quote->update(['status' => 'converted']);
            } else {
                $quote = PurchaseQuote::create($quoteDataCreate);
            }

            $selectedProducts = $products->random($itemCount);
            $subtotal = 0;
            foreach ($selectedProducts as $product) {
                $quantity = rand(5, 50);
                $unitCost = rand(100, 5000) / 100;
                $totalCost = $quantity * $unitCost;
                $subtotal += $totalCost;

                PurchaseQuoteItem::create([
                    'purchase_quote_id' => $quote->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ]);
            }

            $quote->update([
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total' => $subtotal,
            ]);
        }

        $this->command->info('Cotizaciones creadas: ' . count($quotesData));
    }
}
