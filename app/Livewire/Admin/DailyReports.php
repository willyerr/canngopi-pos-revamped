<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use TallStackUi\Traits\Interactions;
use App\Services\StatisticService;
use App\Services\OrderManagementService;
use App\Helpers\ToastHelper;
use App\Helpers\UtilityHelper;
use App\Exports\OrdersExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class DailyReports extends Component
{
    use Interactions;

    private StatisticService $statisticService;
    private OrderManagementService $orderManagementService;
    private ToastHelper $toaster;

    public ?string $filterDateStart = null;
    public ?string $filterDateEnd = null;
    
    public ?string $activeStat = null;
    public bool $showFilterModal = false;
    public array $summaryItems = [];

    public array $orderDetailModal = [
        'title' => 'Order Item Detail',
        'tableHeaders' => [
            ['index' => 'name', 'label' => 'Item Name'],
            ['index' => 'quantity', 'label' => 'Qty'],
            ['index' => 'total_price', 'label' => 'Total Price'],
        ],
        'data' => [],
        'state' => false,
    ];

    public array $statistics = [
        'Total Transaction'   => ['icon' => 'shopping-bag', 'value' => 0],
        'Total Item Sold'      => ['icon' => 'cube', 'value' => 0],
        'Total Revenue'       => ['icon' => 'wallet', 'value' => 0],
        'Total Nett Sales'     => ['icon' => 'banknotes', 'value' => 0],
        'Total Discount'      => ['icon' => 'receipt-percent', 'value' => 0],
        'Total Item Staff Meal'    => ['icon' => 'users', 'value' => 0],
        'Total Item Entertainment' => ['icon' => 'ticket', 'value' => 0],
        'Total Item QC'            => ['icon' => 'shield-check', 'value' => 0],
    ];

    public array $headers = [
        ['index' => 'id', 'label' => 'Order Id'],
        ['index' => 'cashier_name', 'label' => 'Cashier'],
        ['index' => 'customer_name', 'label' => 'Customer Name'], // TAMBAHAN DISINI
        ['index' => 'subtotal', 'label' => 'Subtotal'],
        ['index' => 'discount_name', 'label' => 'Discount Name'],
        ['index' => 'discount_value', 'label' => 'Discount Value'],
        ['index' => 'voucher_quantity', 'label' => 'Voucher Qty'],
        ['index' => 'grand_total', 'label' => 'Grand Total'],
        ['index' => 'payment_method', 'label' => 'Payment Method'],
        ['index' => 'created_at', 'label' => 'Created At'],
        ['index' => 'action', 'label' => 'Action'],
    ];

    public string $currentLayout = 'components.layouts.admin';

    public function boot(StatisticService $statisticService, OrderManagementService $orderManagementService) {
        $this->statisticService = $statisticService;
        $this->orderManagementService = $orderManagementService;
        $this->toaster = new ToastHelper($this->toast());
    }

    public function mount() {
        $userRole = Auth::user()->role;
        $this->currentLayout = (strtolower($userRole) === 'accounting') 
                                ? 'components.layouts.accounting' 
                                : 'components.layouts.admin';

        $today = Carbon::today()->toDateString();
        $this->filterDateStart = Carbon::parse($today)->format('d F Y');
        $this->filterDateEnd = Carbon::parse($today)->format('d F Y');

        $this->refreshStatistics();
    }

    public function filterByStat(string $stat)
    {
        $this->activeStat = $stat;
        $this->showFilterModal = true;
    }

    public function updateDailyStatistic() {
        if (!$this->filterDateStart || !$this->filterDateEnd) {
            $this->toaster->error('Please choose start and end date');
            return;
        }
        $this->activeStat = null; 
        $this->showFilterModal = false;
        $this->refreshStatistics();
        $this->toaster->success('Statistics updated');
    }

    private function refreshStatistics() {
    try {
        $repairedData = $this->getRepairedRawData();

        $this->statistics['Total Transaction']['value'] = $repairedData->count();
        $totalActualItemSold = 0;
        foreach($repairedData as $order) {
            $items = collect($order['items'] ?? []);
            $totalActualItemSold += $items->sum('quantity');
        }
        $this->statistics['Total Item Sold']['value'] = $totalActualItemSold;

        $totalRevenue = 0;
        $totalDiscount = 0;
        $totalNett = 0;
        
        $itemCountStaffMeal = 0;
        $itemCountQC = 0;
        $itemCountEntertain = 0;

        foreach($repairedData as $order) {
            $method = $order['payment_method'] ?? '';
            $items = collect($order['items'] ?? []);
            $totalQtyInOrder = $items->sum('quantity');

            if (stripos($method, 'Staff Meal') !== false) {
                $itemCountStaffMeal += $totalQtyInOrder;
            } elseif (stripos($method, 'QC') !== false) {
                $itemCountQC += $totalQtyInOrder;
            } elseif (stripos($method, 'Entertainment') !== false) {
                $itemCountEntertain += $totalQtyInOrder;
            } else {
                $totalRevenue += $order['subtotal'];
                $totalDiscount += $order['discount_value'];
                $totalNett += $order['grand_total'];
            }
        }

        $this->statistics['Total Revenue']['value'] = UtilityHelper::formatCurrency($totalRevenue);
        $this->statistics['Total Nett Sales']['value'] = UtilityHelper::formatCurrency($totalNett);
        $this->statistics['Total Discount']['value'] = UtilityHelper::formatCurrency($totalDiscount);
        
        $this->statistics['Total Item Staff Meal']['value'] = $itemCountStaffMeal;
        $this->statistics['Total Item QC']['value'] = $itemCountQC;
        $this->statistics['Total Item Entertainment']['value'] = $itemCountEntertain;

    } catch (\Exception $e) { 
        $this->toaster->error('Stats Error: ' . $e->getMessage()); 
    }
}

    private function getRepairedRawData() {
        $start = $this->filterDateStart ? Carbon::parse($this->filterDateStart)->format('Y-m-d') : null;
        $end   = $this->filterDateEnd ? Carbon::parse($this->filterDateEnd)->format('Y-m-d') : null;
        
        $raw = $this->orderManagementService->list($start, $end);
        
        return collect($raw)->map(function($o) {
            $order = (array) $o;
            $items = collect($order['items'] ?? []);
            
            $method = $order['payment_method'] ?? '';
            $isOperational = (stripos($method, 'Staff Meal') !== false) || 
                             (stripos($method, 'QC') !== false) || 
                             (stripos($method, 'Entertainment') !== false);

            $subtotal = floatval($order['subtotal'] ?? 0);
            if ($subtotal <= 0 && $items->count() > 0) {
                $subtotal = $items->sum('total_price');
            }

            $discount = floatval($order['discount_value'] ?? 0);
            $voucherQty = intval($order['voucher_quantity'] ?? 0);
            if ($discount <= 0 && $voucherQty > 0) {
                $discount = $voucherQty * 50000;
            }

            $grandTotal = floatval($order['grand_total'] ?? 0);
            if ($grandTotal <= 0 && !$isOperational && $subtotal > 0) {
                $grandTotal = max(0, $subtotal - $discount);
            }

            return array_merge($order, [
                'subtotal' => $subtotal,
                'discount_value' => $discount,
                'grand_total' => $grandTotal,
                'voucher_quantity' => $voucherQty,
                'customer_name' => $order['customer_name'] ?? '-' // Pastikan customer_name masuk
            ]);
        });
    }

    public function loadOrderData() {
        $repaired = $this->getRepairedRawData();

        if ($this->showFilterModal && $this->activeStat) {
            switch ($this->activeStat) {
                case 'Total Discount':
                    $repaired = $repaired->filter(fn($o) => $o['discount_value'] > 0);
                    break;
                case 'Total Item Staff Meal':
                    $repaired = $repaired->filter(fn($o) => stripos($o['payment_method'], 'Staff Meal') !== false);
                    break;
                case 'Total Item QC':
                    $repaired = $repaired->filter(fn($o) => stripos($o['payment_method'], 'QC') !== false);
                    break;
                case 'Total Item Entertainment':
                    $repaired = $repaired->filter(fn($o) => stripos($o['payment_method'], 'Entertainment') !== false);
                    break;
                case 'Total Revenue':
                case 'Total Nett Sales':
                    $repaired = $repaired->filter(fn($o) => 
                        stripos($o['payment_method'], 'Staff Meal') === false &&
                        stripos($o['payment_method'], 'QC') === false &&
                        stripos($o['payment_method'], 'Entertainment') === false
                    );
                    break;
                default: break;
            }
            $this->calculateItemSummary($repaired);
        } else {
            $this->summaryItems = [];
        }

        return $repaired->values()->map(function ($o) {
            $method = $o['payment_method'] ?? '';
            $isOperational = (stripos($method, 'Staff Meal') !== false) || 
                             (stripos($method, 'QC') !== false) || 
                             (stripos($method, 'Entertainment') !== false);

            $formatted = $o;
            $formatted['customer_name'] = $o['customer_name'] ?? '-'; // Map ke row tabel
            $formatted['subtotal'] = $isOperational ? '-' : UtilityHelper::formatCurrency($o['subtotal']);
            $formatted['grand_total'] = $isOperational ? '-' : UtilityHelper::formatCurrency($o['grand_total']);
            $formatted['discount_value'] = ($o['discount_value'] > 0) ? UtilityHelper::formatCurrency($o['discount_value']) : '-';
            $formatted['voucher_quantity'] = ($o['voucher_quantity'] > 0) ? $o['voucher_quantity'] : '-';
            $formatted['discount_name'] = $o['discount_name'] ?? ($o['voucher_quantity'] > 0 ? "Voucher Marketing" : "-");
            $formatted['action'] = $o['id'] ?? null;
            return $formatted;
        });
    }

    private function calculateItemSummary($filteredOrders) {
        $summary = [];
        foreach ($filteredOrders as $order) {
            $items = $this->orderManagementService->searchOrderItemById($order['id']);
            if ($items) {
                foreach ($items as $item) {
                    $name = $item->name ?? 'Unknown';
                    $qty = $item->quantity ?? 0;
                    $summary[$name] = ($summary[$name] ?? 0) + $qty;
                }
            }
        }
        arsort($summary);
        $this->summaryItems = $summary;
    }

    public function openOrderDetail(int $orderId) {
        try {
            $items = $this->orderManagementService->searchOrderItemById($orderId);
            if (!$items) return $this->toaster->error('Order items not found');
            foreach ($items as $item) { $item->total_price = UtilityHelper::formatCurrency($item->total_price); }
            $this->orderDetailModal['data'] = $items->toArray();
            $this->orderDetailModal['state'] = true;
        } catch (\Exception $e) { $this->toaster->error($e->getMessage()); }
    }

    public function exportToExcel() {
        if (!$this->filterDateStart || !$this->filterDateEnd) {
            $this->toaster->error('Please select start and end date');
            return;
        }
        $fileName = sprintf('orders-%s-%s.xlsx', $this->filterDateStart, $this->filterDateEnd);
        return Excel::download(new OrdersExport($this->filterDateStart, $this->filterDateEnd), $fileName);
    }

    public function render() {
        return view('livewire.admin.daily-reports', [
            'rows' => $this->loadOrderData(),
            'dailySummary' => $this->loadDailySummarySafe()
        ])->layout($this->currentLayout);
    }

    private function loadDailySummarySafe() {
        try {
            $start = $this->filterDateStart ? Carbon::parse($this->filterDateStart)->format('Y-m-d') : null;
            $end   = $this->filterDateEnd ? Carbon::parse($this->filterDateEnd)->format('Y-m-d') : null;
            $raw = $this->orderManagementService->list($start, $end);
            $orders = collect($raw)->map(fn($item) => (array)$item);
            if ($orders->isEmpty()) return collect();
            return $orders->groupBy(fn ($o) => Carbon::parse($o['created_at'])->toDateString())
                ->map(fn ($group, $date) => [
                    'date' => $date,
                    'total_transactions' => $group->count(),
                    'total_item_sold' => $group->sum(fn ($i) => intval($i['item_count'] ?? 0)),
                    'total_revenue' => UtilityHelper::formatCurrency($group->sum(fn ($i) => floatval($i['grand_total'] ?? 0))),
                ])->values();
        } catch (\Exception $e) { return collect(); }
    }
}