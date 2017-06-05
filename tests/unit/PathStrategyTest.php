<?php
/**
 * Created by solly [04.06.17 6:00]
 */

namespace tests\unit;

use Codeception\Test\Unit;
use Codeception\Util\Debug;
use insolita\multifs\entity\Context;
use insolita\multifs\entity\UploadedFile;
use insolita\multifs\strategy\filepath\ContextNameHashStrategy;
use insolita\multifs\strategy\filepath\DateStrategy;
use insolita\multifs\strategy\filepath\DirLimitationStrategy;
use insolita\multifs\strategy\filepath\NameHashStrategy;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use yii\helpers\FileHelper;

class PathStrategyTest extends Unit
{
    use TestFsBuildTrait;
    public function testDirLimitationStrategy()
    {
        $strategy = new DirLimitationStrategy();
        verify($this->fs->has('.dirindex'))->false();
        $path = $strategy->resolveFilePath($this->fs, $this->uploadedObject);
        verify($path)->equals('1/');
        verify($this->fs->has('.dirindex'))->true();
    }
    
    public function testDateStrategy()
    {
        $strategy = new DateStrategy();
        $path = $strategy->resolveFilePath($this->fs, $this->uploadedObject);
        verify($path)->equals(date('Y/m/d/'));
    }
    
    public function testNameHashStrategy()
    {
        $strategy = new NameHashStrategy(1);
        $path = $strategy->resolveFilePath($this->fs, $this->uploadedObject);
        Debug::debug(md5('test.txt'));
        verify($path)->equals('dd/');
        
        $strategy = new NameHashStrategy();
        $path = $strategy->resolveFilePath($this->fs, $this->uploadedObject);
        verify($path)->equals('dd/18/');
    
        $strategy = new NameHashStrategy(3);
        $path = $strategy->resolveFilePath($this->fs, $this->uploadedObject);
        verify($path)->equals('dd/18/bf/');
    }
    
    public function testContextNameHashStrategy()
    {
        $context = new Context('','news/index');
        $strategy = new ContextNameHashStrategy();
        $path = $strategy->resolveFilePath($this->fs, $this->uploadedObject,$context);
        verify($path)->equals('news/dd/18/');

        $strategy = new ContextNameHashStrategy();
        $path = $strategy->resolveFilePath($this->fs, $this->uploadedObject, null);
        verify($path)->equals('dd/18/');
    }
    
    protected function _before()
    {
        $this->initFileTestEnv();
    }
    
    protected function _after()
    {
        $this->clearFileTestEnv();
        parent::_after();
    }
}
