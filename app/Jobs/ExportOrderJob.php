<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ExportStatus;
use App\Models\Order;
use App\Models\OrderExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExportOrderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [5, 10, 15];

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $export = OrderExport::firstOrCreate(
            ['order_id' => $this->order->id],
            ['status' => ExportStatus::Pending, 'attempts' => 0]
        );

        $export->increment('attempts');
        $export->update(['status' => ExportStatus::Pending]);

        $this->order->load('items.product');

        $response = Http::post(config('export.url'), [
            'order_id' => $this->order->id,
            'status' => $this->order->status->value,
            'total_amount' => (float) $this->order->total_amount,
            'customer_id' => $this->order->customer_id,
            'confirmed_at' => $this->order->confirmed_at?->toIso8601String(),
            'items' => $this->order->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_sku' => $item->product->sku,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
            ])->toArray(),
        ]);

        if ($response->successful()) {
            $export->update([
                'status' => ExportStatus::Success,
                'exported_at' => now(),
                'last_error' => null,
            ]);

            Log::info('Order exported successfully', ['order_id' => $this->order->id]);
        } else {
            $errorMessage = 'HTTP ' . $response->status() . ': ' . $response->body();
            $export->update([
                'status' => ExportStatus::Failed,
                'last_error' => $errorMessage,
            ]);

            Log::warning('Order export failed', [
                'order_id' => $this->order->id,
                'status' => $response->status(),
            ]);

            throw new \RuntimeException('Export failed: ' . $response->status());
        }
    }

    public function failed(\Throwable $e): void
    {
        OrderExport::where('order_id', $this->order->id)
            ->update([
                'status' => ExportStatus::Failed,
                'last_error' => $e->getMessage(),
            ]);

        Log::error('Order export job permanently failed', [
            'order_id' => $this->order->id,
            'error' => $e->getMessage(),
        ]);
    }
}
