<?php

namespace App\Models;

use App\Enums\PurchasingPlatform;
use App\Enums\SellingPlatform;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUlids, SoftDeletes;

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
}
