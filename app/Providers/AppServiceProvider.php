<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

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
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $event->menu->add([
                'text' => 'Album',
                'url' => 'albums', // Adjust URL as needed
                'icon' => 'fas fa-fw fa-images',
            ]);
            $event->menu->add([
                'text' => 'Recycle',
                'url' => 'recycle', // Adjust URL as needed
                'icon' => 'fas fa-fw fa-trash-alt',
            ]);
            $event->menu->add([
                'text' => 'Settings',
                'url' => 'settings', // Adjust URL as needed
                'icon' => 'fas fa-fw fa-cog',
            ]);
        });
    }
}