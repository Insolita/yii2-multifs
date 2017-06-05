<?php
/**
 * Created by solly [04.06.17 4:23]
 */

namespace insolita\multifs\entity;

use yii\web\IdentityInterface;

/**
 * Class Context
 *
 * @package insolita\entity
 */
class Context
{
    /**
     * @var string
     */
    private $homeUrl;
    
    /**
     * @var string
     */
    private $route = '';
    
    /**
     * @var array
     */
    private $params = [];
    
    /**
     * @var \yii\web\IdentityInterface
     */
    private $userIdentity;
    
    /**
     * Context constructor.
     *
     * @param string                          $homeUrl
     * @param \yii\web\IdentityInterface|null $userIdentity
     * @param                                 $route
     * @param array                           $params
     */
    public function __construct($homeUrl, $route, $params = [], IdentityInterface $userIdentity = null)
    {
        $this->homeUrl = $homeUrl;
        $this->userIdentity = $userIdentity;
        $this->route = $route;
        $this->params = $params;
    }
    
    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }
    
    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * @return \yii\web\IdentityInterface|null
     */
    public function getUserIdentity()
    {
        return $this->userIdentity;
    }
    
    /**
     * @return string
     */
    public function getHomeUrl()
    {
        return $this->homeUrl;
    }
}
