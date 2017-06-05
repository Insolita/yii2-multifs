<?php
/**
 * Created by solly [04.06.17 9:22]
 */

namespace insolita\multifs;

use insolita\multifs\contracts\IFileObject;
use insolita\multifs\contracts\IUploader;
use insolita\multifs\entity\UploadedFile;
use insolita\multifs\uploader\events\AfterDeleteUploadEvent;
use insolita\multifs\uploader\events\AfterSaveUploadEvent;
use insolita\multifs\uploader\BaseUploader;
use insolita\multifs\uploader\events\BeforeDeleteUploadEvent;
use insolita\multifs\uploader\events\BeforeSaveUploadEvent;
use League\Flysystem\File;
use yii\base\Event;

/**
 * Class Uploader
 */
class Uploader extends BaseUploader
{
    /**
     * @param       $file
     * @param array $streamParams
     *
     * @return false|string
     */
    public function save($file, $streamParams = [])
    {
        $fileObject = UploadedFile::create($file);
        $targetName = $this->getFileNameStrategy()->resolveFileName($fileObject, $this->getContext());
        $targetPath = $this->getFilePathStrategy()->resolveFilePath($this->currentFs, $fileObject, $this->getContext());
        $targetPath.=$targetName;
        $this->fireBeforeSaveEvent($fileObject,$targetPath);
        $result = $this->getFileSaveStrategy()->save($this->currentFs, $fileObject, $this->getFileNameStrategy(),
                                                     $targetPath);
        if($result instanceof File){
            $isSuccess = true;
            $targetPath = $result->getPath();
        }else{
            $isSuccess = false;
        }
        $this->fireAfterSaveEvent($isSuccess,$result, $targetPath);
        return $result?$this->fsPrefix.'://'.$result->getPath():$result;
    }
    
    /**
     * @param $path
     *
     * @return bool
     */
    public function delete($path)
    {
        if ($this->currentFs->has($path)) {
            $this->fireBeforeDeleteEvent($path);
            $isSuccess = $this->currentFs->delete($path);
            $this->fireAfterDeleteEvent($path, $isSuccess);
        }else{
            $isSuccess = false;
        }
        return $isSuccess;
    }
    
    /**
     * @param \insolita\multifs\contracts\IFileObject $fileObject
     * @param                                         $targetPath
     */
    protected function fireBeforeSaveEvent(IFileObject $fileObject, $targetPath)
    {
        $event = \Yii::createObject(['class'=>BeforeSaveUploadEvent::class,
                                     'fsPrefix' => $this->fsPrefix,
                                     'uploadedFile' => $fileObject,
                                     'targetPath' => $targetPath
                                    ]);
        Event::trigger(get_class($this),IUploader::EVENT_BEFORE_SAVE, $event);
    }
    
    /**
     * @param string                                  $targetPath
     * @param bool                                    $isSuccess
     * @param File|false                              $result
     */
    protected function fireAfterSaveEvent($isSuccess,$result, $targetPath)
    {
        $event = \Yii::createObject(['class'=>AfterSaveUploadEvent::class,
                                     'fsPrefix' => $this->fsPrefix,
                                     'isSuccess' => $isSuccess,
                                     'savedFile' => $result,
                                     'targetPath' => $targetPath,
                                    ]);
        Event::trigger(get_class($this),self::EVENT_AFTER_SAVE, $event);
    }
    
    /**
     * @param $path
     */
    protected function fireBeforeDeleteEvent($path)
    {
        $event = \Yii::createObject(['class'=>BeforeDeleteUploadEvent::class,
                                     'fsPrefix' => $this->fsPrefix,
                                     'path' => $path
                                    ]);
        Event::trigger(get_class($this),self::EVENT_BEFORE_DELETE, $event);
    }
    
    /**
     * @param $path
     * @param $isSuccess
     */
    protected function fireAfterDeleteEvent($path, $isSuccess)
    {
        $event = \Yii::createObject(['class'=>AfterDeleteUploadEvent::class,
                                     'fsPrefix' => $this->fsPrefix,
                                     'path' => $path,
                                     'isSuccess' => $isSuccess
                                    ]);
        Event::trigger(get_class($this),self::EVENT_AFTER_DELETE, $event);
    }
    
    
}
