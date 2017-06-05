<?php

namespace insolita\multifs\uploader\events;

use yii\base\Event;

/**
 * Class BeforeDeleteUploadEvent
 */
class BeforeDeleteUploadEvent extends Event
{
    /**
     * @var
     */
    public $fsPrefix;
    
    /**
     * @var
     */
    public $path;
}
