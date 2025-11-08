<?php

namespace Huozi\ImageProcess\Drivers;

use Illuminate\Support\Str;

class HuaweiObs extends AliOss
{

    protected static function formatGravity($g)
    {
        $g = \str_replace(['North', 'Sorth', 'West', 'East'], ['Top', 'Bottom', 'Left', 'Right'], $g);
        $words = Str::ucsplit($g);

        return \count($words) > 1 ? \array_reduce($words, function($g, $word) {
            return $g . \strtolower($word[0]);
        }) : \strtolower($g);
    }
}