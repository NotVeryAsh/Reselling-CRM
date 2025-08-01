<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SellingPlatform: string implements HasLabel
{
    case AMAZON = 'amazon';
    case EBAY = 'ebay';
    case FACEBOOK_MARKETPLACE = 'facebook marketplace';
    case OFFERUP = 'offerup';
    case POSHMARK = 'poshmark';
    case IN_PERSON = 'in person';
    case DEPOP = 'depop';
    case GOODWILL = 'goodwill';

    public function getLabel(): ?string
    {
        return ucwords($this->value);
    }
}
