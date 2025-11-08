<?php

namespace Huozi\ImageProcess;

use Illuminate\Support\Str;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Plugin\AbstractPlugin;

class ToolsPlugin extends AbstractPlugin
{

    private $driver;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'tools';
    }

    public function handle($path)
    {
        if ($this->filesystem instanceof Filesystem) {
            $adapter = $this->filesystem->getAdapter();
            if (method_exists($this->filesystem, 'getUrl')) {
                $path = $this->filesystem->getUrl($path);
            } elseif (method_exists($adapter, 'getUrl')) {
                $path = $adapter->getUrl($path);
            } elseif ($adapter instanceof LocalAdapter) {
                $path = $this->getLocalUrl($path);
            } else {
                throw new \RuntimeException('This driver does not support retrieving URLs.');
            }

            return $this->driver->image($path);
        } 

        throw new \RuntimeException('This driver does not support retrieving URLs.');
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string  $path
     * @return string
     */
    protected function getLocalUrl($path)
    {
        $config = $this->filesystem->getConfig();

        // If an explicit base URL has been set on the disk configuration then we will use
        // it as the base URL instead of the default path. This allows the developer to
        // have full control over the base path for this filesystem's generated URLs.
        if ($config->has('url')) {
            return rtrim($config->get('url'), '/') . '/'. ltrim($path, '/');
        }

        $path = '/storage/'.$path;

        // If the path contains "storage/public", it probably means the developer is using
        // the default disk to generate the path instead of the "public" disk like they
        // are really supposed to use. We will remove the public from this path here.
        if (Str::contains($path, '/storage/public/')) {
            return Str::replaceFirst('/public/', '/', $path);
        }

        return $path;
    }

}