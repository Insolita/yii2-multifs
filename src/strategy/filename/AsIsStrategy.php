<?php
/**
 * Created by solly [03.06.17 15:10]
 */

namespace insolita\multifs\strategy\filename;

use insolita\multifs\entity\Context;
use insolita\multifs\contracts\IFileObject;

/**
 * Class AsIsStrategy
 *
 */
class AsIsStrategy implements IFileNameStrategy
{
    
    /**
     * @param \insolita\multifs\contracts\IFileObject $file
     * @param \insolita\multifs\entity\Context|null   $context
     *
     * @return string
     */
    public function resolveFileName(IFileObject $file, Context $context = null)
    {
        $targetName = $file->getPathInfo('basename');
        $file->setTargetFileName($targetName);
        return $targetName;
    }
    
}
