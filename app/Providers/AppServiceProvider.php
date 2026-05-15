<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use TallStackUi\Facades\TallStackUi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TallStackUi::personalize()
            ->layout('header')
            ->block('slots.right', 'flex items-center gap-3');

        TallStackUi::personalize()
            ->sideBar()
            ->block([
                'desktop.wrapper.second' => 'dark:bg-dark-700 dark:border-dark-600 flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-primary px-2 pb-4',
                'desktop.wrapper.fifth' => 'flex flex-1 flex-col gap-y-3',
            ]);

        TallStackUi::personalize()
            ->sideBar('item')
            ->block([
                'item.state.current' => 'text-black bg-[rgba(255,237,230,0.3)] dark:bg-dark-600 dark:text-white',
                'item.state.normal' => 'text-white hover:bg-[rgba(255,237,230,0.3)] dark:hover:bg-dark-600 dark:text-white',
                'item.icon' => 'text-white h-6 w-6 shrink-0 transition-all dark:text-white'
            ]);
    }
}
