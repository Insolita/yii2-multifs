<?php
/**
 * Created by solly [04.06.17 10:49]
 */

namespace insolita\multifs\actions;

use insolita\multifs\contracts\IUploader;
use yii\base\Action;
use yii\base\Event;
use yii\di\Instance;
use yii\web\HttpException;

/**
 * Class DeleteAction
 */
class DeleteAction extends Action
{
    /**
     * @var string path request param
     */
    public $pathParam = 'path';
    
    /**
     * @var string path request param
     */
    public $prefixParam = 'prefix';
    
    /**
     * @var \insolita\multifs\contracts\IUploader $uploader
     */
    public $uploader;
    
    /**
     * @var string|callable
     **/
    public $forcePrefix;
    
    /**
     * @var string session key to store list of uploaded files
     */
    public $sessionKey = '_uploadedFiles';
    
    /**
     * @var string|callable
     * @example
     * 'afterDeleteCallback'=>function(AfterDeleteUploadEvent $e){
     *    if($e->isSuccess){
     *       Yii::info('File successfully removed from '.$e->fsPrefix.'://'.$e->path);
     *    }else{
     *        Yii::warn('Fail file remove from '.$e->fsPrefix.'://'.$e->path);
     *    }
     * }
     */
    public $afterDeleteCallback;
    
    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->uploader = Instance::ensure($this->uploader, IUploader::class);
        if ($this->afterDeleteCallback) {
            $this->attachAfterDeleteEvent();
        }
        if ($this->forcePrefix && is_callable($this->forcePrefix)) {
            $this->forcePrefix = call_user_func($this->forcePrefix);
        }
    }
    
    /**
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function run()
    {
        $path = \Yii::$app->request->get($this->pathParam);
        $prefix = $this->forcePrefix ?: \Yii::$app->request->get($this->prefixParam);
        $fullPath = implode('://', [$prefix, $path]);
        $paths = \Yii::$app->session->get($this->sessionKey, []);
        if (in_array($fullPath, $paths, true)) {
            $this->uploader->setFsPrefix($prefix);
            $success = $this->uploader->delete($path);
            if (!$success) {
                throw new HttpException(400);
            }
            return $success;
        } else {
            throw new HttpException(403);
        }
    }
    
    /**
     *
     */
    protected function attachAfterDeleteEvent()
    {
        Event::on(get_class($this->uploader), IUploader::EVENT_AFTER_DELETE, $this->afterDeleteCallback);
    }
}
