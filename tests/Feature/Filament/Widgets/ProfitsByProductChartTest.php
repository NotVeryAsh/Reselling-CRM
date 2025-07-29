<?php

use App\Enums\PurchasingPlatform;
use App\Filament\Widgets\LongestToSellProductsChart;
use App\Filament\Widgets\ProfitsByProductChart;
use App\Models\Product;
use Illuminate\Support\Carbon;

it('returns correct data', function () {
    Carbon::setTestNow();

    Product::factory()->create([
        'code' => 'test_product',
        'name' => 'test product',
        'purchased_price' => 10,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(5),
        'sold_at' => now()->addDays(2),
        'sold_price' => 20
    ]);

    Product::factory()->create([
        'code' => 'test_product2',
        'name' => 'test product 2',
        'purchased_price' => 10,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(9),
        'sold_at' => now()->subDay(),
        'sold_price' => -10
    ]);

    Product::factory()->create([
        'code' => 'test_product3',
        'name' => 'test product 3',
        'purchased_price' => 1,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(9),
        'sold_at' => now()->subDay(),
        'sold_price' => 10
    ]);

    Product::factory()->create([
        'code' => 'test_product4',
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'purchased_at' => now()->subDays(100),
    ]);

    Product::factory()->create([
        'code' => 'test_product5',
        'name' => 'test product 5',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(9),
    ]);

    $chart = (new ProfitsByProductChart)->getData();
    
    expect($chart['datasets'][0]['data'])
        ->toEqual([
            10,
            9,
            -20
        ])
        ->and($chart['datasets'][0]['label'])
        ->toEqual('Profit')
        // Second data set
        ->and($chart['datasets'][1]['data'])
        ->toEqual([
            10.0,
            1.0,
            10.0,
        ])
        ->and($chart['datasets'][1]['label'])
        ->toEqual('Sold Price')
        ->and($chart['labels'])
        ->toEqual([
           'test product',
           'test product 3',
           'test product 2',
        ]);
});
