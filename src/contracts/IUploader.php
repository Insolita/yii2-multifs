<?php
/**
 * Created by solly [04.06.17 3:07]
 */

namespace insolita\multifs\contracts;

use insolita\multifs\strategy\filename\IFileNameStrategy;
use insolita\multifs\strategy\filepath\IFilePathStrategy;
use insolita\multifs\strategy\filesave\IFileSaveStrategy;

interface IUploader
{
    const EVENT_BEFORE_SAVE = 'beforeSave';
    const EVENT_AFTER_SAVE = 'afterSave';
    const EVENT_BEFORE_DELETE = 'beforeDelete';
    const EVENT_AFTER_DELETE = 'afterDelete';
    
    /**
     * @param $prefix
     *
     * @throws \League\Flysystem\FilesystemNotFoundException
     * @return $this
     */
    public function setFsPrefix($prefix);
    
    /**
     * @param IFileNameStrategy $fileNameStrategy
     *
     * @return $this
     */
    public function setFileNameStrategy(IFileNameStrategy $fileNameStrategy);
    
    /**
     * @param IFilePathStrategy $filePathStrategy
     *
     * @return $this
     */
    public function setFilePathStrategy(IFilePathStrategy $filePathStrategy);
    
    /**
     * @param IFileSaveStrategy $filePathStrategy
     *
     * @return $this
     */
    public function setFileSaveStrategy(IFileSaveStrategy $fileSaveStrategy);
    
    /**
     * @param \insolita\multifs\contracts\IContextBuilder $contextBuilder
     *
     * @return $this
     */
    public function setContextBuilder(IContextBuilder $contextBuilder);
    
    /**
     * @param       $file
     * @param array $streamParams
     *
     * @return bool|string
     */
    public function save($file, $streamParams = []);
    
    /**
     * @param $path
     *
     * @return bool
     */
    public function delete($path);
    
    /**
     * @var array|\yii\web\UploadedFile[] $files
     * @var array                         $streamParams
     * @return array
     */
    public function saveAll($files, $streamParams = []);
    
    /**
     * @param array $files
     *
     * @return bool
     */
    public function deleteAll($files);
}
