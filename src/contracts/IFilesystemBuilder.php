<?php
/**
 * Created by solly [03.06.17 17:37]
 */

namespace insolita\multifs\contracts;

/**
 * Interface IFilesystemBuilder
 */
interface IFilesystemBuilder
{
    /**
     * @return \League\Flysystem\Filesystem|\League\Flysystem\FilesystemInterface
     */
    public function build();
}
