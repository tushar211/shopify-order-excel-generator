<?php

namespace App\Http\Controllers;

use App\Exports\ShopifyOrdersExport;
use App\Traits\ShopifyOrdersTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    use ShopifyOrdersTrait;

    public function index(Request $request)
    {
        $startDate = $request->input('startDate') ?: now()->format('Y-m-d');
        $endDate   = $request->input('endDate') ?: now()->format('Y-m-d');

        $orders = $this->fetchFormattedOrders($startDate, $endDate); // using your trait


        return view('orders', compact('orders', 'startDate', 'endDate'));
    }


    public function download(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        return Excel::download(new ShopifyOrdersExport($startDate, $endDate), 'shopify_orders.xlsx');
    }
}
