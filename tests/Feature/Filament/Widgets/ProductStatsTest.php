<?php

use App\Enums\PurchasingPlatform;
use App\Enums\SellingPlatform;
use App\Filament\Widgets\ProductStats;
use App\Models\Product;
use Illuminate\Support\Carbon;

it('gets the correct sold to unsold items percentage', function () {
    Product::factory()->create([
        'code' => 'test_product',
        'sold_at' => now(),
        'sold_price' => 1,
        'sold_platform' => SellingPlatform::FACEBOOK_MARKETPLACE,
    ]);

    Product::factory()->count(2)->create();

    $stat = (new ProductStats)->getSoldAndUnsoldItemPercentage();

    expect($stat->getValue())
        ->toBe('1 : 2 - 33%')
        ->and($stat->getLabel())
        ->tobe('Sold-Unsold Item %');
});

it('gets correct average days in inventory', function () {

    Product::factory()->create([
        'code' => 'test_product',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(9),
    ]);

    Product::factory()->create([
        'code' => 'test_product2',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(7),
        'sold_at' => now()->subDay(),
    ]);

    Product::factory()->create([
        'code' => 'test_product3',
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'purchased_at' => now()->subDays(100),
    ]);

    $stat = (new ProductStats)->getAverageDaysInInventory();

    expect($stat->getValue())
        ->toBe(8.0)
        ->and($stat->getLabel())
        ->tobe('Average amount of days in inventory');
});

it('get correct longest days in inventory', function () {

    Product::factory()->create([
        'code' => 'test_product',
        'name' => 'longest item in inventory',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(40),

    ]);

    Product::factory()->create([
        'code' => 'test_product2',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(9),
        'sold_at' => now()->subDay(),
    ]);

    Product::factory()->create([
        'code' => 'test_product3',
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'purchased_at' => now()->subDays(100),
    ]);

    $stat = (new ProductStats)->getLongestDaysInInventory();

    expect($stat->getValue())
        ->toBe('longest item in inventory : 41 days')
        ->and($stat->getLabel())
        ->tobe('Longest days in inventory');
});

it('gets correct longest days currently in inventory', function () {
    Product::factory()->create([
        'code' => 'test_product',
        'name' => 'longest item in inventory',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(14),

    ]);

    Product::factory()->create([
        'code' => 'test_product2',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(9),
        'sold_at' => now()->subDay(),
    ]);

    Product::factory()->create([
        'code' => 'test_product3',
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'purchased_at' => now()->subDays(100),
    ]);

    $stat = (new ProductStats)->getLongestDaysCurrentlyInInventory();

    expect($stat->getValue())
        ->toBe('longest item in inventory : 15 days')
        ->and($stat->getLabel())
        ->tobe('Longest days currently in inventory');
});

it('gets correct current profits', function () {
    Product::factory()->create([
        'code' => 'test_product',
        'purchased_price' => 1,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_at' => now(),
        'sold_price' => 10,
    ]);

    Product::factory()->create([
        'code' => 'test_product2',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_price' => 15,
    ]);

    Product::factory()->create([
        'code' => 'test_product3',
        'purchased_price' => 1,
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'sold_at' => now(),
        'sold_price' => 10,
    ]);

    $stat = (new ProductStats)->getCurrentProfits();

    expect($stat->getValue())
        ->toBe('$9')
        ->and($stat->getLabel())
        ->tobe('Profit on sold items');
});

it('gets correct highest profitable product', function () {
    Product::factory()->create([
        'code' => 'test_product',
        'name' => 'highest profitable product',
        'purchased_price' => 3,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_at' => now(),
        'sold_price' => 20,
    ]);

    Product::factory()->create([
        'code' => 'test_product2',
        'purchased_price' => 4,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_at' => now(),
        'sold_price' => 5,
    ]);

    Product::factory()->create([
        'code' => 'test_product3',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_price' => 15,
    ]);

    Product::factory()->create([
        'code' => 'test_product4',
        'purchased_price' => 1,
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'sold_at' => now(),
        'sold_price' => 10,
    ]);

    $stat = (new ProductStats)->getHighestProfitableProduct();

    expect($stat->getValue())
        ->toBe('highest profitable product : $17')
        ->and($stat->getLabel())
        ->tobe('Most profitable product');
});

