<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class StatisticService
{
    public function getData(Carbon $startDate, Carbon $endDate)
    {
        $orders = DB::table('orders')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);

        $totalTransactions = $orders->count();

        $totalRevenue = (clone $orders)->sum('subtotal');
        $nettSales = (clone $orders)->sum('grand_total');
        $totalDiscount = (clone $orders)->sum('discount_value');
        $totalStaffMeal = DB::table('order_items')
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->whereBetween('orders.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                            ->where('orders.payment_method', 'Staff Meal')
                            ->sum('order_items.quantity');
    

        $itemSold = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('order_items.quantity');

        return [
            'total_transactions' => $totalTransactions,
            'total_item_sold'    => $itemSold,
            'total_revenue'      => $totalRevenue,
            'nett_sales'         => $nettSales,
            'total_discount'     => $totalDiscount,
            'total_staff_meal'   => $totalStaffMeal
        ];
    }
}
