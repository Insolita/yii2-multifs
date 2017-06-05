<?php
/**
 * Created by solly [04.06.17 7:50]
 */

namespace insolita\multifs\strategy\filesave;

use insolita\multifs\contracts\IFileObject;
use insolita\multifs\strategy\filename\IFileNameStrategy;
use League\Flysystem\FilesystemInterface;

/**
 * Interface IFileSaveStrategy
 */
interface IFileSaveStrategy
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
    );
}
