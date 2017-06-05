<?php

namespace insolita\multifs\uploader\events;

use yii\base\Event;

/**
 * Class BeforeSaveUploadEvent
 */
class BeforeSaveUploadEvent extends Event
{
    /**
     * @var string
     */
    public $fsPrefix;
    
    /**
     * @var string
     */
    public $targetPath;
    
    /**
     * @var \insolita\multifs\contracts\IFileObject
     */
    public $uploadedFile;
}
