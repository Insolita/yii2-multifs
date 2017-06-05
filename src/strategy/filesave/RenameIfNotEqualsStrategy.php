<?php
/**
 * Created by solly [04.06.17 7:51]
 */

namespace insolita\multifs\strategy\filesave;

use insolita\multifs\contracts\IFileObject;
use insolita\multifs\strategy\filename\IFileNameStrategy;
use League\Flysystem\File;
use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Handler;

/**
 * Class RenameIfNotEqualsStrategy
 */
class RenameIfNotEqualsStrategy implements IFileSaveStrategy
{
    /**
     * @var int
     */
    protected $maxTries;
    
    /**
     * RenameIfNotEqualsStrategy constructor.
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
            $targetFile = $filesystem->get($targetPath);
            
            if (!$this->isFileEquals($fileObject, $targetFile)) {
                $targetPath = $this->getRenamedValidPath($filesystem, $fileObject, $fileNameStrategy, $targetPath);
            }
        }
        $stream = fopen($fileObject->getPath(), 'rb+');
        $streamParams['ContentType'] = $fileObject->getMimeType();
        if ($filesystem->putStream($targetPath, $stream, $streamParams)) {
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
    
    /**
     * @param \insolita\multifs\contracts\IFileObject $fileObject
     * @param \League\Flysystem\Handler|File          $targetFile
     * So,its primitive compare, you free for make better
     * @return bool
     */
    protected function isFileEquals(IFileObject $fileObject, Handler $targetFile)
    {
        $equals = true;
        if (!$targetFile->isFile()) {
            $equals = false;
        } elseif ($fileObject->getSize() != $targetFile->getSize()) {
            $equals = false;
        } elseif ($fileObject->getMimeType() != $targetFile->getMimetype()) {
            $equals = false;
        }
        return $equals;
    }
    
}
