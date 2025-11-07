<?php

namespace Huozi\ImageFactory\Drivers;

use Huozi\ImageFactory\Text;
use Illuminate\Support\Arr;

class QiniuOss extends AbstractDriver
{

    private $handlers = [];

    /**
     * @inheritDoc
     */
    public function resize($w, $h = null, $mode = null)
    {
        switch ($mode) {
            case 'p':
                $params = ['!', $w, 'p'];
                break;
            case 'l':
                $params = [$w, 'x', $h];
                break;
            case 's':
                $params = ['!', $w, 'x', $h, 'r'];
                break;
                break;
            case 'fixed':
                $params = [$w, 'x', $h, '!'];
                break;
            case 'fill':
            default:
                $params = [$w, 'x', $h];
                break;
        }

        Arr::set($this->handlers, 'imageMogr2.thumbnail', $params);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function crop($w = 0, $h = 0, $x = 0, $y = 0, $g = null)
    {
        if (!\is_null($g)) {
            Arr::set($this->handlers, 'imageMogr2.gravity', $g);
        }

        $params = \array_filter([$w, 'x', $h]);
        if ($x || $y) {
            $params = ['!', $w, 'x', $h, $x > 0 ? 'a' : '', $x, $y > 0 ? 'a' : '', $y];
        }

        Arr::set($this->handlers, 'imageMogr2.crop', $params);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function format(string $type)
    {
        Arr::set($this->handlers, 'imageMogr2.format', $type);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function quality(int $value)
    {
        Arr::set($this->handlers, 'imageMogr2.quality', $value);
        return $this;
    }

    public function interlace(int $value)
    {
        Arr::set($this->handlers, 'imageMogr2.interlace', $value);
        return $this;
    }
    
    public function rotate(int $value)
    {
        Arr::set($this->handlers, 'imageMogr2.rotate', $value);
        return $this;
    }

    public function blur($r, $s)
    {
        Arr::set($this->handlers, 'imageMogr2.blur', [$r, 'x', $s]);
        return $this;
    }

    public function sharpen(int $value)
    {
        Arr::set($this->handlers, 'imageMogr2.sharpen', $value);
        return $this;
    }

    public function radius($r)
    {
        Arr::set($this->handlers, 'roundPic.radius', $r);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function imageWatermark(string $path, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = false)
    {
        $this->handlers['watermark']['3'][] = [
            'image' => static::base64Encode($path),
            'dx' => $x,
            'dy' => $y,
            'gravity' => $g,
            'dissolve' => $t,
            'tile' => $fill ? 1 : 0,
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function textWatermark(Text $text, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = false)
    {
        $this->handlers['watermark']['3'][] = \array_filter([
            'text' => static::base64Encode($text->text),
            'font' => static::base64Encode($text->font),
            'fontsize' => $text->size,
            'fill' => static::base64Encode($text->color),
        ]) + [
            'dx' => $x,
            'dy' => $y,
            'gravity' => $g,
            'dissolve' => $t,
            'tile' => $fill ? 1 : 0,
        ];
        return $this;
    }

    protected function handle() : string
    {
        $image = $this->image;

        return $image . '?' . \collect($this->handlers)->map(function($value, $key) {
            return \sprintf(
                '%s/%s',
                $key,
                \implode('/', \array_map(function($val, $key) {
                    if (\is_array($val)) {
                        $val = \is_array($val[0]) ? \array_reduce($val, function($init, $item) {
                            return $init . ($init ? '/' : '') . \implode('/', \array_map(function($v, $k) {
                                return $k . '/' . $v;
                            }, \array_values($item), \array_keys($item)));
                        }) : \implode('', $val); 
                    }
                    return $key . '/' . $val; 
                }, \array_values($value), \array_keys($value)))
            );
        })->implode('|');
    }
}