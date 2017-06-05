<?php
/**
 * Created by solly [05.06.17 2:09]
 */

namespace tests\unit;

use insolita\multifs\entity\UploadedFile;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use yii\helpers\FileHelper;

/**
 * Trait TestFsBuildTrait
 */
trait TestFsBuildTrait
{
    /**
     * @var string
     */
    protected $tempDir = '@tests/unit/temp';
    
    /**
     * @var string
     */
    protected $fspath1 = '@tests/unit/fspath1';
    
    /**
     * @var string
     */
    protected $fspath2 =  '@tests/unit/fspath2';
    
    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $fs;
    
    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $fs2;
    
    /**
     * @var \insolita\multifs\contracts\IFileObject
     */
    protected $uploadedObject;
    
    /**
     * @var \insolita\multifs\contracts\IFileObject
     */
    protected $uploadedObject2;
    
    /**
     *
     */
    public function initFileTestEnv()
    {
        $this->tempDir = \Yii::getAlias($this->tempDir);
        $this->fspath1 = \Yii::getAlias($this->fspath1);
        $this->fspath2 = \Yii::getAlias($this->fspath2);
        if (!is_dir($this->tempDir)) {
            FileHelper::createDirectory($this->tempDir);
        }
        if (!is_dir($this->fspath1)) {
            FileHelper::createDirectory($this->fspath1);
        }
        if (!file_exists($this->tempDir . '/test.txt')) {
            file_put_contents($this->tempDir. '/test.txt', 'Hello test');
        }
        if (!file_exists($this->tempDir . '/test2.txt')) {
            file_put_contents($this->tempDir. '/test2.txt', 'I another test file');
        }
        $this->uploadedObject = UploadedFile::create($this->tempDir . '/test.txt');
        $this->uploadedObject2 = UploadedFile::create($this->tempDir . '/test2.txt');
        
        $this->fs = new Filesystem(new Local($this->fspath1));
        $this->fs2 = new Filesystem(new Local($this->fspath2));
        if ($this->fs->has('.dirindex')) {
            $this->fs->delete('.dirindex');
        }
    }
    
    /**
     *
     */
    public function clearFileTestEnv()
    {
        FileHelper::removeDirectory($this->fspath1);
        FileHelper::removeDirectory($this->fspath2);
        FileHelper::removeDirectory($this->tempDir);
    }
}
