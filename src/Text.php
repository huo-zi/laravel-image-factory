<?php

namespace Huozi\ImageProcess;

/**
 * @property string $text 文字内容
 * @property string $font 字体
 * @property string $color 16进制色 如#FF0000
 * @property int $size 字体大小
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