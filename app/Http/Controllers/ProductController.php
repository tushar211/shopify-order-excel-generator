<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('products', compact('products'));
    }

    public function fetchFromShopify()
    {
        $admin_shop_url = env('SHOP_URL');
        $token = env('SHOPIFY_ACCESS_TOKEN');

        // 1️⃣ Fetch products with variants
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
        ])->withoutVerifying()->get("https://$admin_shop_url/admin/api/2025-01/products.json?limit=250");

        if ($response->successful()) {
            $shopifyProducts = $response->json()['products'];


            foreach ($shopifyProducts as $product) {
                $variantsInventoryIds = [];

                // Collect inventory_item_ids for cost API
                foreach ($product['variants'] as $variant) {
                    $variantsInventoryIds[$variant['inventory_item_id']] = $variant;
                }

                // 2️⃣ Fetch cost from InventoryLevel API
                $inventoryResponse = Http::withHeaders([
                    'X-Shopify-Access-Token' => $token,
                ])->withoutVerifying()->get("https://$admin_shop_url/admin/api/2025-01/inventory_items.json", [
                    'ids' => implode(',', array_keys($variantsInventoryIds)),
                ]);


                $inventoryLevels = $inventoryResponse->successful() ? $inventoryResponse->json()['inventory_items'] : [];
                // Map inventory_item_id => cost
                $inventoryCosts = [];
                foreach ($inventoryLevels as $level) {
                    $inventoryCosts[$level['id']] = $level['cost'] ?? 0;
                }

                // Save to database
                foreach ($product['variants'] as $variant) {
                    Product::updateOrCreate(
                        ['shopify_id' => $variant['id']],
                        [
                            'title' => $product['title'],
                            'sku'   => $variant['sku'],
                            'price' => $variant['price'],
                            'cost'  => $inventoryCosts[$variant['inventory_item_id']] ?? 0, // use inventory_item_id
                        ]
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Products synced with costs from Shopify!');
    }
}
