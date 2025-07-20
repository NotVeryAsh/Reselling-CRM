<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfitsOverTimeChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected ?string $heading = 'Profits Over Time';
    
    public ?string $filter = 'month';
    
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
        // TODO Refactor this function to get all the profits for the last 30 days -> eg 
//        [
//            June 3rd => 10,
//            June 2nd => 15,
//            June 1st => 2
//        ]
        
        $filter = $this->filter;
        
        $platforms = Product::query()
            ->select(DB::raw("extract($this->filter from sold_at) as $this->filter, sum(purchased_price) as purchased_price, sum(sold_price) as sold_price"))
            ->notOwnItems()
            ->groupBy($filter)
            ->get()
            ->filter(fn (Product $product) => !is_null($product->$filter))
            ->map(function (Product $platform) {

                $filter = $this->filter;

                $date = Carbon::create()->$filter((int) $platform->$filter);
                
                $dateFormat = match ($this->filter) {
                    'day' => $date->format('D dS'),
                    'week' => $date->startOfWeek()->format('M dS'),
                    'month' => $date->format('M'),
                    'year' => $date->format('Y'),
                };
                    
                $platform->$filter = $dateFormat;
                $platform->profit = $platform->sold_price - $platform->purchased_price;
                return $platform;
            });

        return [
            'datasets' => [
                [
                    'label' => 'Profit',
                    'data' => $platforms->pluck('profit')->toArray(),
                ],
            ],
            'labels' => $platforms->pluck($this->filter)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
