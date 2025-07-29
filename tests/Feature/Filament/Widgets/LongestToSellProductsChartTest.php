<?php

use App\Enums\PurchasingPlatform;
use App\Filament\Widgets\LongestToSellProductsChart;
use App\Models\Product;
use Illuminate\Support\Carbon;

it('returns correct data', function () {
    Carbon::setTestNow();

    Product::factory()->create([
        'code' => 'test_product',
        'name' => 'test product',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(5),
        'sold_at' => now()->addDays(2),
    ]);

    Product::factory()->create([
        'code' => 'test_product2',
        'name' => 'test product 2',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(9),
        'sold_at' => now()->subDay(),
    ]);

    Product::factory()->create([
        'code' => 'test_product3',
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'purchased_at' => now()->subDays(100),
    ]);

    Product::factory()->create([
        'code' => 'test_product4',
        'name' => 'test product 4',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(9),
    ]);

    $chart = (new LongestToSellProductsChart())->getData();
    
    expect($chart['datasets'][0]['data'])
        ->toEqual([
            10.0,
            8.0,
            7.0
        ])
        ->and($chart['datasets'][0]['label'])
        ->toEqual('Product')
        ->and($chart['labels'])
        ->toEqual([
           'test product 4',
           'test product 2',
           'test product' 
        ]);
});

it('returns no more than 10 records', function() {
    Carbon::setTestNow();

    Product::factory()->count(12)->create([
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(5),
        'sold_at' => now()->addDays(2),
    ]);

    $chart = (new LongestToSellProductsChart())->getData();

    expect($chart['datasets'][0]['data'])
        ->toHaveCount(10)
        ->and($chart['labels'])
        ->toHaveCount(10);
});
