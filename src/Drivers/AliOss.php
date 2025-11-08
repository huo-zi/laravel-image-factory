<?php

namespace Huozi\ImageProcess\Drivers;

use Huozi\ImageProcess\Text;
use Illuminate\Support\Str;

class AliOss extends AbstractDriver
{

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $handlers;

    public function __construct($app = null, $config = [])
    {
        parent::__construct($app, $config);

        $this->handlers = collect();
    }

    /**
     * @inheritDoc
     */
    public function resize($w, $h = null, $mode = null)
    {
        switch ($mode) {
            case 'p':
                $params = ['p' => $w];
                break;
            case 'l':
                $params = ['l' => $w];
                break;
            case 's':
                $params = ['s' => $w];
                break;
            case 'pad':
                $params = \compact('w', 'h') + ['m' => 'pad'];
                break;
            case 'fixed':
                $params = \compact('w', 'h') + ['m' => 'fixed'];
                break;
            case 'fill':
            default:
                $params = \compact('w', 'h') + ['m' => 'fill'];
                break;
        }

        $this->handlers->put('resize', $params);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function crop($w = 0, $h = 0, $x = 0, $y = 0, $g = 'NorthWest')
    {
        $this->handlers->put('crop', \compact('w', 'h', 'x', 'y') + ['g' => static::formatGravity($g)]);
        return $this;
    }

    public function circle($r)
    {
        $this->handlers->put('circle', \compact('r'));
        return $this;
    }

    public function radius($r)
    {
        $this->handlers->put('rounded-corners', \compact('r'));
        return $this;
    }

    public function blur($r, $s)
    {
        $this->handlers->put('blur', \compact('r', 's'));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function imageWatermark(string $path, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = 0)
    {
        $this->handlers->put('watermark', \compact('x', 'y', 't', 'fill') + [
            'g' => static::formatGravity($g),
            'image' => static::safeBase64Encode(ltrim($path, '/')),
        ]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function textWatermark(Text $text, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = 0)
    {
        $this->handlers->put('watermark', \compact('x', 'y', 't', 'fill') + [
            'g' => static::formatGravity($g),
            'text' => static::safeBase64Encode($text->text),
        ] + \array_filter([
            'type' => static::safeBase64Encode($text->font),
            'color' => ltrim($text->color, '#'),
            'size' => $text->size,
        ]));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function format(string $type)
    {
        $this->handlers->put('format', $type);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function quality(int $value)
    {
        $this->handlers->put('quality', $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flip(int $mode)
    {
        $this->handlers->put('flip', $mode);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rotate(int $value)
    {
        $this->handlers->put('rotate', $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function contrast(int $value)
    {
        $this->handlers->put('contrast', $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sharpen(int $value)
    {
        $this->handlers->put('sharpen', $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function interlace(int $value = 0)
    {
        $this->handlers->put('interlace', $value);
        return $this;
    }

    protected static function formatGravity($g)
    {
        $words = Str::ucsplit($g);
        return \count($words) > 1 ? \array_reduce($words, function($g, $word) {
            return $g . \strtolower($word[0]);
        }) : $g;
    }

    protected function handle() : string
    {
        $image = $this->image;
        if (!\strpos($image, 'x-oss-process')) {
            $image .= '?x-oss-process=image';
        }

        return $this->handlers->reduce(function($image, $value, $key) {
            return \sprintf(
                '%s/%s,%s',
                $image,
                $key,
                \is_array($value) ? \implode(',', \array_map(
                    function($key, $val) {
                        return $key . '_' . $val;
                    },
                    \array_keys($value),
                    \array_values($value)
                )) : $value
            );
        }, $image);
    }

}