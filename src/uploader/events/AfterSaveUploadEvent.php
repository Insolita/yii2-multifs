<?php

namespace insolita\multifs\uploader\events;

use yii\base\Event;

/**
 * Class AfterSaveUploadEvent
 */
class AfterSaveUploadEvent extends Event
{
    /**
     * @var string
     */
    public $fsPrefix;
    /**
     * @var bool
     */
    public $isSuccess;
    
    /**
     * @var \League\Flysystem\File|false
     */
    public $savedFile;
    
    /**
     * @var string
     */
    public $targetPath;
}
