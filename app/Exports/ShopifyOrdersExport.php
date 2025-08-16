<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use App\Traits\ShopifyOrdersTrait;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ShopifyOrdersExport implements FromArray, WithHeadings
{
    use ShopifyOrdersTrait;
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        return $this->fetchFormattedOrders($this->startDate, $this->endDate);
    }

    public function headings(): array
    {
        return ["Order Name", "SKU", "Payment Status", "Cost per item", "Sale Price", "Payment Receive", "Fulfillment Status", "Quantity"];
    }
}
