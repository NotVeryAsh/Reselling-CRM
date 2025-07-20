<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class SoldOwnItemsChart extends ChartWidget
{
    protected static ?int $sort = 5;
    protected ?string $heading = 'Sold Own Items';

    protected function getData(): array
    {
        $products = Product::query()
            ->select(['name', 'sold_price'])
            ->ownItems()
            ->sold()
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Item',
                    'data' => $products->pluck('sold_price')->toArray(),
                ],
            ],
            'labels' => $products->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
