<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

use TallStackUi\Traits\Interactions; 

use App\Helpers\ToastHelper;
use App\Helpers\UtilityHelper;
use App\Services\MenuService;

use Illuminate\Database\Eloquent\Builder;

class MenuManagement extends Component
{
    use WithPagination, Interactions, WithFileUploads;

    public array $headers = [
        ['index' => 'id', 'label' => '#'],
        ['index' => 'name', 'label' => 'menu name'],
        ['index' => 'price', 'label' => 'menu price'],
        ['index' => 'category', 'label' => 'category'],
        ['index' => 'action', 'label' => 'Action'], 
    ];

    public array $modal = [
        'title' => '',
        'fields' => [
            'name' => [
                'icon' => 'book-open',
                'type' => 'text'
            ],
            'price' => [
                'icon' => 'currency-dollar',
                'type' => 'number',
                'min' => 1000,
                'hint' => 'Note: Minimum price is Rp 1.000'
            ],
            'category' => [
                'icon' => 'chevron-up-down',
                'type' => 'select',
                'options' => ['Signature', 'Breakfast', 'Dessert', 'Snack', 'Pizza', 'Burger & Sandwich', 'Soup', 'Pasta', 'Coffee', 'Non Coffee', 'Mocktail', 'Donbury', 'Others'],
            ],
            'image' => [
                'icon' => 'photo',
                'type' => 'file'
            ]
        ],
        'type' => 'create',
        'state' => false
    ];

    public array $formData = [
        'name' => '',
        'price' => null,
        'category' => null,
        'image' => null
    ];
    public ?string $imagePreview = null;
    public ?int $editedMenuId = null;

    public int $quantity = 10; 
    public ?string $search = null; 

    public function boot(MenuService $menuService)
    {
        $this->menuService = $menuService;
        $this->toaster = new ToastHelper($this->toast());
    }

    public function resetFormData(): void 
    {
        $this->formData = [
            'name' => '',
            'price' => null,
            'category' => null,
            'image' => null
        ];
        $this->imagePreview = null;
    }

    public function setFormData($data): void
    {
        $this->formData = [
            'name' => $data['name'],
            'price' => $data['price'],
            'category' => $data['category'],
            'image' => null
        ];

        $this->imagePreview = $data['image'] ? asset('storage/' . $data['image']) : null;
    }

    public function toggleModal(string $type, ?int $menuId = null): void
    {
        switch($type)
        {
            case 'create':
                $this->modal['title'] = 'Add New Menu';
                $this->resetFormData();
                break;
            case 'edit':
                $this->modal['title'] = 'Edit Menu';
                $this->editedMenuId = $menuId;

                $menu = $this->menuService->show($menuId);
                if(!$menu)
                {
                    $this->toaster->error('Unable to update menu, menu not found');
                    return;
                }

                $this->setFormData($menu);
                break;
        }

        $this->modal['type'] = $type;
        $this->modal['state'] = true;
    }

    public function create(): void 
    {
        try
        {
            $this->menuService->store($this->formData);
            $this->toaster->success('A new menu registered');
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
            if(!$this->editedMenuId) {
                $this->toaster->error('No menu selected for editing');
                return;
            }

            $updated = $this->menuService->edit($this->editedMenuId, $this->formData);
            if(!$updated)
            {
                $this->toast()->error('Failed to update menu')->send();
                return;
            }

            $this->toaster->success('Menu successfully updated');
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
            ->question('You are about to delete menu data', 'Are you sure?')
            ->confirm('Confirm', 'delete', $id)
            ->send();
    }

    public function delete(int $id): void 
    {
        try
        {
            $deleted = $this->menuService->delete($id);
            if(!$deleted)
            {
                $this->toaster->error('Unable to delete menu, menu not found');
                return;
            }

            $this->toaster->success('Menu deleted');
        }
        catch(\Exception $e)
        {
            $this->toaster->error($e->getMessage());
        }
    }

    public function loadMenuData()
    {
        return $this->menuService->list()
            ->when($this->search, function (Builder $query) {
                return $query->where('name', 'like', "%{$this->search}%");
            })
            ->paginate($this->quantity)
            ->withQueryString()
            ->through(function ($item) {
                $item->price = UtilityHelper::formatCurrency($item->price);

                return $item;
            });
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        return view('livewire.admin.menu-management', [
            'rows' => $this->loadMenuData()
        ]);
    }
}
