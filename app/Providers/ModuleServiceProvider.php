<?php

namespace App\Modules\Tickets;

use Illuminate\Support\ServiceProvider;

class TicketServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TicketRepository::class);
        $this->app->bind(TicketService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/Views', 'tickets');
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        
        // Register events
        Event::listen(TicketCreated::class, SendTicketNotification::class);
    }
}
