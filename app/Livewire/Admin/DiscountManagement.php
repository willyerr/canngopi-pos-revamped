<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

use TallStackUi\Traits\Interactions; 

use App\Helpers\ToastHelper;
use App\Services\DiscountService;

use Illuminate\Database\Eloquent\Builder;

class DiscountManagement extends Component
{
    use WithPagination, Interactions;

    public array $headers = [
        ['index' => 'id', 'label' => '#'],
        ['index' => 'name', 'label' => 'name'],
        ['index' => 'offer_value', 'label' => 'offer value'],
        ['index' => 'action', 'label' => 'action']
    ];

    public array $modal = [
        'title' => '',
        'fields' => [
            'name' => [
                'icon' => 'tag',
                'type' => 'text'
            ],
            'description' => [
                'icon' => 'chat-bubble-bottom-center-text',
                'type' => 'text',
                'nullable' => true
            ],
            'terms_and_condition' => [
                'icon' => 'document-text',
                'type' => 'textarea',
                'nullable' => true
            ],
            // TAMBAHAN: Ubah field offer value menjadi custom, dan tambahkan type serta minimum purchase
            'type' => [
                'type' => 'custom' 
            ],
            'offer_value' => [
                'type' => 'custom'
            ],
            'minimum_purchase' => [
                'type' => 'custom'
            ]
        ],
        'type' => 'create',
        'state' => false
    ];

    public array $formData = [
        'name' => '',
        'description' => null,
        'terms_and_condition' => null,
        'type' => 'percentage', // TAMBAHAN: Default tipe persentase
        'offer_value' => 1,
        'minimum_purchase' => 0 // TAMBAHAN: Default 0
    ];
    public ?int $editedDiscountId = null;

    public int $quantity = 10; 
    public ?string $search = null; 

    public function boot(DiscountService $discountService)
    {
        $this->discountService = $discountService;
        $this->toaster = new ToastHelper($this->toast());
    }

    public function resetFormData(): void
    {
        $this->formData = [
            'name' => '',
            'description' => null,
            'terms_and_condition' => null,
            'type' => 'percentage', // TAMBAHAN
            'offer_value' => 1,
            'minimum_purchase' => 0 // TAMBAHAN
        ];
    }

    public function setFormData($data): void
    {
        $this->formData = [
            'name' => $data['name'],
            'description' => $data['description'],
            'terms_and_condition' => $data['terms_and_condition'],
            'type' => $data['type'] ?? 'percentage', // TAMBAHAN: fallback jika data lama null
            'offer_value' => $data['offer_value'],
            'minimum_purchase' => $data['minimum_purchase'] ?? 0 // TAMBAHAN
        ]; 
    }

    public function toggleModal(string $type, ?int $discountId = null): void 
    {
        switch($type)
        {
            case 'create':
            {
                $this->modal['title'] = 'Add New Discount';
                $this->resetFormData();
                break;
            }
            case 'edit':
            {
                $this->modal['title'] = 'Edit Discount';
                $this->editedDiscountId = $discountId;

                $discount = $this->discountService->show($discountId);
                if(!$discount)
                {
                    $this->toaster->error('Unable to update discount, discount not found');
                    return;
                }

                $this->setFormData($discount);
                break;
            }
        }

        $this->modal['type'] = $type;
        $this->modal['state'] = true;
    }

    public function create(): void 
    {
        try
        {
            $this->discountService->store($this->formData);
            $this->toaster->success('A new discount registered');

            $this->modal['state'] = false;
        }
        catch(\Exception $e)
        {
            $this->toaster->error($e->getMessage());
        }
    }

    public function edit(): void
    {
        try
        {
            if (!$this->editedDiscountId) {
                $this->toaster->error('No discount selected for editing');
                return;
            }

            $updated = $this->discountService->edit($this->editedDiscountId, $this->formData);
            if(!$updated)
            {
                $this->toast()->error('Failed to update discount')->send();
                return;
            }

            $this->toaster->success('Discount successfully updated');
            $this->resetFormData();
            $this->modal['state'] = false;

        }
        catch(\Exception $e)
        {
            $this->toaster->error($e->getMessage());
        }
    }

    public function deleteConfirmation(int $id): void
    {
        $this->dialog()
            ->question('You are about to delete discount data', 'Are you sure?')
            ->confirm('Confirm', 'delete', $id)
            ->send();
    }

    public function delete(int $id): void
    {
        try
        {
            $discountExists = $this->discountService->delete($id);
            if(!$discountExists)
            {
                $this->toaster->error('Unable to delete discount, discount not found');
                return;
            }

            $this->toaster->success('Discount successfully deleted');
        }
        catch(\Exception $e)
        {
            $this->toaster->error($e->getMessage());        
        }
    }

    public function loadDiscountData()
    {
        return $this->discountService->list()
            ->when($this->search, function (Builder $query) {
                return $query->where('name', 'like', "%{$this->search}%");
            })
            ->paginate($this->quantity)
            ->withQueryString()
            ->through(function ($item) {
                
                // TAMBAHAN: Memformat tampilan Offer Value di dalam tabel berdasarkan tipenya
                if (($item->type ?? 'percentage') === 'percentage') {
                    $item->offer_value = $item->offer_value . '%';
                } else {
                    $formattedValue = 'Rp ' . number_format($item->offer_value, 0, ',', '.');
                    if ($item->minimum_purchase > 0) {
                        $formattedValue .= ' (Min: Rp ' . number_format($item->minimum_purchase, 0, ',', '.') . ')';
                    }
                    $item->offer_value = $formattedValue;
                }

                return $item;
            });
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        return view('livewire.admin.discount-management', [
            'rows' => $this->loadDiscountData()
        ]);
    }
}