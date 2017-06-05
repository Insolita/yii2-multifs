<?php
/**
 * Created by solly [04.06.17 7:51]
 */

namespace insolita\multifs\strategy\filesave;

use insolita\multifs\contracts\IFileObject;
use insolita\multifs\strategy\filename\IFileNameStrategy;
use League\Flysystem\FilesystemInterface;

/**
 * Class OverwriteExistsStrategy
 */
class OverwriteExistsStrategy implements IFileSaveStrategy
{
    /**
     * @param \League\Flysystem\FilesystemInterface                 $filesystem
     * @param \insolita\multifs\contracts\IFileObject               $fileObject
     * @param \insolita\multifs\strategy\filename\IFileNameStrategy $fileNameStrategy
     * @param                                                       $targetPath
     * @param array                                                 $streamParams
     *
     * @return bool|\League\Flysystem\Handler
     */
    public function save(
        FilesystemInterface $filesystem,
        IFileObject $fileObject,
        IFileNameStrategy $fileNameStrategy,
        $targetPath,
        $streamParams = []
    ) {
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
}
