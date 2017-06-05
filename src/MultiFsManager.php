<?php
/**
 * Created by solly [04.06.17 6:29]
 */

namespace insolita\multifs;

use insolita\multifs\contracts\IFilesystemBuilder;
use insolita\multifs\contracts\IMultifsManager;
use League\Flysystem\MountManager;

/**
 * Class MultiFsManager
 */
class MultiFsManager extends MountManager implements IMultifsManager
{
    /**
     * @return array
     */
    public function listPrefixes()
    {
        return array_keys($this->filesystems);
    }
    
    /**
     * @param $prefix
     *
     * @return bool
     */
    public function hasFilesystem($prefix)
    {
        return isset($this->filesystems[$prefix]);
    }
    
    /**
     * @param                                                  $prefix
     * @param \insolita\multifs\contracts\IFilesystemBuilder   $builder
     *
     * @return $this
     */
    public function buildAndMountFilesystem($prefix, IFilesystemBuilder $builder)
    {
        return $this->mountFilesystem($prefix, $builder->build());
    }
}
