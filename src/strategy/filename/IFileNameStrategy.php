<?php
/**
 * Created by solly [03.06.17 15:05]
 */

namespace insolita\multifs\strategy\filename;

use insolita\multifs\entity\Context;
use insolita\multifs\contracts\IFileObject;

/**
 * Interface IFileNameStrategy
 */
interface IFileNameStrategy
{
    /**
     * @param IFileObject $file
     * @param Context     $context
     *
     * @return string
     */
    public function resolveFileName(IFileObject $file, Context $context=null);
}
