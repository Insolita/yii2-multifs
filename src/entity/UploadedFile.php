<?php
/**
 * Created by solly [03.06.17 15:08]
 */

namespace insolita\multifs\entity;

use insolita\multifs\contracts\IFileObject;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\BaseObject;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use yii\web\UploadedFile as YiiUpload;

/**
 * Class UploadedFile based on Class File from trntv\filekit
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
class UploadedFile extends BaseObject implements IFileObject
{
    /**
     * @var string
     */
    protected $path;
    
    /**
     * @var string
     */
    protected $extension;
    
    /**
     * @var
     */
    protected $size;
    
    /**
     * @var string
     */
    protected $mimeType;
    
    /**
     * @var array
     */
    protected $pathinfo;
    
    /**
     * @var string
     */
    protected $targetFileName;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->path === null) {
            throw new InvalidConfigException();
        }
    }
    
    /**
     * @return string|null
     */
    public function getTargetFileName()
    {
        return $this->targetFileName;
    }
    
    /**
     * @param string $targetFileName
     */
    public function setTargetFileName($targetFileName)
    {
        $this->targetFileName = $targetFileName;
    }
    
    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
    
    /**
     * @return mixed
     */
    public function getSize()
    {
        if (!$this->size) {
            $this->size = filesize($this->path);
        }
        return $this->size;
    }
    
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function getMimeType()
    {
        if (!$this->mimeType) {
            $this->mimeType = FileHelper::getMimeType($this->path);
        }
        return $this->mimeType;
    }
    
    /**
     * @return mixed|null
     */
    public function getExtension()
    {
        if ($this->extension === null) {
            $this->extension = $this->getPathInfo('extension');
        }
        return $this->extension;
    }
    
    /**
     * @param $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }
    
    /**
     * @return mixed
     */
    public function getExtensionByMimeType()
    {
        $extensions = FileHelper::getExtensionsByMimeType($this->getMimeType());
        return array_shift($extensions);
    }
    
    /**
     * @param bool|string $part
     *
     * @return mixed|null
     */
    public function getPathInfo($part = false)
    {
        if ($this->pathinfo === null) {
            $this->pathinfo = pathinfo($this->path);
        }
        if ($part !== false) {
            return array_key_exists($part, $this->pathinfo) ? $this->pathinfo[$part] : null;
        }
        return $this->pathinfo;
    }
    
    /**
     * @param $file string|\yii\web\UploadedFile
     *
     * @return self|object
     * @throws \yii\base\InvalidParamException
     */
    public static function create($file)
    {
        
        if (is_a($file, self::class)) {
            return $file;
        }
        
        // UploadedFile
        if (is_a($file, YiiUpload::class)) {
            if ($file->error) {
                throw new InvalidParamException("File upload error \"{$file->error}\"");
            }
            return \Yii::createObject(
                [
                    'class'     => self::class,
                    'path'      => $file->tempName,
                    'extension' => $file->getExtension(),
                ]
            );
        } // Path
        else {
            return \Yii::createObject(
                [
                    'class'          => self::class,
                    'path'           => FileHelper::normalizePath($file),
                    'targetFileName' => StringHelper::basename($file),
                ]
            );
        }
    }
    
    /**
     * @param array $files
     *
     * @return self[]
     * @throws \yii\base\InvalidConfigException
     */
    public static function createAll(array $files)
    {
        $result = [];
        foreach ($files as $file) {
            $result[] = self::create($file);
        }
        return $result;
    }
}
