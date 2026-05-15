<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

use App\Mail\InvoiceMail;
use App\Models\Order;
use App\Models\OrderItem;

use Carbon\Carbon;

class OrderManagementService
{
    public $orderInfoFields = [
        'customer_name' => ['required', 'string'],
        'table_number' => ['required', 'numeric'],
        'order_type' => ['required', 'string'],
        'cashier_name' => ['required', 'string'],
        'customer_email' => ['nullable', 'email'],
        'payment_method' => [
            'nullable', // Mengizinkan kosong saat open bill
            'string',
            'in:Bank Transfer,Staff Meal,Marketting Voucher,Entertainment,QC,QRIS'
        ],
        'discount_name' => ['nullable', 'string'],
        'discount_value' => ['numeric'],
        'subtotal' => ['required', 'numeric'],
        'grand_total' => ['required', 'numeric'],
        'status' => ['required', 'string'],
    ];

    public function list(?string $startDate = null, ?string $endDate = null)
    {
        $orders = Order::with('items')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            })
            ->when($startDate && !$endDate, function ($query) use ($startDate) {
                return $query->whereDate('created_at', $startDate);
            })
            ->get();
    
        return $orders->map(function ($order) {
            return $order->only([
                'id', 'customer_name', 'cashier_name', 'customer_email',
                'payment_method', 'discount_name', 'discount_value', 'voucher_quantity',
                'subtotal', 'grand_total', 'status', 'order_type', 'created_at'
            ]) + ['items' => $order->items->toArray()];
        });
    }

    public function getOrderByRange(string $startDate, string $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();  
    
        return Order::with('items')
            ->whereBetween('created_at', [$start, $end])
            ->get();
    }

    public function searchOrderItemById(int $orderId)
    {
        return OrderItem::where('order_id', $orderId)->get();
    }

    // FUNGSI BARU: Mengambil Order Spesifik
    public function getOrderById(int $orderId)
    {
        $order = Order::with('items')->find($orderId);
        if (!$order) throw new \Exception("Order not found");
        return $order->toArray();
    }
    
    public function createOrder(array $orderInfo, array $orderItems)
    {
        $orderInfoValidator = Validator::make($orderInfo, $this->orderInfoFields);
        if($orderInfoValidator->fails()) 
            throw new ValidationException($orderInfoValidator);

        $orderItemsValidator = Validator::make($orderItems, [
            '*.name' => 'required|string',
            '*.price' => 'required|numeric',
            '*.category' => 'required|string',
            '*.quantity' => 'required|numeric',
            '*.total_price' => 'required|numeric',
            '*.notes' => 'nullable|string',
        ]);
        if($orderItemsValidator->fails()) 
            throw new ValidationException($orderItemsValidator);

        try 
        {
            return DB::transaction(function () use ($orderInfo, $orderItems) {
                $order = Order::create($orderInfo);

                $itemsToInsert = collect($orderItems)->map(function ($item) use ($order) {
                    return [
                        'order_id' => $order->id,
                        'name' => $item['name'],
                        'category' => $item['category'],
                        'quantity' => $item['quantity'],
                        'total_price' => $item['total_price'],
                        'notes' => $item['notes'] ?? null,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at
                    ];
                })->all();
                
                OrderItem::insert($itemsToInsert);
                $orderWithItems = $order->load('items');

                if($order->status === 'Done' && !empty($order->customer_email))
                    Mail::to($order->customer_email)->send(new InvoiceMail($orderWithItems));

                return $orderWithItems;
            });
        } 
        catch (\Exception $e) 
        {
            throw new \RuntimeException("Failed to create order: " . $e->getMessage());
        }
    }

    // FUNGSI BARU: Update Order yang sudah ada (Open Bill)
    public function updateOrder(int $orderId, array $orderInfo, array $orderItems)
    {
        $orderInfoValidator = Validator::make($orderInfo, $this->orderInfoFields);
        if($orderInfoValidator->fails()) 
            throw new ValidationException($orderInfoValidator);

        $orderItemsValidator = Validator::make($orderItems, [
            '*.name' => 'required|string',
            '*.price' => 'required|numeric',
            '*.category' => 'required|string',
            '*.quantity' => 'required|numeric',
            '*.total_price' => 'required|numeric',
            '*.notes' => 'nullable|string',
        ]);
        if($orderItemsValidator->fails()) 
            throw new ValidationException($orderItemsValidator);

        try 
        {
            return DB::transaction(function () use ($orderId, $orderInfo, $orderItems) {
                $order = Order::findOrFail($orderId);
                $order->update($orderInfo);

                // Hapus item lama, timpa dengan data keranjang yang baru
                OrderItem::where('order_id', $orderId)->delete();

                $itemsToInsert = collect($orderItems)->map(function ($item) use ($order) {
                    return [
                        'order_id' => $order->id,
                        'name' => $item['name'],
                        'category' => $item['category'],
                        'quantity' => $item['quantity'],
                        'total_price' => $item['total_price'],
                        'notes' => $item['notes'] ?? null,
                        'created_at' => $order->created_at, // Pertahankan waktu pesan pertama
                        'updated_at' => now()
                    ];
                })->all();
                
                OrderItem::insert($itemsToInsert);
                $orderWithItems = $order->load('items');

                if($order->status === 'Done' && !empty($order->customer_email))
                    Mail::to($order->customer_email)->send(new InvoiceMail($orderWithItems));

                return $orderWithItems;
            });
        } 
        catch (\Exception $e) 
        {
            throw new \RuntimeException("Failed to update order: " . $e->getMessage());
        }
    }

    public function finishOrder(int $orderId)
    {
        return Order::where('id', $orderId)->update(['status' => 'Done']);
    }
}