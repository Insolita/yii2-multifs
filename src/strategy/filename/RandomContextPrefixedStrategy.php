<?php
/**
 * Created by solly [03.06.17 15:27]
 */

namespace insolita\multifs\strategy\filename;

use insolita\multifs\entity\Context;
use insolita\multifs\contracts\IFileObject;

/**
 * Class RandomContextPrefixedStrategy
 */
class RandomContextPrefixedStrategy implements IFileNameStrategy
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
        if ($context) {
            $prefix = explode('/', $context->getRoute())[0];
            $postfix = $context->getUserIdentity() ? $context->getUserIdentity()->getId() : '';
            $targetName = ($prefix ? $prefix . '_' : '') . $randomString
                . ($postfix ? '_' . $postfix : '') . '.' . $ext;
        } else {
            $targetName = $randomString . '.' . $ext;
        }
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
