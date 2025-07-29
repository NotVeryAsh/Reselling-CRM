<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProfitsBySellingPlatformChart extends ChartWidget
{
    protected ?string $heading = 'Profits By Selling Platform';

    protected static ?int $sort = 7;

    public function getData(): array
    {
        $platforms = Product::query()
            ->select(DB::raw('sold_platform, sum(purchased_price) as purchased_price, sum(sold_price) as sold_price'))
            ->notOwnItems()
            ->sold()
            ->groupBy('sold_platform')
            ->get()
            ->map(function (Product $platform) {
                $platform->profit = $platform->sold_price - $platform->purchased_price;
                $platform->platform = $platform->sold_platform->getLabel();

                return $platform;
            })
            ->sortByDesc('profit');

        return [
            'datasets' => [
                [
                    'label' => 'Profit',
                    'data' => $platforms->pluck('profit')->toArray(),
                ],
            ],
            'labels' => $platforms->pluck('platform')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
