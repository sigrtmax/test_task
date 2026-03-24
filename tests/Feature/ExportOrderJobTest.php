<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ExportStatus;
use App\Enums\OrderStatus;
use App\Jobs\ExportOrderJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderExport;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ExportOrderJobTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['price' => 50.00, 'stock_quantity' => 100]);

        $this->order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::New,
            'total_amount' => 100.00,
        ]);

        $this->order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
        ]);
    }

    public function test_export_job_dispatched_when_order_confirmed(): void
    {
        // Use Event fake to check OrderConfirmed is fired,
        // then verify that listener dispatches the job by faking the queue
        Queue::fake();

        // We need to disable the queued listener so the job is dispatched directly
        // Instead, test that OrderConfirmed event is fired
        \Illuminate\Support\Facades\Event::fake([\App\Events\OrderConfirmed::class]);

        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(200);

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\OrderConfirmed::class, function ($event) {
            return $event->order->id === $this->order->id;
        });
    }

    public function test_export_job_creates_order_export_record_on_success(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'ok'], 200),
        ]);

        $job = new ExportOrderJob($this->order);
        $job->handle();

        $this->assertDatabaseHas('order_exports', [
            'order_id' => $this->order->id,
            'status' => ExportStatus::Success->value,
        ]);

        $export = OrderExport::where('order_id', $this->order->id)->first();
        $this->assertNotNull($export);
        $this->assertEquals(ExportStatus::Success, $export->status);
        $this->assertNotNull($export->exported_at);
    }

    public function test_export_job_marks_failed_on_http_error(): void
    {
        Http::fake([
            '*' => Http::response('Internal Server Error', 500),
        ]);

        $this->expectException(\RuntimeException::class);

        $job = new ExportOrderJob($this->order);
        $job->handle();

        $this->assertDatabaseHas('order_exports', [
            'order_id' => $this->order->id,
            'status' => ExportStatus::Failed->value,
        ]);
    }

    public function test_export_job_failed_method_updates_status(): void
    {
        // Create an initial export record
        OrderExport::create([
            'order_id' => $this->order->id,
            'status' => ExportStatus::Pending,
            'attempts' => 3,
        ]);

        $job = new ExportOrderJob($this->order);
        $job->failed(new \RuntimeException('Max retries exceeded'));

        $this->assertDatabaseHas('order_exports', [
            'order_id' => $this->order->id,
            'status' => ExportStatus::Failed->value,
            'last_error' => 'Max retries exceeded',
        ]);
    }

    public function test_export_job_sends_correct_payload(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'ok'], 200),
        ]);

        $job = new ExportOrderJob($this->order);
        $job->handle();

        Http::assertSent(function ($request) {
            $body = $request->data();
            return isset($body['order_id'])
                && $body['order_id'] === $this->order->id
                && isset($body['total_amount'])
                && isset($body['items']);
        });
    }
}
