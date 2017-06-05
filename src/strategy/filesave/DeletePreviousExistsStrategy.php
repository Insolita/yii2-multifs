<?php
/**
 * Created by solly [04.06.17 7:57]
 */

namespace insolita\multifs\strategy\filesave;

use insolita\multifs\contracts\IFileObject;
use insolita\multifs\strategy\filename\IFileNameStrategy;
use League\Flysystem\FilesystemInterface;

/**
 * Class DeletePreviousSaveStrategy
 */
class DeletePreviousExistsStrategy implements IFileSaveStrategy
{
    /**
     * @param \League\Flysystem\FilesystemInterface                 $filesystem
     * @param \insolita\multifs\contracts\IFileObject               $fileObject
     * @param \insolita\multifs\strategy\filename\IFileNameStrategy $fileNameStrategy
     * @param                                                       $targetPath
     * @param array                                                 $streamParams
     *
     * @return \League\Flysystem\File|\League\Flysystem\Handler|false
     */
    public function save(
        FilesystemInterface $filesystem,
        IFileObject $fileObject,
        IFileNameStrategy $fileNameStrategy,
        $targetPath,
        $streamParams = []
    ) {
        if ($filesystem->has($targetPath)) {
            $filesystem->delete($targetPath);
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
}
