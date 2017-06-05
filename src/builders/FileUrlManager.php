<?php
/**
 * Created by solly [04.06.17 10:43]
 */

namespace insolita\multifs\builders;

use insolita\multifs\contracts\IFileUrlManager;

/**
 * Class FileUrlManager
 */
class FileUrlManager implements IFileUrlManager
{
    /**
     * @var bool|string
     */
    private $storageUrl;
    
    /**
     * FileUrlManager constructor.
     *
     * @param $storageUrl
     */
    public function __construct($storageUrl)
    {
        $this->storageUrl = \Yii::getAlias($storageUrl);
    }
    
    /**
     * @param      $path
     * @param bool $fsPrefix
     *
     * @return string
     */
    public function getFileUrl($path, $fsPrefix = false)
    {
        if ($fsPrefix) {
            $path = implode(DIRECTORY_SEPARATOR, [$this->storageUrl, $fsPrefix, $path]);
        } else {
            $path = implode(DIRECTORY_SEPARATOR, [$this->storageUrl, $path]);
        }
        return $path;
    }
}
