<?php

namespace App\Enums;

enum Feed: string
{
    case Main = 'main';
    case Memes = 'memes';

    public function label(): string
    {
        return match ($this) {
            self::Main => 'Main feed',
            self::Memes => 'Memes',
        };
    }

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $f) => [$f->value => $f->label()])
            ->all();
    }
}
