<?php

namespace App\Livewire\Kitchen;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class OrderListCard extends Component
{
    #[Reactive]
    public array $orderData;

    public function render()
    {
        return view('livewire.kitchen.order-list-card');
    }
}
