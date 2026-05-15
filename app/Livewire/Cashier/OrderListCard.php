<?php

namespace App\Livewire\Cashier;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class OrderListCard extends Component
{
    #[Reactive]
    public array $orderData;

    public function finishOrder()
    {
        $this->dispatch('order-finished', $this->orderData['id']);
    }

    public function render()
    {
        return view('livewire.cashier.order-list-card');
    }
}
