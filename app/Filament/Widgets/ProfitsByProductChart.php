<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class ProfitsByProductChart extends ChartWidget
{
    protected ?string $heading = 'Profits By Product';

    protected static ?int $sort = 2;

    public function getData(): array
    {
        $products = Product::query()
            ->select(['name', 'purchased_price', 'sold_price'])
            ->notOwnItems()
            ->sold()
            ->get()
            ->map(function (Product $product) {
                $product->profit = $product->sold_price - $product->purchased_price;
                return $product;
            })
            ->sortByDesc('profit');

        return [
            'datasets' => [
                [
                    'label' => 'Profit',
                    'data' => $products->pluck('profit')->toArray(),
                    'backgroundColor' => '#14b8a6',
                    'borderColor' => '#2dd4bf',
                ],
                [
                    'label' => 'Sold Price',
                    'data' => $products->pluck('purchased_price')->toArray(),
                ],
            ],
            'labels' => $products->pluck('name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