it('gets correct least profitable product', function () {
    Product::factory()->create([
        'code' => 'test_product',
        'name' => 'least profitable product',
        'purchased_price' => 5,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(14),
        'sold_at' => now(),
        'sold_price' => -5,
    ]);

    Product::factory()->create([
        'code' => 'test_product2',
        'purchased_price' => 20,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_at' => now(),
        'sold_price' => 19,
    ]);

    Product::factory()->create([
        'code' => 'test_product3',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_price' => 15,
    ]);

    Product::factory()->create([
        'code' => 'test_product4',
        'purchased_price' => 1,
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'purchased_at' => now()->subDays(14),
        'sold_at' => now(),
        'sold_price' => 10,
    ]);

    $stat = (new ProductStats)->getLeastProfitableProduct();

    expect($stat->getValue())
        ->toBe('least profitable product : $-10')
        ->and($stat->getLabel())
        ->tobe('Least profitable product');
});

it('gets correct overall profit', function () {
    Product::factory()->create([
        'code' => 'test_product',
        'purchased_price' => 2,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_at' => now(),
        'sold_price' => -2,
    ]);

    Product::factory()->create([
        'code' => 'test_product2',
        'purchased_price' => 0,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_at' => now(),
        'sold_price' => 10,
    ]);

    Product::factory()->create([
        'code' => 'test_product3',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(9),
        'purchased_price' => 5,
    ]);

    Product::factory()->create([
        'code' => 'test_product4',
        'purchased_price' => 1,
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'purchased_at' => now()->subDays(14),
        'sold_at' => now(),
        'sold_price' => 10,
    ]);

    $stat = (new ProductStats)->getOverallProfit();

    expect($stat->getValue())
        ->toBe('$1')
        ->and($stat->getLabel())
        ->tobe('Overall profit');
});

it('it returns correct products from get products query', function () {

    Product::factory()->create([
        'code' => 'test_product',
        'name' => 'test product',
        'purchased_price' => 1,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_at' => now(),
        'sold_price' => 5,
    ]);

    Product::factory()->create([
        'code' => 'test_product2',
        'name' => 'test product 2',
        'purchased_price' => -1,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'sold_at' => now(),
        'sold_price' => 4,
    ]);

    Product::factory()->create([
        'code' => 'test_product3',
        'purchased_price' => -2,
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
    ]);

    Product::factory()->create([
        'code' => 'test_product4',
        'purchased_price' => 1,
        'purchased_platform' => PurchasingPlatform::OWN_ITEMS,
        'purchased_at' => now()->subDays(14),
        'sold_at' => now(),
        'sold_price' => 10,
    ]);

    $stat = (new ProductStats)->getProfits();

    expect($stat->toArray())
        ->toContain([
            'name' => 'test product',
            'purchased_price' => 1,
            'sold_price' => 5,
            'profit' => 4,
        ],
            [
                'name' => 'test product 2',
                'purchased_price' => -1,
                'sold_price' => 4,
                'profit' => 5,
            ]);
});

it('gets correct days in inventory', function () {

    Carbon::setTestNow();

    Product::factory()->create([
        'code' => 'test_product',
        'name' => 'test product',
        'purchased_platform' => PurchasingPlatform::GARAGE_SALE,
        'purchased_at' => now()->subDays(40),
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

    $stat = (new ProductStats)->getDaysInInventory();

    expect($stat)
        ->contains(fn ($item) => $item['name'] === 'test product' &&
            $item['sold_at'] === null &&
            $item['days_in_inventory'] === 41.0 &&
            $item['purchased_at']->format('Y-m-d') === now()->subDays(40)->format('Y-m-d')
        )
        ->toBeTrue()
        ->and($stat)
        ->contains(fn ($item) => $item['name'] === 'test product 2' &&
            $item['sold_at']->format('Y-m-d') === now()->subDay()->format('Y-m-d') &&
            $item['days_in_inventory'] === 8.0 &&
            $item['purchased_at']->format('Y-m-d') === now()->subDays(9)->format('Y-m-d')
        )
        ->toBeTrue();
});
