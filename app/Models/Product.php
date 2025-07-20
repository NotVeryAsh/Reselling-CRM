<?php

namespace App\Models;

use App\Enums\PurchasingPlatform;
use App\Enums\SellingPlatform;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'purchased_at',
        'purchased_price',
        'purchased_platform',
        'sold_at',
        'sold_price',
        'sold_platform',
    ];

    protected $casts = [
        'purchased_at' => 'date',
        'purchased_platform' => PurchasingPlatform::class,
        'sold_at' => 'date',
        'sold_platform' => SellingPlatform::class,
    ];

    #[Scope]
    protected function sold(Builder $query): void
    {
        $query->whereNotNull('sold_at');
    }

    #[Scope]
    protected function unSold(Builder $query): void
    {
        $query->whereNull('sold_at');
    }

    #[Scope]
    protected function notOwnItems(Builder $query): void
    {
        $query->whereNot('purchased_platform', PurchasingPlatform::OWN_ITEMS->value);
    }

    #[Scope]
    protected function ownItems(Builder $query): void
    {
        $query->where('purchased_platform', PurchasingPlatform::OWN_ITEMS->value);
    }
}
