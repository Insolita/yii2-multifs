<?php
/**
 * Created by solly [03.06.17 17:33]
 */

namespace insolita\multifs\builders;

use insolita\multifs\contracts\IFilesystemBuilder;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class LocalFsBuilder
 */
class LocalFsBuilder implements IFilesystemBuilder
{
    /**
     * @var bool|string
     */
    private $basePath;
    
    /**
     * LocalFsBuilder constructor.
     *
     * @param $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = \Yii::getAlias($basePath);
    }
    
    /**
     * @return \League\Flysystem\Filesystem
     */
    public function build()
    {
        $adapter = new Local($this->basePath, LOCK_EX, Local::DISALLOW_LINKS,
            [
                'file' => ['public'  => 0744,'private' => 0700,],
                'dir'  => ['public'  => 0755,'private' => 0700],
            ]
        );
        $fs = new Filesystem($adapter);
        return $fs;
    }
}
