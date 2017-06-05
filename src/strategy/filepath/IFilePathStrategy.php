<?php
/**
 * Created by solly [04.06.17 3:12]
 */

namespace insolita\multifs\strategy\filepath;

use insolita\multifs\entity\Context;
use insolita\multifs\contracts\IFileObject;
use League\Flysystem\FilesystemInterface;

/**
 * Interface IFilePathStrategy
 */
interface IFilePathStrategy
{
    
    /**
     * @param \League\Flysystem\FilesystemInterface   $filesystem
     * @param \insolita\multifs\contracts\IFileObject $fileObject
     * @param \insolita\multifs\entity\Context|null   $context
     *
     * @return string
     */
    public function resolveFilePath(
        FilesystemInterface $filesystem,
        IFileObject $fileObject,
        Context $context = null
    );
}
