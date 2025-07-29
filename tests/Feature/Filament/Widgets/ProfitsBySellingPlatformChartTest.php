<?php

use App\Enums\PurchasingPlatform;
use App\Enums\SellingPlatform;
use App\Filament\Widgets\LongestToSellProductsChart;
use App\Filament\Widgets\ProfitsByProductChart;
use App\Filament\Widgets\ProfitsBySellingPlatformChart;
use App\Models\Product;
use Illuminate\Support\Carbon;

it('returns correct data', function () {
    Carbon::setTestNow();

    Product::factory()->create([
        'purchased_price' => 10,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_platform' => SellingPlatform::FACEBOOK_MARKETPLACE,
        'sold_at' => now()->addDays(2),
        'sold_price' => 20
    ]);

    Product::factory()->create([
        'purchased_price' => 0,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_platform' => SellingPlatform::FACEBOOK_MARKETPLACE,
        'sold_at' => now()->addDays(2),
        'sold_price' => -5
    ]);
    
    Product::factory()->create([
        'purchased_price' => 10,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_platform' => SellingPlatform::AMAZON,
        'sold_at' => now()->subDay(),
        'sold_price' => 1
    ]);

    Product::factory()->create([
        'purchased_price' => 1,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_platform' => SellingPlatform::OFFERUP,
        'sold_at' => now()->subDay(),
        'sold_price' => 12
    ]);

    Product::factory()->create([
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'purchased_at' => now()->subDays(100),
    ]);

    Product::factory()->create([
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
    ]);

    $chart = (new ProfitsBySellingPlatformChart)->getData();
    
    expect($chart['datasets'][0]['data'])
        ->toEqual([
            11,
            5,
            -9
        ])
        ->and($chart['datasets'][0]['label'])
        ->toEqual('Profit')
        ->and($chart['labels'])
        ->toEqual([
            'Offerup', 
            'Facebook Marketplace', 
            'Amazon',
        ]);
});
