<?php
/**
 * Created by solly [04.06.17 3:46]
 */

namespace insolita\multifs\strategy\filepath;

use insolita\multifs\entity\Context;
use insolita\multifs\contracts\IFileObject;
use League\Flysystem\FilesystemInterface;

/**
 * Class DirLimitationStrategy
 *
 * @package insolita\strategy\filepath
 */
class DirLimitationStrategy implements IFilePathStrategy
{
    /**
     * @var int
     */
    private $dirIndex = 1;
    
    /**
     * @var string
     */
    private $dirIndexName = '.dirindex';
    
    /**
     * @var int
     */
    private $maxDirFiles;
    
    /**
     * DirLimitationStrategy constructor.
     *
     * @param int $maxDirFiles
     */
    public function __construct($maxDirFiles = 65535)
    {
        $this->maxDirFiles = $maxDirFiles;
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
    )
    {
        $dirIndex = $this->getDirIndex($filesystem);
        return $dirIndex . DIRECTORY_SEPARATOR;
    }
    
    /**
     * @param \League\Flysystem\FilesystemInterface $filesystem
     *
     * @return false|int|string
     */
    protected function getDirIndex(FilesystemInterface $filesystem)
    {
        if (!$filesystem->has($this->dirIndexName)) {
            $filesystem->write($this->dirIndexName, (string)$this->dirIndex);
        } else {
            $this->dirIndex = $filesystem->read($this->dirIndexName);
            if ($this->maxDirFiles !== -1) {
                $filesCount = count($filesystem->listContents($this->dirIndex));
                if ($filesCount > $this->maxDirFiles) {
                    $this->dirIndex++;
                    $filesystem->put($this->dirIndexName, (string)$this->dirIndex);
                }
            }
        }
        return $this->dirIndex;
    }
    
}
