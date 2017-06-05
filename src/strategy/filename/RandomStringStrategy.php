<?php
/**
 * Created by solly [03.06.17 15:14]
 */

namespace insolita\multifs\strategy\filename;

use insolita\multifs\contracts\IFileObject;
use insolita\multifs\entity\Context;

/**
 * Class RandomStringStrategy
 */
class RandomStringStrategy implements IFileNameStrategy
{
    
    /**
     * @param \insolita\multifs\contracts\IFileObject $file
     * @param \insolita\multifs\entity\Context|null   $context
     *
     * @return string
     */
    public function resolveFileName(IFileObject $file, Context $context = null)
    {
        $ext = $file->getExtension() ?: $file->getExtensionByMimeType();
        $randomString = $this->generateRandomString();
        $targetName = $randomString . '.' . $ext;
        $file->setTargetFileName($targetName);
        return $targetName;
    }
    
    /**
     * @return string
     */
    protected function generateRandomString()
    {
        return \Yii::$app->security->generateRandomString();
    }
}
