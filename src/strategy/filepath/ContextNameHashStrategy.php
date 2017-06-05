<?php
/**
 * Created by solly [04.06.17 6:21]
 */

namespace insolita\multifs\strategy\filepath;

use insolita\multifs\contracts\IFileObject;
use insolita\multifs\entity\Context;
use League\Flysystem\FilesystemInterface;

/**
 * Class ContextNameHashStrategy
 */
class ContextNameHashStrategy extends NameHashStrategy
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
        $hashPath = parent::resolveFilePath($filesystem, $fileObject);
        if ($context) {
            $prefix = explode('/', $context->getRoute())[0];
            if ($prefix) {
                $hashPath = $prefix . DIRECTORY_SEPARATOR . $hashPath;
            }
        }
        return $hashPath;
    }
}
