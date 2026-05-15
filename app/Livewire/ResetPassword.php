<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;

use TallStackUi\Traits\Interactions; 

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Helpers\ToastHelper;

class ResetPassword extends Component
{
    use Interactions;

    public string $email;
    public string $token;
    public string $password;
    public string $password_confirmation;

    public function mount($token)
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function boot()
    {
        $this->toaster = new ToastHelper($this->toast());
    }

    public function resetPassword()
    {
        $this->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if($status === Password::PASSWORD_RESET) 
        {
            $this->toaster->success('Your password has been reset.', true);
            return redirect()->route('login');
        } 
        else $this->toaster->error('Unable to reset password');
    }

    #[Title('Can Ngopi | Point of Sale - Reset Password')]
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.reset-password');
    }
}
