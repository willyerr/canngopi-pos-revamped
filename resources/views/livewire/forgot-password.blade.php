<div class="h-full flex justify-center items-center">
    <form class="w-[850px] h-[400px] flex shadow-lg rounded-xl" wire:submit="sendResetLink">
        <div class="w-1/2 h-full flex flex-col justify-center items-center gap-3 bg-primary rounded-l-xl">
            <img src="{{ asset('images/logo.png') }}" alt="canngopi_logo" class="h-[200px] object-cover">
            <span class="text-white text-xl font-semibold">Can Ngopi - Point of Sale</span>
        </div>

        <div class="w-1/2 h-full bg-white rounded-r-xl flex flex-col justify-center gap-8 p-8">
            <span class="text-xl">Account Forgot Password</span>

            <div class="w-full flex flex-col gap-5">
                <x-input 
                    type="email" 
                    label="Email" 
                    icon="envelope"
                    placeholder="Input your email"
                    input-class="focus:outline-none"
                    wire:model="email"
                    required 
                />
            </div>

            <x-button type="submit" icon="arrow-right-end-on-rectangle" position="left" color="red">Send Reset Link</x-button>
        </div>
    </form>
</div>
