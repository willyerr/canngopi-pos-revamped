<?php

namespace App\Exports;

use App\Models\OrderItem;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class OrderItemsExport implements FromCollection, WithMapping, WithHeadings
{
    protected $category;
    protected $startDate;
    protected $endDate;

    public function __construct($category, $startDate, $endDate)
    {
        $this->category = $category;
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $columns = array_diff(
            Schema::getColumnListing('order_items'),
            ['id', 'notes', 'updated_at'] 
        );

        $query = OrderItem::select($columns)
        ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        if($this->category !== 'All')
            $query->where('category', $this->category);

        return $query->get();
    }

    public function map($order): array
    {
        return [
            $order->order_id,
            $order->name,
            $order->category,
            $order->quantity,
            $order->total_price,
            Carbon::parse($order->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i \W\I\B')
        ];
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Name',
            'Category',
            'Qty',
            'Total Price',
            'Order Date'
        ];
    }
}
