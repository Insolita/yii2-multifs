<?php
/**
 * Created by solly [04.06.17 10:41]
 */

namespace insolita\multifs\contracts;

/**
 * Interface IFileUrlManager
 */
interface IFileUrlManager
{
    /**
     * @param      $path
     * @param bool $fsPrefix
     *
     * @return string
     */
    public function getFileUrl($path, $fsPrefix = false);
}
