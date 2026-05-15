<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;

use TallStackUi\Traits\Interactions; 

use App\Services\AuthService;
use App\Helpers\ToastHelper;

class Login extends Component
{
    use Interactions; 

    public $email;
    public $password;

    public function boot(AuthService $authService)
    {
        $this->authService = $authService;
        $this->toaster = new ToastHelper($this->toast());
    }

    public function authenticate()
    {
        $credentials = [
            'email' => $this->email,
            'password' => $this->password
        ];

        $authenticated = $this->authService->login($credentials);
        if(!$authenticated)
        {
            $this->toaster->error('Invalid credentials');
            return back()->onlyInput('email');
        }
        
        $route = null;
        switch($this->authService->getUserRole())
        {
            case 'Admin':
                $route = 'admin.dashboard';
                break;
            case 'Cashier':
                $route = 'cashier.create-order';
                break;
            case 'Accounting':
                $route = 'accounting.dashboard';
                break;
            case 'Kitchen':
                $route = 'kitchen.order-list';
                break;
            case 'IT':
                $route = 'it.user-management';
                break;
            default:
                $route = 'login';
                $this->toaster->error('Unregistered role', true);
                break;
        }
        return $this->redirect(route($route));
    }

    #[Title('Can Ngopi | Point of Sale - Login')]
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.login');
    }
}
