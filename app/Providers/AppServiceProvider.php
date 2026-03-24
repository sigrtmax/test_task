<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\OrderConfirmed;
use App\Listeners\DispatchExportOrderJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(
            OrderConfirmed::class,
            DispatchExportOrderJob::class,
        );
    }
}
