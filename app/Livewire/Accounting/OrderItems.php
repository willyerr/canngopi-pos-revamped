<?php

namespace App\Livewire\Accounting;

use Livewire\Attributes\Layout;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

use App\Services\OrderManagementService;
use App\Helpers\UtilityHelper;
use App\Helpers\ToastHelper;
use App\Exports\OrderItemsExport;

use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class OrderItems extends Component
{
    use Interactions;

    private OrderManagementService $orderManagementService;
    private ToastHelper $toaster;

    public ?string $filterDateStart = null;
    public ?string $filterDateEnd = null;
    public string $filterCategory = 'All';

    public float $totalRevenue = 0;
    public float $totalNonRevenue = 0;

    public array $headers = [
        ['index' => 'order_id', 'label' => 'Order Id'],
        ['index' => 'name', 'label' => 'Item Name'],
        ['index' => 'category', 'label' => 'Category'],
        ['index' => 'quantity', 'label' => 'Qty'],
        ['index' => 'total_price', 'label' => 'Total Price'],
        ['index' => 'payment_method', 'label' => 'Payment Method'],
        ['index' => 'revenue', 'label' => 'Revenue'],
        ['index' => 'created_at', 'label' => 'Created At'],
    ];

    public $rows = [];

    public function boot(OrderManagementService $orderManagementService)
    {
        $this->orderManagementService = $orderManagementService;
        $this->toaster = new ToastHelper($this->toast());
    }

    public function mount()
    {
        $today = Carbon::today()->toDateString();
        $this->filterDateStart = $today;
        $this->filterDateEnd = $today;

        $this->rows = $this->loadOrderItemData();
    }

    public function updateOrderItemData()
    {
        if (!$this->filterDateStart || !$this->filterDateEnd) {
            return $this->toaster->error('Please select start and end date');
        }

        $this->rows = $this->loadOrderItemData();
        $this->toaster->success('Order item successfully updated');
    }

    public function exportToExcel()
    {
        $filename = 'order-items-' . $this->filterDateStart . '-' . $this->filterDateEnd . '.xlsx';

        return Excel::download(
            new OrderItemsExport(
                $this->filterCategory,
                $this->filterDateStart,
                $this->filterDateEnd
            ),
            $filename
        );
    }

    private function isRevenue(string $paymentMethod): bool
    {
        return !in_array($paymentMethod, [
            'Staff Meal',
            'Marketting Voucher',
            'Entertainment',
            'QC',
        ]);
    }

    public function loadOrderItemData()
    {
        $this->totalRevenue = 0;
        $this->totalNonRevenue = 0;

        if (!$this->filterDateStart || !$this->filterDateEnd) {
            return collect();
        }

        $orders = $this->orderManagementService
            ->getOrderByRange($this->filterDateStart, $this->filterDateEnd);

        $rows = collect();

        foreach ($orders as $order) {
            foreach ($order->items as $item) {

                if ($this->filterCategory !== 'All' && $item->category !== $this->filterCategory) {
                    continue;
                }

                $revenue = $this->isRevenue($order->payment_method)
                    ? $item->total_price
                    : 0;

                $this->totalRevenue += $revenue;
                $this->totalNonRevenue += ($item->total_price - $revenue);

                $rows->push([
                    'order_id' => $item->order_id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                    'payment_method' => $order->payment_method,
                    'revenue' => $revenue,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return $rows;
    }

    public function openOrderDetail($orderId)
    {
        $this->toaster->info("Open order detail: {$orderId}");
    }

    #[Layout('components.layouts.accounting')]
    public function render()
    {
        return view('livewire.accounting.order-items', [
            'rows' => $this->rows,
            'totalRevenue' => UtilityHelper::formatCurrency($this->totalRevenue),
            'totalNonRevenue' => UtilityHelper::formatCurrency($this->totalNonRevenue),
        ]);
    }
}
