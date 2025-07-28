<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProfitsOverTimeChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Profits Over Time';

    public ?string $filter = 'day';

    protected function getFilters(): ?array
    {
        return [
            'day' => 'Daily',
            'week' => 'Weekly',
            'month' => 'Monthly',
            'year' => 'Yearly',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        $method = 'sub'.ucwords($filter).'s';
        $unit = match ($filter) {
            'day' => 30,
            // 6 months
            'week' => 26,
            'month' => 6,
            'year' => 5,
        };

        $periods = CarbonPeriod::create(now()->$method($unit), "1 $filter", now());

        $time = $filter === 'day' ? 'DOY' : $filter;

        $platforms = Product::query()
            ->select(DB::raw("extract($time from sold_at) as $filter, extract(year from sold_at) as year, sum(purchased_price) as purchased_price, sum(sold_price) as sold_price"))
            ->notOwnItems()
            ->groupBy($filter, 'year')
            ->limit($unit)
            ->get()
            ->filter(fn (Product $product) => ! is_null($product->$filter))
            ->map(function (Product $platform) use ($filter) {
                $platform->$filter = Carbon::create()->year(intval($platform->year))->$filter((int) $platform->$filter);
                $platform->profit = $platform->sold_price - $platform->purchased_price;

                return $platform;
            });

        $results = collect();

        $currentCount = 0;

        collect($periods)->map(function ($period) use ($platforms, $filter, $results, &$currentCount) {
            $platform = $platforms->where(function ($product) use ($period, $filter) {
                $periodDateFormat = $this->getDateFriendlyFormat($period);
                $productDateFormat = $this->getDateFriendlyFormat($product->$filter);

                return $periodDateFormat === $productDateFormat;
            })->first();

            $dateFormat = $this->getDateFriendlyFormat($period);

            $count = $platform?->profit ?? 0;
            $currentCount += $count;

            $results->push([
                $filter => $dateFormat,
                'profit' => $currentCount,
            ]);
        });

        return [
            'datasets' => [
                [
                    'label' => 'Profit',
                    'data' => $results->pluck('profit')->toArray(),
                ],
            ],
            'labels' => $results->pluck($filter)->toArray(),
        ];
    }

    private function getDateFriendlyFormat(Carbon $date): string
    {
        return match ($this->filter) {
            'day' => $date->format('M D dS'),
            'week' => $date->startOfWeek()->format('M dS'),
            'month' => $date->format('M'),
            'year' => $date->format('Y'),
        };
    }

    protected function getType(): string
    {
        return 'line';
    }
}
