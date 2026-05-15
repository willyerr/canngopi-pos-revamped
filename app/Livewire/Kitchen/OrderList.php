<?php

namespace App\Livewire\Kitchen;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On; 
use Livewire\Component;

use TallStackUi\Traits\Interactions; 

use App\Services\OrderManagementService;
use App\Helpers\ToastHelper;

use Carbon\Carbon;

class OrderList extends Component
{
    use Interactions;

    private OrderManagementService $orderManagementService;
    private ToastHelper $toaster;

    protected $listeners = ['echo:orders,OrderCreated' => 'handleOrderCreated'];

    public array $orderData = [];

    public function mount()
    {
        $this->orderData = $this->loadOrderData()->toArray();
    }

    public function boot(OrderManagementService $orderManagementService)
    {
        $this->orderManagementService = $orderManagementService;
        $this->toaster = new ToastHelper($this->toast());
    }
    
    public function reloadOrder()
    {
        $this->orderData = $this->loadOrderData()->toArray();
        $this->toaster->success('Order reloaded');
    }

    public function loadOrderData()
    {
        try
        {
            $now = Carbon::today()->toDateString();
            $data = $this->orderManagementService->list($now);
            return $data->reverse()->values();
        }
        catch(\Exception $e)
        {
            $this->orderData = [];
            $this->handleError($e);
        }
    }

    private function handleError(\Exception $e)
    {
        return $this->toaster->error($e->getMessage());
    }

    #[Layout('components.layouts.kitchen')]
    public function render()
    {
        return view('livewire.kitchen.order-list');
    }
}
