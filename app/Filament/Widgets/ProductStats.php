<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class ProductStats extends StatsOverviewWidget
{
    public Collection $profits;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            $this->getSoldAndUnsoldItemPercentage(),
            $this->getAverageDaysInInventory(),
            $this->getLongestDaysInInventory(),
            $this->getLongestDaysCurrentlyInInventory(),
            $this->getCurrentProfits(),
            $this->getHighestProfitableProduct(),
            $this->getLeastProfitableProduct(),
            $this->getOverallProfit(),
        ];
    }

    public function getSoldAndUnsoldItemPercentage(): Stat
    {
        $sold = Product::query()->sold()->count();
        $unsold = Product::query()->unsold()->count();
        $percent = number_format(($sold / ($sold + $unsold)) * 100);

        return Stat::make('Sold-Unsold Item %', sprintf('%s : %s - %s%%', $sold, $unsold, $percent));
    }

    public function getDaysInInventory(): Collection
    {
        return Product::query()
            ->select(['name', 'purchased_at', 'sold_at'])
            ->notOwnItems()
            ->get()
            ->map(function (Product $product) {
                $start = Carbon::parse($product->purchased_at);
                $end = Carbon::parse($product->sold_at ?? now());
                $product->days_in_inventory = max(1, ceil($start->diffInDays($end)));

                return $product;
            });
    }

    public function getAverageDaysInInventory(): Stat
    {
        $averageDays = $this->getDaysInInventory()
            ->average('days_in_inventory');

        return Stat::make('Average amount of days in inventory', $averageDays);
    }

    public function getLongestDaysInInventory(): Stat
    {
        $longestDays = $this->getDaysInInventory()
            ->sortByDesc('days_in_inventory')
            ->first();

        return Stat::make('Longest days in inventory', "$longestDays->name : $longestDays->days_in_inventory days");
    }

    public function getLongestDaysCurrentlyInInventory(): Stat
    {
        $longestDays = $this->getDaysInInventory()
            ->whereNull('sold_at')
            ->sortByDesc('days_in_inventory')
            ->first();

        return Stat::make('Longest days currently in inventory', "$longestDays->name : $longestDays->days_in_inventory days");
    }

    public function getProfits(): Collection
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

    public function getCurrentProfits(): Stat
    {
        $profits = $this->getProfits()->sum('profit');

        return Stat::make('Profit on sold items', '$'.$profits);
    }

    public function getHighestProfitableProduct(): Stat
    {
        $highesProfitable = $this->getProfits()->sortByDesc('profit')->first();

        return Stat::make('Most profitable product', "$highesProfitable->name : $highesProfitable->profit");
    }

    public function getLeastProfitableProduct(): Stat
    {
        $leastProfitable = $this->getProfits()->sortBy('profit')->first();

        return Stat::make('Least profitable product', "$leastProfitable->name : $$leastProfitable->profit");
    }

    public function getOverallProfit(): Stat
    {
        $products = Product::query()
            ->select(['name', 'purchased_price', 'sold_price'])
            ->notOwnItems()
            ->get()
            ->map(function (Product $product) {
                $product->profit = $product->sold_price - $product->purchased_price;

                return $product;
            })
            ->sum('profit');

        return Stat::make('Overall profit', "$$products");
    }
}
