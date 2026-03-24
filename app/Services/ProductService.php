<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get paginated list of products with optional filters.
     * Results are cached in Redis using cache tags for easy invalidation.
     */
    public function getList(array $filters): LengthAwarePaginator
    {
        $cacheKey = 'products:' . md5(json_encode($filters));

        return Cache::tags(['products'])->remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            $query = Product::query();

            if (! empty($filters['category'])) {
                $query->byCategory($filters['category']);
            }

            if (! empty($filters['search'])) {
                $query->search($filters['search']);
            }

            return $query->orderBy('name')->paginate($filters['per_page'] ?? 15);
        });
    }
}
