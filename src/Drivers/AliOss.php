<?php

namespace Huozi\ImageFactory\Drivers;

use Huozi\ImageFactory\Text;
use Illuminate\Support\Str;

/**
 * @method static format(string $type) 格式化
 * @method static quality(int $value) 
 * @method static flip(int $mode) 反转
 * @method static rotate(int $value) 旋转
 * @method static contrast(int $value) 对比度
 * @method static sharpen(int $value) 锐化
 * @method static interlace(int $value = 0) 显示方式 0 标准 1 渐进
 */
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

    public function crop($w = 0, $h = 0, $x = 0, $y = 0, $g = 'NorthWest')
    {
        $this->handlers->put('crop', \compact('w', 'h', 'x', 'y') + ['g' => $this->formatGravity($g)]);
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
    public function imageWatermark(string $path, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = false)
    {
        $this->handlers->put('watermark', \compact('x', 'y', 'g', 't', 'fill') + [
            'image' => static::base64Encode($path),
        ]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function textWatermark(Text $text, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = false)
    {
        $this->handlers->put('watermark', \compact('x', 'y', 'g', 't', 'fill') + [
            'text' => static::base64Encode($text->text),
            'type' => static::base64Encode($text->font),
            'color' => ltrim($text->color, '#'),
            'size' => $text->size,
        ]);
        return $this;
    }

    public function __call($name, $args)
    {
        $this->handlers->put($name, $args[0] ?? null);
        return $this;
    }

    protected function formatGravity($g)
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