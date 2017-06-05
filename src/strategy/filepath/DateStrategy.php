<?php
/**
 * Created by solly [04.06.17 4:02]
 */

namespace insolita\multifs\strategy\filepath;

use insolita\multifs\contracts\IFileObject;
use insolita\multifs\entity\Context;
use League\Flysystem\FilesystemInterface;

/**
 * Class DateStrategy
 */
class DateStrategy implements IFilePathStrategy
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
    ) {
        $date = explode('-', date('Y-m-d', time()));
        return implode(DIRECTORY_SEPARATOR, $date) . DIRECTORY_SEPARATOR;
    }
}
