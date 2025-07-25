<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnsoldProductCountOverTimeChart extends ChartWidget
{
    protected static ?int $sort = 4;
    protected ?string $heading = 'Unsold Product Count Over Time';
    
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

        $method = 'sub' . ucwords($filter) . 's';
        $unit = match ($filter) {
            'day' => 30,
            // 6 months
            'week' => 26,
            'month' => 6,
            'year' => 5,
        };

        $periods = CarbonPeriod::create(now()->$method($unit), "1 $filter", now());

        $time = $filter === 'day' ? 'DOY' : $filter;
        
        // TODO Refactor query - it seems to miss one record (displays 73 unsold items instead of 74) 
        $platforms = Product::query()
            ->select(DB::raw("extract($time from purchased_at) as $filter, extract(year from purchased_at) as year, count(code) as count"))
            ->notOwnItems()
            ->unsold()
            ->groupBy($filter, 'year')
            ->get()
            ->filter(fn (Product $product) => !is_null($product->$filter))
            ->map(function (Product $platform) use($filter) {
                $platform->$filter = Carbon::create()->year(intval($platform->year))->$filter((int) $platform->$filter);
                return $platform;
            })
            ->flatten()
            ->filter(fn ($product) => $product instanceof Product && $product->$filter instanceof Carbon);

        $results = collect();

        $currentCount = 0;
        
        collect($periods)->map(function ($period) use ($platforms, $filter, $results, &$currentCount) {
            $platform = $platforms->where(function ($product) use($period, $filter) {
                $periodDateFormat = $this->getDateFriendlyFormat($period);
                $productDateFormat = $this->getDateFriendlyFormat($product->$filter);

                return $periodDateFormat === $productDateFormat;
            })->first();

            $dateFormat = $this->getDateFriendlyFormat($period);

            $count = $platform?->count ?? 0;
            $currentCount += $count;
            
            $results->push([
                $filter => $dateFormat,
                'count' => $currentCount
            ]);
        });
        
        return [
            'datasets' => [
                [
                    'label' => 'Amount',
                    'data' => $results->pluck('count')->toArray(),
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
