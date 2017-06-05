<?php
/**
 * Created by solly [04.06.17 3:07]
 */

namespace insolita\multifs\uploader;

use insolita\multifs\builders\ContextBuilder;
use insolita\multifs\contracts\IContextBuilder;
use insolita\multifs\contracts\IFileObject;
use insolita\multifs\contracts\IMultifsManager;
use insolita\multifs\contracts\IUploader;
use insolita\multifs\strategy\filename\IFileNameStrategy;
use insolita\multifs\strategy\filename\RandomStringStrategy;
use insolita\multifs\strategy\filepath\DateStrategy;
use insolita\multifs\strategy\filepath\IFilePathStrategy;
use insolita\multifs\strategy\filesave\IFileSaveStrategy;
use insolita\multifs\strategy\filesave\OverwriteExistsStrategy;
use League\Flysystem\File;

/**
 * Class BaseUploader
 */
abstract class BaseUploader implements IUploader
{
    /**
     * @var IFileNameStrategy
     */
    protected $fileNameStrategy;
    
    /**
     * @var IFilePathStrategy
     */
    protected $filePathStrategy;
    
    /**
     * @var IFileSaveStrategy
     */
    protected $fileSaveStrategy;
    
    /**
     * @var \insolita\multifs\contracts\IMultifsManager
     */
    protected $multifs;
    
    /**
     * @var \insolita\multifs\contracts\IContextBuilder
     */
    protected $contextBuilder;

    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $currentFs;
    
    /**
     * @var string
     */
    protected $fsPrefix;
    
    /**
     * @var \insolita\multifs\entity\Context
     */
    protected $context;
    
    /**
     * BaseUploader constructor.
     *
     * @param \insolita\multifs\contracts\IMultifsManager      $multifs
     * @param string                                           $fsPrefix Default Filesystem prefix
     * @param IFileNameStrategy|null                           $fileNameStrategy
     * @param IFilePathStrategy|null                           $filePathStrategy
     * @param IFileSaveStrategy|null                           $fileSaveStrategy
     * @param \insolita\multifs\contracts\IContextBuilder|null $contextBuilder
     */
    public function __construct(
        IMultifsManager $multifs,
        $fsPrefix,
        IFileNameStrategy $fileNameStrategy = null,
        IFilePathStrategy $filePathStrategy = null,
        IFileSaveStrategy $fileSaveStrategy = null,
        IContextBuilder $contextBuilder = null
    ) {
        $this->multifs = $multifs;
        $this->setFsPrefix($fsPrefix);
        $this->fileNameStrategy = $fileNameStrategy;
        $this->filePathStrategy = $filePathStrategy;
        $this->fileSaveStrategy = $fileSaveStrategy;
        $this->contextBuilder = $contextBuilder;
    }
    
    /**
     * @param $prefix
     *
     * @throws \League\Flysystem\FilesystemNotFoundException
     * @return $this
     */
    public function setFsPrefix($prefix)
    {
        $this->currentFs = $this->multifs->getFilesystem($prefix);
        $this->fsPrefix = $prefix;
        return $this;
    }
    
    /**
     * @var array|\yii\web\UploadedFile[] $files
     * @var array                         $streamParams
     * @return bool|string
     */
    abstract public function save($file, $streamParams = []);
    
    /**
     * @var array|\yii\web\UploadedFile[] $files
     * @var array                         $streamParams
     * @return array
     */
    public function saveAll($files, $streamParams = [])
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $this->save($file, $streamParams);
        }
        return $paths;
    }
    
    /**
     * @param $path
     *
     * @return bool
     */
    abstract public function delete($path);
    
    /**
     * @param \insolita\multifs\contracts\IFileObject $fileObject
     * @param                                         $targetPath
     */
    abstract protected function fireBeforeSaveEvent(IFileObject $fileObject, $targetPath);
    
    /**
     * @param string                                  $targetPath
     * @param bool                                    $isSuccess
     * @param File|false                              $result
     */
    abstract protected function fireAfterSaveEvent($isSuccess,$result, $targetPath);
    
    /**
     * @param string $path
     */
    abstract protected function fireBeforeDeleteEvent($path);
    
    /**
     * @param string $path
     * @param bool $isSuccess
     */
    abstract protected function fireAfterDeleteEvent($path, $isSuccess);
    
    /**
     * @param array $files
     *
     * @return bool
     */
    public function deleteAll($files)
    {
        foreach ($files as $path) {
            $this->delete($path);
        }
    }
    
    /**
     * @return \insolita\multifs\strategy\filename\IFileNameStrategy|\insolita\multifs\strategy\filename\RandomStringStrategy
     */
    protected function getFileNameStrategy()
    {
        if (!$this->fileNameStrategy) {
            $this->fileNameStrategy = new RandomStringStrategy();
        }
        return $this->fileNameStrategy;
    }
    
    /**
     * @param IFileNameStrategy $fileNameStrategy
     *
     * @return $this
     */
    public function setFileNameStrategy(IFileNameStrategy $fileNameStrategy)
    {
        $this->fileNameStrategy = $fileNameStrategy;
        return $this;
    }
    
    /**
     * @return \insolita\multifs\strategy\filepath\DateStrategy|\insolita\multifs\strategy\filepath\IFilePathStrategy
     */
    protected function getFilePathStrategy()
    {
        if (!$this->filePathStrategy) {
            $this->filePathStrategy = new DateStrategy();
        }
        return $this->filePathStrategy;
    }
    
    /**
     * @param IFilePathStrategy $filePathStrategy
     *
     * @return $this
     */
    public function setFilePathStrategy(IFilePathStrategy $filePathStrategy)
    {
        $this->filePathStrategy = $filePathStrategy;
        return $this;
    }
    
    /**
     * @return \insolita\multifs\strategy\filesave\IFileSaveStrategy|\insolita\multifs\strategy\filesave\OverwriteExistsStrategy
     */
    protected function getFileSaveStrategy()
    {
        if (!$this->fileSaveStrategy) {
            $this->fileSaveStrategy = new OverwriteExistsStrategy();
        }
        return $this->fileSaveStrategy;
    }
    
    /**
     * @param IFileSaveStrategy $filePathStrategy
     *
     * @return $this
     */
    public function setFileSaveStrategy(IFileSaveStrategy $fileSaveStrategy)
    {
        $this->fileSaveStrategy = $fileSaveStrategy;
        return $this;
    }
    
    protected function getContext()
    {
        if (!$this->context) {
            
            $this->context = $this->getContextBuilder()->build();
        }
        return $this->context;
    }
    
    protected function getContextBuilder()
    {
        if (!$this->contextBuilder) {
            
            $this->contextBuilder = new ContextBuilder();
        }
        return $this->contextBuilder;
    }
    
    /**
     * @param \insolita\multifs\contracts\IContextBuilder $contextBuilder
     *
     * @return $this
     */
    public function setContextBuilder(IContextBuilder $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
        return $this;
    }
    
}
