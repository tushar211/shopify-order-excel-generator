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
        $admin_shop_url = env('SHOP_URL');
        $token = env('SHOPIFY_ACCESS_TOKEN');

        $currentDate = Carbon::parse($startDate, 'Asia/Kolkata')->startOfDay();
        $endDate = Carbon::parse($endDate, 'Asia/Kolkata')->endOfDay();

        // Loop through each day in the date range
        while ($currentDate->lte($endDate)) {
            $dayStartUtc = $currentDate->copy()->startOfDay()->setTimezone('Asia/Kolkata')->toIso8601String();
            $dayEndUtc   = $currentDate->copy()->endOfDay()->setTimezone('Asia/Kolkata')->toIso8601String();

            $url = "https://$admin_shop_url/admin/api/2025-01/orders.json";
            $params = [
                'status'          => 'any',
                'limit'           => 250,
                'created_at_min'  => $dayStartUtc,
                'created_at_max'  => $dayEndUtc,
            ];
            set_time_limit(0);
            do {
                $response = Http::withHeaders([
                    'X-Shopify-Access-Token' => $token,
                ])->withoutVerifying()->get($url, $params);

                if (!$response->successful()) {
                    break;
                }

                $orders = $response->json()['orders'] ?? [];

                foreach ($orders as $order) {
                    $skus = [];
                    $quantities = 0;
                    $prices = 0;
                    $costs = 0;

                    foreach ($order['line_items'] as $item) {
                        $skus[] = $item['sku'] ?? '';
                        $quantities += $item['quantity'];
                        $prices += $item['price'];
                        if (!empty($item['sku'])) {
                            $product = Product::where('sku', $item['sku'])->first();
                            $costs += $product ? $product->cost : 0;
                        }
                    }

                    $financialStatus = $order['financial_status'] === 'pending'
                        ? 'cod'
                        : $order['financial_status'];

                    $ordersData[] = [
                        'name'               => $order['name'],
                        'skus'               => implode(", ", array_filter($skus)),
                        'financial_status'   => $financialStatus,
                        'costs'              => $costs,
                        'prices'             => $prices,
                        'total_price'        => $order['total_price'],
                        'fulfillment_status' => $order['fulfillment_status'] ?? null,
                        'quantities'         => $quantities,
                        'created_at'         => $order['created_at'],
                    ];
                }
                usleep(500000);
                // pagination: follow next page if present
                $linkHeader = $response->header('Link');
                $nextPageUrl = null;

                if ($linkHeader && preg_match('/<([^>]+)>; rel="next"/', $linkHeader, $matches)) {
                    $nextPageUrl = $matches[1];
                }

                if ($nextPageUrl) {
                    $url = $nextPageUrl;
                    $params = []; // clear params, nextPageUrl already has page_info
                }

            } while ($nextPageUrl);

            // move to the next day
            $currentDate->addDay();
        }

        return $ordersData;
    }
}
