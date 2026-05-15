<div class="h-full flex justify-center items-center">
    @php
        $formFields = [
            [
                'type' => 'email',
                'icon' => 'envelope'
            ],
            [
                'type' => 'password',
                'icon' => 'key'
            ]
        ];
    @endphp
    <x-toast />
    <form class="w-[850px] h-[400px] flex shadow-lg rounded-xl" wire:submit="authenticate">
        <div class="w-1/2 h-full flex flex-col justify-center items-center gap-3 bg-primary rounded-l-xl">
            <img src="{{ asset('images/logo.png') }}" alt="canngopi_logo" class="h-[200px] object-cover">
            <span class="text-white text-xl font-semibold">Can Ngopi - Point of Sale</span>
        </div>

        <div class="w-1/2 h-full bg-white rounded-r-xl flex flex-col justify-center gap-8 p-8">
            <span class="text-xl">Welcome, Staff 👋</span>

            <div class="w-full flex flex-col gap-5">
                @foreach($formFields as $id => $field)
                    <x-input 
                        type="{{ $field['type'] }}" 
                        label="{{ ucfirst($field['type']) }}" 
                        icon="{{ $field['icon'] }}"
                        placeholder="Input your {{ $field['type'] }}"
                        wire:model="{{ $field['type'] }}"
                        input-class="focus:outline-none"
                        required 
                    />
                @endforeach
            </div>

            <x-button type="submit" icon="arrow-right-end-on-rectangle" position="left" color="red">Log In</x-button>
            <a href="{{ route('forgot-password') }}" class="flex justify-center items-center text-gray-400">
                <span>Forgot Password?</span>
            </a>
        </div>
    </form>
</div>
