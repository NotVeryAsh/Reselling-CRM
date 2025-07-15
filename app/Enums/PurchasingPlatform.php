<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PurchasingPlatform: string implements HasLabel
{
    case GARAGE_SALE = 'garage sale';
    case CHEAPER_BY_THE_DAY = 'cheaper by the day';
    case WHATNOT = 'whatnot';
    case STORE_CLEARANCE = 'store clearance';
    case OWN_ITEMS = 'own items';
    case STREET_PICKUP = 'street pickup';

    public function getLabel(): ?string
    {
        return ucwords($this->value);
    }
}
