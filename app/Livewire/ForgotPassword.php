<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;

use TallStackUi\Traits\Interactions; 

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

use App\Helpers\ToastHelper;

class ForgotPassword extends Component
{
    use Interactions;

    private ToastHelper $toaster;

    public string $email;
    protected $rules = [
        'email' => 'required|email|exists:users,email',
    ];

    public function boot()
    {
        $this->toaster = new ToastHelper($this->toast());
    }

    public function sendResetLink()
    {
        if(RateLimiter::tooManyAttempts('reset-password:'.$this->email, 3)) 
        {
            $seconds = RateLimiter::availableIn('reset-password:'.$this->email);
            $this->toaster->error("Too many attempts. Please try again in {$seconds} seconds.");
            return;
        }

        $this->validate();
        $status = Password::sendResetLink(['email' => $this->email]);

        if($status == Password::RESET_LINK_SENT) {
            RateLimiter::hit('reset-password:'.$this->email, 300);
            $this->toaster->success('A reset link has been sent to your email address.');
        } else {
            $this->toaster->error('Unable to send reset link.');
        }
    }

    #[Title('Can Ngopi | Point of Sale - Forgot Password')]
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.forgot-password');
    }
}
