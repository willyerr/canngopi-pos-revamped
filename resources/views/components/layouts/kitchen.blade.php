<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Can Ngopi | Point of Sale - Cashier</title>

        <tallstackui:script /> 
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @php
            $navigators = [      
                [
                    'label' => 'Order List',
                    'icon' => 'clipboard-document-list',
                    'route' => 'kitchen.order-list'    
                ],
                [
                    'label' => 'Logout',
                    'icon' => 'arrow-left-end-on-rectangle',
                    'route' => 'logout'
                ]
            ];

            $currentRouteName = Route::currentRouteName();
            $currentPageName = collect($navigators)->firstWhere('route', $currentRouteName)['label'] ?? 'Can Ngopi | Point of Sale';
        @endphp

        <x-dialog />
        <x-toast z-index="z-50" /> 

        <section class="h-screen flex flex-col justify-between">
            <header class="h-[60px] flex items-center p-3 shadow text-sm">
                <div class="w-[45%]">
                    <span>Can Ngopi Point of Sale</span>
                </div>
                <div class="w-[10%] h-full flex justify-center items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="canngopi-logo" class="h-full object-cover" />
                </div>
                <div class="w-[45%] flex justify-end items-center gap-3">
                    <x-avatar color="stone" xs />
                    <span>{{ auth()->user()->fullname }}</span>
                </div>
            </header>

            {{ $slot }}

            <nav class="h-[50px] flex bg-primary shadow">
                @foreach($navigators as $nav)
                    @if($currentRouteName === $nav['route'])
                        <div class="grow flex justify-center items-center gap-3 text-white bg-red-700 opacity-70 cursor-not-allowed">
                            <x-icon :name="$nav['icon']" class="h-5 w-5" />
                            <span>{{ $nav['label'] }}</span>
                        </div>
                    @else
                        <a href="{{ route($nav['route']) }}" class="grow flex justify-center items-center gap-3 text-white hover:bg-red-500" wire:navigate>
                            <x-icon :name="$nav['icon']" class="h-5 w-5" />
                            <span>{{ $nav['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>
        </section>
    </body>
</html>
