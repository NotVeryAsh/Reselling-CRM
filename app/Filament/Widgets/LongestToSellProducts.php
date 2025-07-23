<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class LongestToSellProducts extends ChartWidget
{
    protected ?string $heading = 'Longest To Sell Products';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $products = Product::query()
            ->select(['name', 'purchased_at', 'sold_at'])
            ->notOwnItems()
            ->get()
            ->map(function(Product $product) {
                $start = Carbon::parse($product->purchased_at);
                $end = Carbon::parse($product->sold_at ?? now());
                $product->days_in_inventory = max(1, ceil($start->diffInDays($end)));
                return $product;
            })
            ->sortByDesc('days_in_inventory')
            ->take(10);

        return [
            'datasets' => [
                [
                    'label' => 'Product',
                    'data' => $products->pluck('days_in_inventory')->toArray(),
                ]
            ],
            'labels' => $products->pluck('name')->toArray(),
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
}
