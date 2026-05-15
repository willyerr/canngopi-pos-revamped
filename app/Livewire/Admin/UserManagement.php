<?php

namespace App\Livewire\Admin;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

use TallStackUi\Traits\Interactions; 

use App\Services\UserService;
use App\Helpers\ToastHelper;

class UserManagement extends Component
{
    use WithPagination, Interactions;

    protected UserService $userService;
    public array $headers = [
        ['index' => 'id', 'label' => '#'],
        ['index' => 'fullname', 'label' => 'full name'],
        ['index' => 'email', 'label' => 'email'],
        ['index' => 'role', 'label' => 'role'],
        ['index' => 'action', 'label' => 'Action'], 
    ];
    public array $modal = [
        'title' => '',
        'fields' => [
            'fullname' => [
                'icon' => 'user',
                'type' => 'text'
            ],
            'email' => [
                'icon' => 'envelope',
                'type' => 'email'
            ],
            'password' => [
                'icon' => 'key',
                'type' => 'password'
            ]
        ],
        'type' => 'Add',
        'state' => false
    ];
    public array $formData = [
        'fullname' => '',
        'email' => '',
        'password' => '',
        'role' => 'Admin'
    ];
    public ?int $editedUserId = null;

    public int $quantity = 10; 
    public ?string $search = null; 

    private $toaster = null;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
        $this->toaster = new ToastHelper($this->toast());
    }

    private function resetFormData()
    {
        $this->formData = [
            'fullname' => '',
            'email' => '',
            'password' => '',
            'role' => 'Admin'
        ];
    }

    public function toggleModal(string $type, ?int $userId = null): void
    {
        switch($type)
        {
            case 'create':
                $this->modal['title'] = 'Add New User';
                $this->resetFormData();
                break;
            case 'edit':
                $this->modal['title'] = 'Edit User';
                $this->editedUserId = $userId;

                $user = $this->userService->show($userId);
                if(!$user)
                {
                    $this->toaster->error('Unable to update user, user not found');
                    return;
                }

                $this->formData = [
                    'fullname' => $user->fullname,
                    'email' => $user->email,
                    'role' => $user->role
                ];
                break;
        }
        $this->modal['type'] = $type;
        $this->modal['state'] = true;
    }

    public function create(): void
    {
        try
        {            
            $this->userService->store($this->formData);
            $this->toaster->success('A new user registered');
            
            $this->resetFormData();
            $this->modal['state'] = false;

        }
        catch(\Exception $e)
        {
            $errMessage = $e->getMessage();
            if($e instanceof \Illuminate\Database\UniqueConstraintViolationException)
                $errMessage = 'Unable to create user, email already exists';
            
            $this->toaster->error($errMessage);
        }
    }

    public function edit(): void
    {
        try
        {
            $updated = $this->userService->edit($this->editedUserId, $this->formData);
            if(!$updated)
            {
                $this->toaster->error('Unable to update user, user not found');
                return;
            }

            $this->toaster->success('User successfully updated');
            $this->resetFormData();
            $this->modal['state'] = false;
        }
        catch(\Exception $e)
        {
            $errMessage = $e->getMessage();
            if($e instanceof \Illuminate\Database\UniqueConstraintViolationException)
                $errMessage = 'Unable to update user, email already exists';
            
            $this->toaster->error($errMessage);
        }
    }

    public function deleteConfirmation(int $id): void
    {
        $this->dialog()
            ->question('You are about to delete user data', 'Are you sure?')
            ->confirm('Confirm', 'delete', $id)
            ->send();
    }

    public function delete(int $id)
    {
        $userExists = $this->userService->delete($id);
        if(!$userExists)
        {
            $this->toaster->error('Unable to delete user, user not found');
            return;
        }

        $this->toaster->success('User deleted');
    }

    private function loadUserData()
    {
        return $this->userService->list()
            ->when($this->search, function (Builder $query) {
                return $query->where('fullname', 'like', "%{$this->search}%");
            })
            ->paginate($this->quantity)
            ->withQueryString();
    }

    #[Layout('components.layouts.it')]
    public function render()
    {
        return view('livewire.admin.user-management', [
            'rows' => $this->loadUserData()
        ]);
    }
}  
