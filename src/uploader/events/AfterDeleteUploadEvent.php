<?php

namespace insolita\multifs\uploader\events;

use yii\base\Event;

/**
 * Class AfterDeleteUploadEvent
 */
class AfterDeleteUploadEvent extends Event
{
    /**
     * @var
     */
    public $fsPrefix;
    
    /**
     * @var
     */
    public $path;
    
    /**
     * @var
     */
    public $isSuccess;
}
