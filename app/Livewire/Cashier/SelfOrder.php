<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

use TallStackUi\Traits\Interactions; 

use App\Services\DiscountService;
use App\Services\SelfOrderService;
use App\Services\OrderManagementService;

use App\Helpers\ToastHelper;
use App\Helpers\UtilityHelper;

class SelfOrder extends Component
{
    use Interactions;

    private DiscountService $discountService;
    private SelfOrderService $selfOrderService;
    private OrderManagementService $orderManagementService;
    private ToastHelper $toaster;

    public array $draftOrder = [];
    public array $discounts = [];

    public ?int $selectedDraftOrderId = null;
    public array $selectedOrderInfo = [
        'customer_name' => null,
        'table_number' => null,
        'customer_email' => null,
        'order_type' => null
    ];
    public array $selectedOrderItems = [];
    public array $selectedOrderSummary = [
        'subtotal' => 0,
        'discount' => 0,
        'grand_total' => 0
    ];

    public array $discountOptions = [];
    public ?int $selectedDiscountId = null;

    public bool $confirmOrderModalState = false;

    public function mount()
    {
        $this->draftOrder = $this->loadOrderData()->toArray();
        $this->discounts = $this->loadDiscountData();
    }

    public function boot(DiscountService $discountService, SelfOrderService $selfOrderService, OrderManagementService $orderManagementService)
    {
        $this->discountService = $discountService;
        $this->selfOrderService = $selfOrderService;
        $this->orderManagementService = $orderManagementService;
        $this->toaster = new ToastHelper($this->toast());
    }

    public function updatedSelectedDiscountId($value)
    {
        $this->calculateOrderSummary();
        $this->toaster->success(is_null($this->selectedDiscountId) ? 'Discount successfully removed' : 'Discount successfully applied');
    }

    private function calculateOrderSummary()
    {
        $this->selectedOrderSummary['subtotal'] = collect($this->selectedOrderItems)->sum('total_price');
        if(!is_null($this->selectedDiscountId))
        {
            $selectedDiscount = collect($this->discounts)->firstWhere('id', $this->selectedDiscountId);
            if($selectedDiscount)
            {
                $this->selectedOrderSummary['discount'] = $this->selectedOrderSummary['subtotal'] * ($selectedDiscount['offer_value'] / 100);
                $this->selectedOrderSummary['grand_total'] = $this->selectedOrderSummary['subtotal'] - $this->selectedOrderSummary['discount'];
            }
        }
        else
        {
            $this->selectedOrderSummary['discount'] = 0;
            $this->selectedOrderSummary['grand_total'] = $this->selectedOrderSummary['subtotal'];
        }
    }

    #[On('show-confirm-order-modal')]
    public function showConfirmOrderModal($orderData)
    {
        $this->resetRelatedOrderVariable();
        $this->selectedDraftOrderId = $orderData['id'];

        $this->selectedOrderInfo = [
            'customer_name' => $orderData['customer_name'],
            'table_number' => $orderData['table_number'],
            'customer_email' => $orderData['customer_email'],
            'order_type' => $orderData['order_type'],
        ];

        foreach($orderData['items'] as $item)
        {
            $this->selectedOrderItems[] = [
                'id' => $item['menu']['id'],
                'name' => $item['menu']['name'],
                'price' => $item['menu']['price'],
                'quantity' => $item['quantity'],
                'total_price' => $item['quantity'] * $item['menu']['price'],
                'category' => $item['menu']['category'],
                'notes' => $item['notes']
            ];
        }

        $this->calculateOrderSummary();
        $this->confirmOrderModalState = true;
    }

    #[On('show-cancel-confirm-dialog')]
    public function showCancelConfirmDialog($orderId)
    {
        $this->dialog()
            ->question('You are about to cancel this order', 'Are you sure?')
            ->confirm('Confirm', 'cancelOrder', $orderId)
            ->send();
    }

    private function resetRelatedOrderVariable()
    {
        $this->reset('selectedDraftOrderId');
        $this->reset('selectedOrderInfo');
        $this->reset('selectedOrderItems');
        $this->reset('selectedOrderSummary');
        $this->reset('selectedDiscountId');
    }

    public function showTransactionConfirmDialog()
    {
        $this->dialog()
            ->question('Payment Confirmation (please click confirm once customer has paid)', 'Are you sure?')
            ->confirm('Confirm', 'proceedOrder')
            ->send(); 
    }

    public function proceedOrder()
    {
        try
        {
            $discountData = [
                'discount_name' => null,
                'discount_value' => 0
            ];

            if(!is_null($this->selectedDiscountId))
            {
                $selectedDiscount = collect($this->discounts)->firstWhere('id', $this->selectedDiscountId);
                $discountData = [
                    'discount_name' => $selectedDiscount['name'] . ' (' . $selectedDiscount['offer_value'] . '%)',
                    'discount_value' => $this->selectedOrderSummary['discount']
                ];
            }

            $orderData = [
                ...$this->selectedOrderInfo,
                ...$discountData,
                'cashier_name' => auth()->user()->fullname,
                'payment_method' => 'Bank Transfer',
                'subtotal' => $this->selectedOrderSummary['subtotal'],
                'grand_total' => $this->selectedOrderSummary['grand_total'],
                'status' => 'Pending'
            ];

            $this->selfOrderService->convertDraftToPaidOrder($this->selectedDraftOrderId, $orderData, $this->selectedOrderItems);
            $this->resetRelatedOrderVariable();
            
            $this->draftOrder = $this->loadOrderData()->toArray();
            $this->toaster->success('Order successfully created');
            $this->confirmOrderModalState = false;
        }
        catch(\Exception $e)
        {
            $this->handleError($e);
        }
    }

    public function cancelOrder(int $orderId)
    {
        try
        {
            $removed = $this->selfOrderService->remove($orderId);
            if(!$removed)
                return $this->toaster->error('Failed to cancel order');

            $this->draftOrder = $this->loadOrderData()->toArray();
            $this->toaster->success('Order successfully cancelled');
        }
        catch(\Exception $e)
        {
            $this->handleError($e);
        }
    }

    public function loadOrderData()
    {
        try
        {
            $data = $this->selfOrderService->list();
            return $data;
        }
        catch(\Exception $e)
        {
            $this->orderData = [];
            $this->handleError($e);
        }
    }

    private function loadDiscountData()
    {
        try
        {
            $discountList = $this->discountService->list()->get()->toArray();
            $this->setupDiscountOptions($discountList);

            return $discountList;
        }
        catch(\Exception $e)
        {
            $this->handleError($e);
            return [];   
        }
    }

    private function setupDiscountOptions(array $discounts)
    {
        foreach($discounts as $discount)
        {
            $discountData = [
                'label' => $discount['name'] . ' (' . $discount['offer_value'] . '%)',
                'value' => $discount['id']
            ];
            $this->discountOptions[] = $discountData;
        }
    }

    private function handleError(\Exception $e)
    {
        return $this->toaster->error($e->getMessage());
    }

    #[Layout('components.layouts.cashier')]
    public function render()
    {
        return view('livewire.cashier.self-order');
    }
}
