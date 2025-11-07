<?php

namespace Huozi\ImageFactory;


/**
 * @property string $text
 * @property string $font
 * @property string $color
 * @property int $size
 */
class Text extends \ArrayObject
{

    public function __construct($array = [])
    {
        parent::__construct($array + [
            'text' => '',
            'font' => '',
            'color' => '',
            'size' => '',
        ]);
    }

    public function __set($key, $value)
    {
        return $this->offsetSet($key, $value);
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }
}