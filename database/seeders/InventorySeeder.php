<?php

namespace Database\Seeders;

use App\Models\Inventory\Category;
use App\Models\Inventory\LotBatch;
use App\Models\Inventory\Product;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockLocation;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Unit;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            Unit::firstOrCreate(['symbol' => 'pcs'], ['name' => 'Piece']),
            Unit::firstOrCreate(['symbol' => 'kg'], ['name' => 'Kilogram']),
            Unit::firstOrCreate(['symbol' => 'box'], ['name' => 'Box']),
        ];

        $categories = [
            Category::create(['name' => 'Electronics', 'parent_id' => null]),
            Category::create(['name' => 'Office Supplies', 'parent_id' => null]),
            Category::create(['name' => 'Raw Materials', 'parent_id' => null]),
        ];
        Category::create(['name' => 'Cables & Adapters', 'parent_id' => $categories[0]->id]);
        Category::create(['name' => 'Paper & Notebooks', 'parent_id' => $categories[1]->id]);

        $suppliers = [
            Supplier::create(['name' => 'Global Parts Inc', 'email' => 'orders@globalparts.test', 'phone' => '+1-555-0100', 'address' => '123 Industrial Blvd']),
            Supplier::create(['name' => 'Office Depot Wholesale', 'email' => 'wholesale@officedepot.test', 'phone' => '+1-555-0101', 'address' => '456 Commerce St']),
        ];

        $products = [];
        foreach (['Widget A', 'Widget B', 'Cable Type-C', 'Notebook A4', 'Resistor 10k'] as $i => $name) {
            $products[] = Product::create([
                'sku' => 'INV-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'name' => $name,
                'description' => 'Sample product: ' . $name,
                'unit_id' => $units[$i < 3 ? 0 : ($i === 3 ? 2 : 0)]->id,
                'category_id' => $categories[$i % 3]->id,
            ]);
        }

        foreach ($products as $index => $product) {
            $supplier = $suppliers[$index % count($suppliers)];
            $product->suppliers()->attach($supplier->id, [
                'cost_price' => round(10 + $index * 2.5, 2),
                'lead_time_days' => 3 + ($index % 5),
                'minimum_order_qty' => 1,
            ]);
        }

        $locations = [
            StockLocation::create(['name' => 'Main Warehouse', 'type' => 'warehouse']),
            StockLocation::create(['name' => 'Shelf A1', 'type' => 'shelf']),
        ];

        foreach ($products as $index => $product) {
            foreach ($locations as $loc) {
                $stock = Stock::create([
                    'product_id' => $product->id,
                    'location_id' => $loc->id,
                    'quantity' => 100 + $index * 25,
                    'reserved' => $index === 0 ? 10 : 0,
                ]);
                StockMovement::create([
                    'stock_id' => $stock->id,
                    'type' => StockMovement::TYPE_ENTRY,
                    'quantity' => (float) $stock->quantity,
                    'reference_id' => null,
                    'note' => 'Initial stock',
                ]);
            }
        }

        foreach (array_slice($products, 0, 2) as $product) {
            LotBatch::create([
                'product_id' => $product->id,
                'batch_number' => 'LOT-' . $product->id . '-001',
                'expiry_date' => now()->addYear()->format('Y-m-d'),
                'quantity' => 50,
            ]);
        }
    }
}
