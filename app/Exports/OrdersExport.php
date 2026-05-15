<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class OrdersExport implements FromCollection, WithMapping, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
    }

    public function collection()
    {
        $columns = array_diff(
            Schema::getColumnListing('orders'),
            ['updated_at'] 
        );

        return Order::select($columns)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->cashier_name,
            $order->customer_name ?? '-', // TAMBAHAN: Nama Customer
            $order->payment_method,
            $order->discount_name ?? '-',
            $order->discount_value,
            $order->voucher_quantity ?? '-',
            $order->subtotal,
            $order->grand_total,
            Carbon::parse($order->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i \W\I\B')
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cashier',
            'Customer Name', // TAMBAHAN: Heading Customer Name
            'Payment Method',
            'Discount Name',
            'Discount Value',
            'Voucher Quantity',
            'Subtotal',
            'Grand Total',
            'Order Date'
        ];
    }
}