<?php
/**
 * Created by solly [04.06.17 10:41]
 */

namespace insolita\multifs\contracts;

interface IFileUrlManager
{
    public function getFileUrl($path, $fsPrefix = false);
}
