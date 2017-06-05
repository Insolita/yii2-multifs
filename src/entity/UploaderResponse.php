<?php
/**
 * Created by solly [04.06.17 10:59]
 */

namespace insolita\multifs\entity;

use yii\base\Object;
use yii\web\Response;

/**
 * Class UploaderResponse
 */
class UploaderResponse extends Object
{
    /**
     * @var string
     */
    public $format = Response::FORMAT_JSON;
    
    /**
     * @var string
     */
    public $pathParam = 'path';
    
    /**
     * @var string
     */
    public $baseUrlParam = 'base_url';
    
    /**
     * @var string
     */
    public $urlParam = 'url';
    
    /**
     * @var string
     */
    public $deleteUrlParam = 'delete_url';
    
    /**
     * @var string
     */
    public $mimeTypeParam = 'type';
    
    /**
     * @var string
     */
    public $nameParam = 'name';
    
    /**
     * @var string
     */
    public $sizeParam = 'size';
    
    /**
     * @var string
     */
    public $prefixParam = 'prefix';
}
