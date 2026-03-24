<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Jobs\ExportOrderJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchExportOrderJob implements ShouldQueue
{
    public function handle(OrderConfirmed $event): void
    {
        ExportOrderJob::dispatch($event->order);
    }
}
