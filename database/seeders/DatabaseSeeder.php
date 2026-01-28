<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    // public function run(): void
    // {
    //     // User::factory(10)->create();

    //     User::factory()->create([
    //         'name' => 'Test User',
    //         'email' => 'test@example.com',
    //     ]);
    // }
     public function run(): void
    {
        // Create Branches
        $branch1 = Branch::create([
            'name' => 'Branch 1 - Nairobi',
            'location' => 'Nairobi CBD',
            'is_active' => true,
        ]);

        $branch2 = Branch::create([
            'name' => 'Branch 2 - Kiambu',
            'location' => 'Ruiru, Kiambu',
            'is_active' => true,
        ]);

        // Create Stores
        $store1 = Store::create([
            'branch_id' => $branch1->id,
            'name' => 'Store 1A - Main',
            'location' => 'Ground Floor, Nairobi CBD',
            'is_active' => true,
        ]);

        $store2 = Store::create([
            'branch_id' => $branch2->id,
            'name' => 'Store 2A - North',
            'location' => 'Ruiru Mall, 1st Floor',
            'is_active' => true,
        ]);

        $store3 = Store::create([
            'branch_id' => $branch2->id,
            'name' => 'Store 2B - South',
            'location' => 'Ruiru Town Center',
            'is_active' => true,
        ]);

        // Create Administrator
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@kkwholesalers.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'branch_id' => null,
            'store_id' => null,
        ]);

        // Create Branch Managers
        User::create([
            'name' => 'John Mwangi',
            'email' => 'john.mwangi@kkwholesalers.com',
            'password' => Hash::make('password123'),
            'role' => 'branch_manager',
            'branch_id' => $branch1->id,
            'store_id' => null,
        ]);

        User::create([
            'name' => 'Mary Njeri',
            'email' => 'mary.njeri@kkwholesalers.com',
            'password' => Hash::make('password123'),
            'role' => 'branch_manager',
            'branch_id' => $branch2->id,
            'store_id' => null,
        ]);

        // Create Store Managers
        User::create([
            'name' => 'Peter Kamau',
            'email' => 'peter.kamau@kkwholesalers.com',
            'password' => Hash::make('password123'),
            'role' => 'store_manager',
            'branch_id' => $branch1->id,
            'store_id' => $store1->id,
        ]);

        User::create([
            'name' => 'Alice Wanjiku',
            'email' => 'alice.wanjiku@kkwholesalers.com',
            'password' => Hash::make('password123'),
            'role' => 'store_manager',
            'branch_id' => $branch2->id,
            'store_id' => $store2->id,
        ]);

        User::create([
            'name' => 'James Ochieng',
            'email' => 'james.ochieng@kkwholesalers.com',
            'password' => Hash::make('password123'),
            'role' => 'store_manager',
            'branch_id' => $branch2->id,
            'store_id' => $store3->id,
        ]);

        // Create 10 Products (SKUs)
        $products = [
            ['sku' => 'SKU-001', 'name' => 'Rice 25kg', 'description' => 'Premium long grain rice', 'unit_price' => 3500.00],
            ['sku' => 'SKU-002', 'name' => 'Sugar 2kg', 'description' => 'White refined sugar', 'unit_price' => 250.00],
            ['sku' => 'SKU-003', 'name' => 'Cooking Oil 5L', 'description' => 'Vegetable cooking oil', 'unit_price' => 1200.00],
            ['sku' => 'SKU-004', 'name' => 'Wheat Flour 2kg', 'description' => 'All-purpose wheat flour', 'unit_price' => 180.00],
            ['sku' => 'SKU-005', 'name' => 'Maize Flour 2kg', 'description' => 'Fine maize flour', 'unit_price' => 150.00],
            ['sku' => 'SKU-006', 'name' => 'Tea Leaves 500g', 'description' => 'Kenya tea leaves', 'unit_price' => 450.00],
            ['sku' => 'SKU-007', 'name' => 'Salt 1kg', 'description' => 'Iodized table salt', 'unit_price' => 50.00],
            ['sku' => 'SKU-008', 'name' => 'Pasta 500g', 'description' => 'Spaghetti pasta', 'unit_price' => 120.00],
            ['sku' => 'SKU-009', 'name' => 'Milk Powder 1kg', 'description' => 'Full cream milk powder', 'unit_price' => 850.00],
            ['sku' => 'SKU-010', 'name' => 'Beans 2kg', 'description' => 'Red kidney beans', 'unit_price' => 280.00],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);

            // Initialize inventory for each store with random quantities
            foreach ([$store1, $store2, $store3] as $store) {
                Inventory::create([
                    'product_id' => $product->id,
                    'store_id' => $store->id,
                    'quantity' => rand(50, 200),
                    'minimum_stock' => rand(10, 30),
                ]);
            }
        }
    }
}
