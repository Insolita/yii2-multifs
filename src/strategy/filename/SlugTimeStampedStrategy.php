<?php
/**
 * Created by solly [03.06.17 15:18]
 */

namespace insolita\multifs\strategy\filename;

use insolita\multifs\entity\Context;
use insolita\multifs\contracts\IFileObject;
use yii\helpers\Inflector;

/**
 * Class SlugTimeStampedStrategy
 *
 */
class SlugTimeStampedStrategy implements IFileNameStrategy
{
    
    /**
     * @param \insolita\multifs\contracts\IFileObject $file
     * @param \insolita\multifs\entity\Context|null   $context
     *
     * @return string
     */
    public function resolveFileName(IFileObject $file, Context $context = null): string
    {
        $ext = $file->getExtension() ?: $file->getExtensionByMimeType();
        $name = $file->getPathInfo('filename');
        $name = $this->slugify($name) . '_' . time();
        $targetName = $name . '.' . $ext;
        $file->setTargetFileName($targetName);
        return $targetName;
    }
    
    /**
     * @param $string
     *
     * @return string
     */
    protected function slugify($string)
    {
        return Inflector::slug($string, '_');
    }
    
}
