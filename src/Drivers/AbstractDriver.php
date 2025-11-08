<?php

namespace Huozi\ImageProcess\Drivers;

use Huozi\ImageProcess\Text;

abstract class AbstractDriver
{

    protected $app;

    protected $config;

    protected $image;

    public function __construct($app = null, $config = [])
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function image($path)
    {
        $this->image = $path;
        return $this;
    }

    /**
     * 缩放
     * @param integer $w 宽
     * @param integer $h 高
     * @param string $mode 缩放模式 默认:fill fill:等比缩放居中裁切 fixed:强制宽高缩略 pad:等比缩放空白填充 l:指定缩略长边等比缩放 s:指定缩略短边等比缩放 p:按比例缩放
     * @return static
     */
    abstract public function resize($w, $h = null, $mode = null);

    /**
     * 裁剪
     *
     * @param integer $w 宽
     * @param integer $h 高
     * @param integer $x
     * @param integer $y
     * @param string $g 原点位置 默认:NorthWest NorthWest|North|NorthEast|West|Center|East|SorthWest|Sorth|SorthEast 
     * @return static
     */
    abstract public function crop($w = 0, $h = 0, $x = 0, $y = 0, $g = 'NorthWest');

    /**
     * 格式化类型
     * @param string $type 图片格式 png/jpeg/gif/webp...
     * @return static
     */
    abstract public function format(string $type);

    /**
     * 图片水印
     * @param string $path 图片路径
     * @param integer $x 横轴边距
     * @param integer $y 纵轴边距
     * @param string $g 锚点位置 默认:SouthEast
     * @param integer $t 透明度 默认100不透明
     * @param bool $fill 是否铺满
     * @return static
     */
    abstract public function imageWatermark(
        string $path,
        int $x = 10,
        int $y = 10,
        string $g = 'SouthEast',
        int $t = 100,
        $fill = 0
    );

    /**
     * 图片水印
     * @param Text $text 水印文字
     * @param integer $x 横轴边距
     * @param integer $y 纵轴边距
     * @param string $g 锚点位置 默认:SouthEast
     * @param integer $t 透明度 默认100不透明
     * @param bool $fill 是否铺满
     * @return static
     */
    abstract public function textWatermark(
        Text $text,
        int $x = 10,
        int $y = 10,
        string $g = 'SouthEast',
        int $t = 100,
        $fill = 0
    );

    /**
     * 图片处理
     *
     * @return string
     */
    abstract protected function handle() : string;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->handle();
    }

    /**
     * safe base64Encode
     * @param string $string
     * @return string
     */
    protected static function safeBase64Encode(string $string)
    {
        return \str_replace(['+', '/', '='], ['-', '_', ''], \base64_encode($string));
    }
}