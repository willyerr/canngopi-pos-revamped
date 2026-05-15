<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Can Ngopi | Point of Sale - Accounting Dashboard</title>

        <tallstackui:script /> 
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @php
            $navigators = [          
                [
                    'label' => 'User Management',
                    'icon' => 'users',
                    'route' => 'it.user-management'
                ],
                [
                    'label' => 'Log Out',
                    'icon' => 'arrow-left-end-on-rectangle',
                    'route' => 'logout'
                ]
            ];

            $currentRouteName = Route::currentRouteName();
            $currentPageName = collect($navigators)->firstWhere('route', $currentRouteName)['label'] ?? 'Can Ngopi | Point of Sale';
        @endphp

        <x-dialog />
        <x-toast z-index="z-50" /> 
        <x-layout>
            <!-- Header Section -->
            <x-slot:header>
                <x-layout.header>
                    <x-slot:left>
                        <span>{{ $currentPageName }}</span>
                    </x-slot:left>
                    
                    <x-slot:right>
                        <x-avatar color="stone" xs />
                        <span>{{ Auth::user()->fullname }}</span>
                    </x-slot:right>
                </x-layout.header>
            </x-slot:header>

            <!-- Sidebar Section -->
            <x-slot:menu>
                <x-side-bar smart navigate>
                    <x-slot:brand>
                        <div class="flex flex-col items-center gap-y-2">
                            <img src="{{ asset('images/logo.png') }}" alt="canngopi-logo" class="h-[125px] mt-4" />
                            <span class="text-white font-semibold">Can Ngopi - Point of Sale</span>
                        </div>
                    </x-slot:brand>

                    @foreach($navigators as $id => $nav)
                        <x-side-bar.item class="text-green-500" wire:key="{{ $id }}" text="{{ $nav['label'] }}" icon="{{ $nav['icon'] }}" route="{{ route($nav['route']) }}" />
                    @endforeach
                </x-side-bar>
            </x-slot:menu>
            {{ $slot }}
        </x-layout>
    </body>
</html>
