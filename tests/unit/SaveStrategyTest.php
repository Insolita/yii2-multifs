<?php
/**
 * Created by solly [05.06.17 1:33]
 */

namespace tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use insolita\multifs\strategy\filename\AsIsStrategy;
use insolita\multifs\strategy\filename\RandomStringStrategy;
use insolita\multifs\strategy\filesave\DeletePreviousExistsStrategy;
use insolita\multifs\strategy\filesave\ExceptionSaveExistsStrategy;
use insolita\multifs\strategy\filesave\OverwriteExistsStrategy;
use insolita\multifs\strategy\filesave\RenameIfNotEqualsStrategy;
use insolita\multifs\strategy\filesave\RenameOnExistsStrategy;
use League\Flysystem\FileExistsException;
use yii\helpers\FileHelper;

/**
 * Class SaveStrategyTest
 * @mixin \tests\unit\TestFsBuildTrait
 */
class SaveStrategyTest extends Unit
{
    use Specify;
    use TestFsBuildTrait;
    
    /**
     * @var \UnitTester $tester
     **/
    protected $tester;
    
    /**
     *
     */
    public function testInitial()
    {
        Debug::debug(
            [
                'Inintal test env',
                'tempDir' => $this->tempDir,
                'fspath1' => $this->fspath1,
                'fspath2' => $this->fspath2,
            ]
        );
        Debug::debug($this->fs->listContents($this->tempDir));
        Debug::debug(FileHelper::findFiles($this->tempDir));
        Debug::debug($this->fs->listContents());
    }
    
    /**
     *
     */
    public function testDeletePreviousExistsStrategy()
    {
        $this->specify(
            'saveNotExisted',
            function () {
                $strategy = new DeletePreviousExistsStrategy();
                $strategy->save($this->fs, $this->uploadedObject, new AsIsStrategy(), 'test.txt');
                verify($this->fs->has('test.txt'))->true();
                $content = $this->fs->read('test.txt');
                verify($content)->contains('Hello test');
            }
        );
        $this->specify(
            'saveExisted',
            function () {
                file_put_contents($this->uploadedObject->getPath(), 'Another content');
                $strategy = new DeletePreviousExistsStrategy();
                $strategy->save($this->fs, $this->uploadedObject, new AsIsStrategy(), 'test.txt');
                verify($this->fs->has('/test.txt'));
                $content = $this->fs->read('/test.txt');
                verify($content)->notContains('Hello test');
                verify($content)->contains('Another content');
            }
        );
    }
    
    /**
     * @test
     */
    public function testExceptionSaveExistsStrategy()
    {
        $this->specify(
            'saveNotExisted',
            function () {
                $strategy = new ExceptionSaveExistsStrategy();
                $strategy->save($this->fs, $this->uploadedObject, new AsIsStrategy(), 'test.txt');
                verify($this->fs->has('test.txt'))->true();
                $content = $this->fs->read('test.txt');
                verify($content)->contains('Hello test');
            }
        );
        
        $this->specify(
            'saveExisted',
            function () {
                $strategy = new ExceptionSaveExistsStrategy();
                $strategy->save($this->fs, $this->uploadedObject, new AsIsStrategy(), 'test.txt');
            },
            ['throws' => FileExistsException::class]
        );
    }
    
    /**
     * @test
     */
    public function testOverwriteExistsStrategy()
    {
        $this->specify(
            'saveNotExisted',
            function () {
                $strategy = new OverwriteExistsStrategy();
                $strategy->save($this->fs, $this->uploadedObject, new AsIsStrategy(), 'test.txt');
                verify($this->fs->has('test.txt'))->true();
                $content = $this->fs->read('test.txt');
                verify($content)->contains('Hello test');
            }
        );
        $this->specify(
            'saveExisted',
            function () {
                file_put_contents($this->uploadedObject->getPath(), 'Another content');
                $strategy = new OverwriteExistsStrategy();
                $strategy->save($this->fs, $this->uploadedObject, new AsIsStrategy(), 'test.txt');
                verify($this->fs->has('/test.txt'));
                $content = $this->fs->read('/test.txt');
                verify($content)->notContains('Hello test');
                verify($content)->contains('Another content');
            }
        );
    }
    
    /**
     * @incomplete
     */
    public function testRenameOnExistsStrategy()
    {
        $nameStartegy = new RandomStringStrategy();
        $targetName = $nameStartegy->resolveFileName($this->uploadedObject);
        $this->specify(
            'saveNotExisted',
            function () use ($targetName) {
                $strategy = new RenameOnExistsStrategy();
                $resultFile = $strategy->save(
                    $this->fs,
                    $this->uploadedObject,
                    new RandomStringStrategy(),
                    $targetName
                );
                verify($this->fs->has($targetName))->true();
                verify($resultFile->getPath())->contains($targetName);
                Debug::debug($resultFile->getPath());
                Debug::debug($this->fs->listContents());
            }
        );
        $this->specify(
            'saveExisted',
            function () use ($targetName) {
                verify($this->fs->has($targetName))->true();
                $strategy = new RenameOnExistsStrategy();
                $resultFile = $strategy->save(
                    $this->fs,
                    $this->uploadedObject,
                    new RandomStringStrategy(),
                    $targetName
                );
                Debug::debug($resultFile->getPath());
                verify($this->fs->has($targetName))->true();
                verify($resultFile->getPath())->notContains($targetName);
            }
        );
    }
    
    /**
     * @incomplete
     */
    public function testRenameIfNotEqualsStrategy()
    {
        $nameStartegy = new RandomStringStrategy();
        $targetName = $nameStartegy->resolveFileName($this->uploadedObject);
        $this->specify(
            'saveNotExisted',
            function () use ($targetName) {
                $strategy = new RenameIfNotEqualsStrategy();
                $resultFile = $strategy->save(
                    $this->fs,
                    $this->uploadedObject,
                    new RandomStringStrategy(),
                    $targetName
                );
                verify($this->fs->has($targetName))->true();
                verify($resultFile->getPath())->contains($targetName);
                verify($this->fs->listContents())->count(1);
            }
        );
        $this->specify(
            'saveEquals',
            function () use ($targetName) {
                verify($this->fs->has($targetName))->true();
                $strategy = new RenameIfNotEqualsStrategy();
                $resultFile = $strategy->save(
                    $this->fs,
                    $this->uploadedObject,
                    new RandomStringStrategy(),
                    $targetName
                );
                Debug::debug($resultFile->getPath());
                verify($this->fs->has($targetName))->true();
                verify($resultFile->getPath())->contains($targetName);
                verify($this->fs->listContents())->count(1);
            }
        );
        $this->specify(
            'saveEqualsNameButNotEquals',
            function () use ($targetName) {
                verify($this->fs->has($targetName))->true();
                $this->fs->put(
                    $targetName,
                    'Test change content for different file size, '
                    . 'so file has equals name but not equals content'
                );
                $strategy = new RenameIfNotEqualsStrategy();
                $resultFile = $strategy->save(
                    $this->fs,
                    $this->uploadedObject,
                    new RandomStringStrategy(),
                    $targetName
                );
                Debug::debug($resultFile->getPath());
                verify($this->fs->has($targetName))->true();
                verify($resultFile->getPath())->notContains($targetName);
                verify($this->fs->listContents())->count(2);
            }
        );
    }
    
    /**
     *
     */
    protected function _before()
    {
        parent::_before();
        $this->initFileTestEnv();
    }
    
    /**
     *
     */
    protected function _after()
    {
        $this->clearFileTestEnv();
        parent::_after();
        
    }
}
