<?php
/**
 * Created by solly [05.06.17 13:00]
 */

namespace tests\unit;

use insolita\multifs\entity\Context;

class CustomContext extends Context
{
    private $myParam;
    
    public function __construct($myParam)
    {
        $this->myParam = $myParam;
        parent::__construct('', '', [], null);
    }
    
    public function getMyParam()
    {
        return $this->myParam;
    }
}
