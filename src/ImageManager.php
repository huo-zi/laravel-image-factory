<?php

namespace Huozi\ImageFactory;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Manager;

class ImageManager extends Manager
{

    public function getDefaultDriver()
    {
        return $this->config->get('filesystems.default');
    }

    public function image($image)
    {
        $this->driver()->image($image);
        return $this;
    }

    public function __toString()
    {
        return $this->driver()->render();
    }

    public function config($key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    public static function extendDefaultDrivers()
    {
        /** @var static */
        $manager = app(static::class);

        foreach ($manager->config('filesystems.disks') as $name => $config) {
            $driver = $config['driver'] ?? '';
            switch ($driver) {
                case 'oss':
                case 'aliyun':
                case 'aliyunoss':
                    $className = Drivers\Local::class;
                    break;
                case 'cos':
                case 'tencentcos':
                    $className = Drivers\TencentCos::class;
                    break;
                case 'qiniu':
                case 'qiniuyun':
                case 'qiniuyunoss':
                    $className = Drivers\QiniuOss::class;
                case 'local':
                    $className = Drivers\Local::class;
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf(
                        'Unable to resolve NULL driver for [%s].', $driver
                    ));
            }

            $manager->extend($name, function ($app) use ($name, $className) {
                $driver = new $className($app, $this->config->get('filesystems.disks.' . $name));

                Log::debug('driver_class', [
                    get_class(Storage::disk($name)),
                ]);
                return $driver;
            });
        }
    }

}