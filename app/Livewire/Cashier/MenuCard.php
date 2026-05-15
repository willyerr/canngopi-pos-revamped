<?php

namespace App\Livewire\Cashier;

use Livewire\Component;

class MenuCard extends Component
{
    public array $menuData;

    public function itemClicked()
    {
        $itemData = [
            'id' => $this->menuData['id'],
            'name' => $this->menuData['name'],
            'price' => $this->menuData['price'],
            'category' => $this->menuData['category'],
            'image' => $this->menuData['image'],
        ];
        $this->dispatch('item-clicked', $itemData);
    }

    public function render()
    {
        return view('livewire.cashier.menu-card');
    }
}
