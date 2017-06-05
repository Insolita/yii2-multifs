<?php
/**
 * Created by solly [04.06.17 7:51]
 */

namespace insolita\multifs\strategy\filesave;

use insolita\multifs\contracts\IFileObject;
use insolita\multifs\strategy\filename\IFileNameStrategy;
use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;

/**
 * Class RenameOnExistsStrategy
 */
class RenameOnExistsStrategy implements IFileSaveStrategy
{
    /**
     * @var int
     */
    protected $maxTries;
    
    /**
     * RenameOnExistsStrategy constructor.
     *
     * @param int $maxTries
     */
    public function __construct($maxTries = 10)
    {
        $this->maxTries = ($maxTries && $maxTries > 0) ? $maxTries : 10000;
    }
    
    /**
     * @param \League\Flysystem\FilesystemInterface                 $filesystem
     * @param \insolita\multifs\contracts\IFileObject               $fileObject
     * @param \insolita\multifs\strategy\filename\IFileNameStrategy $fileNameStrategy
     * @param                                                       $targetPath
     * @param array                                                 $streamParams
     *
     * @return bool|\League\Flysystem\Handler
     * @throws \League\Flysystem\FileExistsException
     */
    public function save(
        FilesystemInterface $filesystem,
        IFileObject $fileObject,
        IFileNameStrategy $fileNameStrategy,
        $targetPath,
        $streamParams = []
    ) {
        if ($filesystem->has($targetPath)) {
            $targetPath = $this->getRenamedValidPath($filesystem, $fileObject, $fileNameStrategy, $targetPath);
        }
        $stream = fopen($fileObject->getPath(), 'rb+');
        $streamParams['ContentType'] = $fileObject->getMimeType();
        if ($filesystem->writeStream($targetPath, $stream, $streamParams)) {
            $result = $filesystem->get($targetPath);
        } else {
            $result = false;
        }
        if (is_resource($stream)) {
            fclose($stream);
        }
        return $result;
    }
    
    /**
     * @param \League\Flysystem\FilesystemInterface                 $filesystem
     * @param \insolita\multifs\contracts\IFileObject               $fileObject
     * @param \insolita\multifs\strategy\filename\IFileNameStrategy $fileNameStrategy
     * @param                                                       $targetPath
     *
     * @return mixed
     * @throws \League\Flysystem\FileExistsException
     */
    protected function getRenamedValidPath(
        FilesystemInterface $filesystem,
        IFileObject $fileObject,
        IFileNameStrategy $fileNameStrategy,
        $targetPath
    ) {
        $try = 0;
        while ($try < $this->maxTries and $filesystem->has($targetPath)) {
            $try++;
            $oldName = $fileObject->getTargetFileName();
            $newName = $fileNameStrategy->resolveFileName($fileObject);
            $targetPath = str_replace($oldName, $newName, $targetPath);
        }
        if ($filesystem->has($targetPath)) {
            throw new FileExistsException($targetPath);
        }
        return $targetPath;
    }
}
