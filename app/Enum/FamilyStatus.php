<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;
enum FamilyStatus: string implements HasLabel
{
    case Single = 'single';
    case Married = 'married';
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Single => 'Single',
            self::Married => 'Married'
        };
    }
}
