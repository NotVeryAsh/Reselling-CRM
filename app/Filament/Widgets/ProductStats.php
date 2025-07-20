<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class ProductStats extends StatsOverviewWidget
{
    private Collection $profits;
    protected static ?int $sort = 1;
    
    function __construct()
    {
        $this->profits = $this->getProfits();
    }

    protected function getStats(): array
    {        
        return [
            $this->getSoldAndUnsoldItemPercentage(),
            $this->getAverageDaysInInventory(),
            $this->getLongestDaysInInventory(),
            $this->getLongestDaysCurrentlyInInventory(),
            $this->getCurrentProfits(),
            $this->getHighestProfitableProduct(),
            $this->getLeastProfitableProduct()
        ];
    }
    
    private function getSoldAndUnsoldItemPercentage(): Stat
    {
        $sold = Product::query()->sold()->count();
        $unsold = Product::query()->unsold()->count();
        $percent = number_format(($sold / ($sold + $unsold)) * 100);
        
        return Stat::make('Sold-Unsold Item %', sprintf('%s : %s - %s%%', $sold, $unsold, $percent));
    }
    
    private function getDaysInInventory(): Collection
    {
        return Product::query()
            ->select(['name', 'purchased_at', 'sold_at'])
            ->notOwnItems()
            ->get()
            ->map(function(Product $product) {
                $start = Carbon::parse($product->purchased_at);
                $end = Carbon::parse($product->sold_at ?? now());
                $product->days_in_inventory = max(1, ceil($start->diffInDays($end)));
                return $product;
            });
    }
    
    private function getAverageDaysInInventory(): Stat
    {
        $averageDays = $this->getDaysInInventory()
            ->pluck('days_in_inventory')
            ->first();
        
        return Stat::make('Average amount of days in inventory', $averageDays);
    }
    
    private function getLongestDaysInInventory(): Stat
    {
        $longestDays = $this->getDaysInInventory()
            ->sortByDesc('days_in_inventory')
            ->first();

        return Stat::make('Longest Days in inventory', "$longestDays->name : $longestDays->days_in_inventory days");
    }

    private function getLongestDaysCurrentlyInInventory(): Stat
    {
        $longestDays = $this->getDaysInInventory()
            ->whereNull('sold_at')
            ->sortByDesc('days_in_inventory')
            ->first();
        
        return Stat::make('Longest Currently in inventory', "$longestDays->name : $longestDays->days_in_inventory days");
    }
    
    private function getProfits(): Collection
    {
        return Product::query()
            ->select(['name', 'purchased_price', 'sold_price'])
            ->notOwnItems()
            ->sold()
            ->get()
            ->map(function (Product $product) {
                $product->profit = $product->sold_price - $product->purchased_price;
                return $product;
            });
    }
    
    private function getCurrentProfits(): Stat
    {
        $profits = $this->profits->sum('profit');
        return Stat::make('Current profits', '$' . $profits);
    }

    private function getHighestProfitableProduct(): Stat
    {
        $highesProfitable = $this->profits->sortByDesc('profit')->first();
        
        return Stat::make('Most profitable product', "$highesProfitable->name : $$highesProfitable->profit");
    }

    private function getLeastProfitableProduct(): Stat
    {
        $leastProfitable = $this->profits->sortBy('profit')->first();
        return Stat::make('Least profitable product', "$leastProfitable->name : $$leastProfitable->profit");
    }
}
