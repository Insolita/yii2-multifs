<?php
/**
 * Created by solly [04.06.17 4:52]
 */

namespace insolita\multifs\strategy\filepath;

use insolita\multifs\contracts\IFileObject;
use insolita\multifs\entity\Context;
use League\Flysystem\FilesystemInterface;

/**
 * Class NameHashStrategy
 */
class NameHashStrategy implements IFilePathStrategy
{
    /**
     * @var int
     */
    protected $depth;
    
    /**
     * NameHashStrategy constructor.
     *
     * @param int $depth
     */
    public function __construct($depth = 2)
    {
        $this->depth = $depth;
    }
    
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
        $hash = md5($fileObject->getTargetFileName());
        $pathParts = [];
        $i = 0;
        while ($i < $this->depth) {
            $pathParts[] = mb_substr($hash, $i * 2, 2);
            $i++;
        }
        return implode(DIRECTORY_SEPARATOR, $pathParts) . DIRECTORY_SEPARATOR;
    }
}
