<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modificar tabla products para añadir type y requires_serial
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'type')) {
                $table->string('type', 20)->default('individual')->after('is_active');
            }
            if (!Schema::hasColumn('products', 'requires_serial')) {
                $table->boolean('requires_serial')->default(false)->after('type');
            }
        });

        // 2. Crear tabla pivote product_kit_items
        Schema::create('product_kit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('child_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->unique(['parent_id', 'child_id']);
        });

        // 3. Modificar tabla product_batches
        Schema::table('product_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('product_batches', 'expiration_date')) {
                $table->date('expiration_date')->nullable()->after('expiry_date');
            }
            if (!Schema::hasColumn('product_batches', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('stock_in_item_id');
            }
            if (!Schema::hasColumn('product_batches', 'currency')) {
                $table->string('currency', 3)->nullable()->after('serial_number');
            }
            if (!Schema::hasColumn('product_batches', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('unit_cost');
            }
            if (!Schema::hasColumn('product_batches', 'tax_status')) {
                $table->string('tax_status', 20)->nullable()->after('price');
            }
        });

        // 4. Modificar tabla stock_in_items
        Schema::table('stock_in_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_in_items', 'expiration_date')) {
                $table->date('expiration_date')->nullable()->after('expiry_date');
            }
        });

        // 5. Migrar datos de expiry_date a expiration_date
        DB::table('product_batches')->update([
            'expiration_date' => DB::raw('expiry_date')
        ]);
        DB::table('stock_in_items')->update([
            'expiration_date' => DB::raw('expiry_date')
        ]);

        // 6. Eliminar la columna expiry_date definitivamente
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'expiry_date']);
            $table->dropColumn('expiry_date');
            $table->index(['product_id', 'expiration_date']);
        });
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropColumn('expiry_date');
        });

        // 7. Migrar datos históricos de kits
        $kitIdMap = [];

        if (Schema::hasTable('kits')) {
            $kits = DB::table('kits')->get();
            $defaultUser = DB::table('users')->first();
            $defaultUserId = $defaultUser ? $defaultUser->id : 1;

            foreach ($kits as $kit) {
                // Buscar si ya existe un producto con el mismo código o nombre
                $existingProduct = DB::table('products')
                    ->where('code', $kit->code)
                    ->orWhere('name', $kit->name)
                    ->first();

                if ($existingProduct) {
                    DB::table('products')->where('id', $existingProduct->id)->update([
                        'type' => 'composite_kit',
                        'is_kit' => true,
                    ]);
                    $kitIdMap[$kit->id] = $existingProduct->id;
                } else {
                    $fallbackUnit = DB::table('units')->first();
                    $newProductId = DB::table('products')->insertGetId([
                        'code' => $kit->code ?? ('KIT-' . $kit->id),
                        'name' => $kit->name,
                        'description' => $kit->description ?? '',
                        'is_active' => $kit->is_active ?? true,
                        'price' => $kit->unit_price ?? 0,
                        'type' => 'composite_kit',
                        'is_kit' => true,
                        'is_generic' => true,
                        'unit_id' => $fallbackUnit ? $fallbackUnit->id : 1,
                        'category_id' => null,
                        'location_id' => null,
                        'brand_id' => null,
                        'stock' => 0,
                        'min_stock' => 0,
                        'cost' => 0,
                        'user_id' => $defaultUserId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $kitIdMap[$kit->id] = $newProductId;
                }
            }
        }

        // Migrar relaciones de kit_items a product_kit_items
        if (Schema::hasTable('kit_items')) {
            $kitItems = DB::table('kit_items')->get();
            foreach ($kitItems as $item) {
                $parentId = $kitIdMap[$item->kit_id] ?? null;
                if ($parentId) {
                    DB::table('product_kit_items')->insertOrIgnore([
                        'parent_id' => $parentId,
                        'child_id' => $item->product_id,
                        'quantity' => $item->quantity_required,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 8. Actualizar transacciones existentes que referencien kits a la estructura unificada
        if (Schema::hasColumn('rfq_items', 'kit_id')) {
            $rfqItems = DB::table('rfq_items')->where('item_type', 'kit')->whereNotNull('kit_id')->get();
            foreach ($rfqItems as $item) {
                $newProductId = $kitIdMap[$item->kit_id] ?? null;
                if ($newProductId) {
                    DB::table('rfq_items')->where('id', $item->id)->update([
                        'product_id' => $newProductId,
                        'item_type' => 'product',
                        'kit_id' => null,
                    ]);
                }
            }
        }

        if (Schema::hasColumn('purchase_order_items', 'kit_id')) {
            $poItems = DB::table('purchase_order_items')->where('item_type', 'kit')->whereNotNull('kit_id')->get();
            foreach ($poItems as $item) {
                $newProductId = $kitIdMap[$item->kit_id] ?? null;
                if ($newProductId) {
                    DB::table('purchase_order_items')->where('id', $item->id)->update([
                        'product_id' => $newProductId,
                        'item_type' => 'product',
                        'kit_id' => null,
                    ]);
                }
            }
        }

        if (Schema::hasColumn('request_items', 'kit_id')) {
            $reqItems = DB::table('request_items')->where('item_type', 'kit')->whereNotNull('kit_id')->get();
            foreach ($reqItems as $item) {
                $newProductId = $kitIdMap[$item->kit_id] ?? null;
                if ($newProductId) {
                    DB::table('request_items')->where('id', $item->id)->update([
                        'product_id' => $newProductId,
                        'item_type' => 'product',
                        'kit_id' => null,
                    ]);
                }
            }
        }

        // Poner type = 'composite_kit' para los productos que ya estaban marcados como is_kit
        DB::table('products')->where('is_kit', true)->update(['type' => 'composite_kit']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['type', 'requires_serial']);
        });

        Schema::dropIfExists('product_kit_items');

        // Restaurar expiry_date
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'expiration_date']);
            $table->date('expiry_date')->nullable();
            $table->index(['product_id', 'expiry_date']);
        });
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->date('expiry_date')->nullable();
        });

        DB::table('product_batches')->update([
            'expiry_date' => DB::raw('expiration_date')
        ]);
        DB::table('stock_in_items')->update([
            'expiry_date' => DB::raw('expiration_date')
        ]);

        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropColumn(['expiration_date', 'invoice_number', 'currency', 'price', 'tax_status']);
        });
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropColumn('expiration_date');
        });
    }
};
