<?php

namespace Huozi\ImageProcess;

use Huozi\ImageProcess\ImageManager;
use Illuminate\Support\Facades\Facade as LaravelFacade;

/** 
 * @method static void add(string $key)
 * @method static bool has(string $key)
 */
class Facade extends LaravelFacade
{

    protected static function getFacadeAccessor()
    {
        return ImageManager::class;
    }
}
