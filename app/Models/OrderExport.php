<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'attempts',
        'last_error',
        'exported_at',
    ];

    protected $casts = [
        'status' => ExportStatus::class,
        'attempts' => 'integer',
        'exported_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
