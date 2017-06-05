<?php
/**
 * Created by solly [04.06.17 10:49]
 */

namespace insolita\multifs\actions;

use insolita\multifs\contracts\IUploader;
use insolita\multifs\entity\UploaderErrors;
use insolita\multifs\entity\UploaderResponse;
use yii\base\Action;
use yii\base\DynamicModel;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * Class UploadAction
 */
class UploadAction extends Action
{
    /**
     * @var string
     */
    public $baseUrl;
    
    /**
     * @var string
     */
    public $deleteRoute;
    
    /**
     * @var IUploader $uploader
     */
    public $uploader;
    
    /**
     * @var string|callable
     **/
    public $fsPrefix;
    
    /**
     * @var UploaderResponse
     */
    public $responseMap;
    
    /**
     * @var string
     */
    public $fileparam = 'file';
    
    /**
     * @var bool
     */
    public $multiple = true;
    
    /**
     * @var bool
     */
    public $disableCsrf = true;
    
    /**
     * @var string session key to store list of uploaded files
     */
    public $sessionKey = '_uploadedFiles';
    
    /**
     * @var array
     * @see https://github.com/yiisoft/yii2/blob/master/docs/guide/input-validation.md#ad-hoc-validation-
     */
    public $validationRules;
    
    /**
     * Exclusive afterSave callback;
     *
     * @example
     * 'afterSaveCallback'=>function(AfterSaveUploadEvent $e){
     *     if($e->isSuccess){
     *        $file = $e->savedFile;
     *       Yii::info('New file uploaded to '.$e->fsPrefix.'://'.$file->getPath()
     *                 .' with size '.$file->getSize()
     *                 .' with mimeType '.$file->getMimetype()
     * );
     *     }else{
     *        Yii::warn('Fail save uploaded file to '.$e->fsPrefix.'://'.$e->targetPath)
     *     }
     * }
     * @var callable|null
     */
    public $afterSaveCallback;
    
    /**
     *
     */
    public function init()
    {
        parent::init();
        if (!$this->uploader instanceof IUploader) {
            throw new InvalidConfigException('Uploader must implement IUploader interface');
        }
        
        if (!$this->responseMap) {
            $this->responseMap = \Yii::createObject(UploaderResponse::class);
        }
        \Yii::$app->response->format = $this->responseMap->format;
        $this->fileparam = \Yii::$app->request->get('fileparam', null) ?: 'file';
        if ($this->disableCsrf) {
            \Yii::$app->request->enableCsrfValidation = false;
        }
        if ($this->afterSaveCallback) {
            $this->attachAfterSaveEvent();
        }
    }
    
    /**
     * @return array|mixed
     */
    public function run()
    {
        $result = [];
        $uploadedFiles = $this->getUploadedFiles();
        
        foreach ($uploadedFiles as $uploadedFile) {
            /* @var \yii\web\UploadedFile $uploadedFile */
            $output = [
                $this->responseMap->nameParam     => Html::encode($uploadedFile->name),
                $this->responseMap->mimeTypeParam => $uploadedFile->type,
                $this->responseMap->sizeParam     => $uploadedFile->size,
            ];
            if ($uploadedFile->error === UPLOAD_ERR_OK) {
                $validationModel = DynamicModel::validateData(['file' => $uploadedFile], $this->validationRules);
                if (!$validationModel->hasErrors()) {
                    if ($this->fsPrefix) {
                        $this->fsPrefix = (is_callable($this->fsPrefix))
                            ? call_user_func($this->fsPrefix, $uploadedFile)
                            : (string)$this->fsPrefix;
                        $this->uploader->setFsPrefix($this->fsPrefix);
                    }
                    if ($this->baseUrl) {
                        $this->baseUrl = is_callable($this->baseUrl)
                            ?
                            call_user_func($this->baseUrl, $this->fsPrefix)
                            :
                            \Yii::getAlias($this->baseUrl);
                    }
                    $savedPath = $this->uploader->save($uploadedFile);
                    list($prefix, $path) = explode('://', $savedPath);
                    if ($prefix && $path) {
                        $output[$this->responseMap->baseUrlParam] = $this->baseUrl;
                        $output[$this->responseMap->pathParam] = $path;
                        $output[$this->responseMap->prefixParam] = $prefix;
                        $output[$this->responseMap->urlParam] = $this->baseUrl . '/' . $path;
                        $output[$this->responseMap->deleteUrlParam]
                            = Url::to([$this->deleteRoute, 'path' => $path, 'prefix' => $prefix]);
                        $paths = \Yii::$app->session->get($this->sessionKey, []);
                        $paths[] = $savedPath;
                        \Yii::$app->session->set($this->sessionKey, $paths);
                    } else {
                        $output['error'] = true;
                        $output['errors'] = [];
                    }
                } else {
                    $output['error'] = true;
                    $output['errors'] = $validationModel->errors;
                }
            } else {
                $output['error'] = true;
                $output['errors'] = UploaderErrors::value($uploadedFile->error);
            }
            
            $result['files'][] = $output;
        }
        return $this->multiple ? $result : array_shift($result);
    }
    
    /**
     * @return \yii\web\UploadedFile[]
     */
    protected function getUploadedFiles()
    {
        return UploadedFile::getInstancesByName($this->fileparam);
    }
    
    /**
     *
     */
    protected function attachAfterSaveEvent()
    {
        Event::on(get_class($this->uploader), IUploader::EVENT_AFTER_SAVE, $this->afterSaveCallback);
    }
}
