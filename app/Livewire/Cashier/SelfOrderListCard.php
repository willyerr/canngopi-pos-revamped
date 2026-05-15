<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use Livewire\Attributes\Reactive;


class SelfOrderListCard extends Component
{
    #[Reactive]
    public $orderData;

    public function showConfirmOrderModal()
    {
        $this->dispatch('show-confirm-order-modal', $this->orderData);
    }

    public function showCancelConfirmDialog()
    {
        $this->dispatch('show-cancel-confirm-dialog', $this->orderData['id']);
    }

    public function render()
    {
        return view('livewire.cashier.self-order-list-card');
    }
}
