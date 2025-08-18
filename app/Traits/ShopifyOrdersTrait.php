<?php

namespace App\Traits;

use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

trait ShopifyOrdersTrait
{
    public function fetchFormattedOrders($startDate, $endDate): array
    {
        $ordersData = [];
        $startDateUtc = Carbon::parse($startDate, 'Asia/Kolkata')->startOfDay()->setTimezone('UTC')->toIso8601String();
        $endDateUtc   = Carbon::parse($endDate, 'Asia/Kolkata')->endOfDay()->setTimezone('UTC')->toIso8601String();
        $admin_shop_url = env('SHOP_URL');
        $token = env('SHOPIFY_ACCESS_TOKEN');
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
        ])->withoutVerifying()->get("https://$admin_shop_url/admin/api/2025-01/orders.json", [
            'status'         => 'any',
            'created_at_min' => $startDateUtc,
            'created_at_max' => $endDateUtc,
            'limit'          => 250,
        ]);

        if ($response->successful()) {
            $orders = $response->json()['orders'];

            foreach ($orders as $order) {
                $skus = [];
                $quantities = [];
                $prices = [];
                $costs = [];

                foreach ($order['line_items'] as $item) {
                    $skus[] = $item['sku'] ?? '';
                    $quantities[] = $item['quantity'];
                    $prices[] = $item['price'];
                    if (!empty($item['sku'])) {
                        $product = Product::where('sku', $item['sku'])->first();
                        $costs[] = $product ? $product->cost : 'N/A';
                    } else {
                        $costs[] = 'N/A';
                    }
                }

                // Convert pending to cod
                $financialStatus = $order['financial_status'] === 'pending' ? 'cod' : $order['financial_status'];

                $ordersData[] = [
                    'name'               => $order['name'],
                    'skus'               => implode(", ", $skus),
                    'financial_status'   => $financialStatus,
                    'costs'              => implode(", ", $costs),
                    'prices'             => implode(", ", $prices),
                    'total_price'        => $order['total_price'],
                    'fulfillment_status' => $order['fulfillment_status'],
                    'quantities'         => implode(", ", $quantities),
//                   'created_at'         => $order['created_at'],
                ];
            }
        }

        return $ordersData;
    }
}
