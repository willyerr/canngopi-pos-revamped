<?php

namespace App\Livewire\Cashier;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class OrderItemCard extends Component
{
    #[Reactive]
    public array $itemData;

    public function editItem()
    {
        $this->dispatch('edit-item-clicked', $this->itemData['id']);
    }

    public function removeItem()
    {
        $this->dispatch('remove-item-clicked', $this->itemData['id']);
    }

    public function render()
    {
        return view('livewire.cashier.order-item-card');
    }
}