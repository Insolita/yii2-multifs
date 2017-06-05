<?php
/**
 * Created by solly [05.06.17 1:33]
 */

namespace tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Codeception\Util\ReflectionHelper;
use Codeception\Util\Stub;
use insolita\multifs\builders\ContextBuilder;
use insolita\multifs\contracts\IFileObject;
use insolita\multifs\contracts\IUploader;
use insolita\multifs\entity\Context;
use insolita\multifs\MultiFsManager;
use insolita\multifs\strategy\filename\AsIsStrategy;
use insolita\multifs\strategy\filename\RandomStringStrategy;
use insolita\multifs\strategy\filepath\DateStrategy;
use insolita\multifs\strategy\filepath\DirLimitationStrategy;
use insolita\multifs\strategy\filesave\OverwriteExistsStrategy;
use insolita\multifs\Uploader;
use insolita\multifs\uploader\events\AfterDeleteUploadEvent;
use insolita\multifs\uploader\events\AfterSaveUploadEvent;
use insolita\multifs\uploader\events\BeforeDeleteUploadEvent;
use insolita\multifs\uploader\events\BeforeSaveUploadEvent;
use League\Flysystem\File;
use League\Flysystem\Filesystem;
use yii\base\Event;

/**
 * Class UploaderTest
 *
 * @mixin \tests\unit\TestFsBuildTrait
 */
class UploaderTest extends Unit
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
    public function testInitialization()
    {
        $multifs = new MultiFsManager(['fs1' => $this->fs, 'fs2' => $this->fs2]);
        $this->specify(
            'Defaults only init',
            function () use ($multifs) {
                $uploader = new Uploader($multifs, 'fs1');
                
                verify(ReflectionHelper::readPrivateProperty($uploader, 'fileNameStrategy'))->null();
                verify(ReflectionHelper::readPrivateProperty($uploader, 'filePathStrategy'))->null();
                verify(ReflectionHelper::readPrivateProperty($uploader, 'fileSaveStrategy'))->null();
                verify(ReflectionHelper::readPrivateProperty($uploader, 'contextBuilder'))->null();
                verify(ReflectionHelper::readPrivateProperty($uploader, 'context'))->null();
                verify(ReflectionHelper::readPrivateProperty($uploader, 'fsPrefix'))->equals('fs1');
                verify(ReflectionHelper::readPrivateProperty($uploader, 'currentFs'))
                    ->isInstanceOf(Filesystem::class);
                
                ReflectionHelper::invokePrivateMethod($uploader, 'getFileNameStrategy');
                verify(ReflectionHelper::readPrivateProperty($uploader, 'fileNameStrategy'))
                    ->isInstanceOf(RandomStringStrategy::class);
                
                ReflectionHelper::invokePrivateMethod($uploader, 'getFilePathStrategy');
                verify(ReflectionHelper::readPrivateProperty($uploader, 'filePathStrategy'))
                    ->isInstanceOf(DateStrategy::class);
                
                ReflectionHelper::invokePrivateMethod($uploader, 'getFileSaveStrategy');
                verify(ReflectionHelper::readPrivateProperty($uploader, 'fileSaveStrategy'))
                    ->isInstanceOf(OverwriteExistsStrategy::class);
                
                ReflectionHelper::invokePrivateMethod($uploader, 'getContextBuilder');
                verify(ReflectionHelper::readPrivateProperty($uploader, 'contextBuilder'))
                    ->isInstanceOf(ContextBuilder::class);
                
                $contextBuilder = Stub::make(
                    ContextBuilder::class,
                    [
                        'build' => Stub::once(
                            function () {
                                return new Context('', 'dummy/route', [], new DummyUser());
                            }
                        ),
                    ],
                    $this
                );
                
                $uploader->setContextBuilder($contextBuilder);
                
                ReflectionHelper::invokePrivateMethod($uploader, 'getContext');
                
                $context = ReflectionHelper::readPrivateProperty($uploader, 'context');
                verify($context)->isInstanceOf(Context::class);
                verify($context->getRoute())->equals('dummy/route');
            }
        );
        
    }
    
    /**
     *
     */
    public function testSave()
    {
        $multifs = new MultiFsManager(['fs1' => $this->fs, 'fs2' => $this->fs2]);
        $contextBuilder = Stub::make(
            ContextBuilder::class,
            [
                'build' => Stub::once(
                    function () {
                        return new Context('', 'dummy/route', [], new DummyUser());
                    }
                ),
            ],
            $this
        );
        $nameStrategy = Stub::make(
            AsIsStrategy::class,
            [
                'resolveFileName' => Stub::once(
                    function (
                        IFileObject $obj,
                        $context
                    ) {
                        verify($context->getRoute())->equals('dummy/route');
                        $obj->setTargetFileName('resolvedName.dat');
                        return 'resolvedName.dat';
                    }
                ),
            ],
            $this
        );
        $pathStrategy = Stub::make(
            DateStrategy::class,
            [
                'resolveFilePath' => Stub::once(
                    function () {
                        return 'a/b/c/';
                    }
                ),
            ],
            $this
        );
        $uploader = new Uploader($multifs, 'fs1', $nameStrategy, $pathStrategy, null, $contextBuilder);
        $path = $uploader->save($this->uploadedObject);
        verify($path)->internalType('string');
        verify($path)->equals('fs1://a/b/c/resolvedName.dat');
        list($prefix, $relativePath) = explode('://', $path);
        verify($this->fs->has($relativePath));
    }
    
    /**
     *
     */
    public function testSaveAll()
    {
        $multifs = new MultiFsManager(['fs1' => $this->fs, 'fs2' => $this->fs2]);
        $uploader = Stub::construct(
            Uploader::class,
            [$multifs, 'fs1'],
            [
                'save' => Stub::consecutive('fs1://a/b/c/file1.txt', 'fs1://a/b/c/file2.txt'),
            ]
        );
        $result = $uploader->saveAll([$this->uploadedObject, $this->uploadedObject2]);
        verify($result)->count(2);
        verify($result)->contains('fs1://a/b/c/file1.txt');
        verify($result)->contains('fs1://a/b/c/file2.txt');
    }
    
    /**
     *
     */
    public function testDelete()
    {
        $path = '/a/b/deleteMe.txt';
        $multifs = new MultiFsManager(['fs1' => $this->fs, 'fs2' => $this->fs2]);
        $this->fs->put($path, 'qwertyui');
        verify($this->fs->has($path))->true();
        $uploader = new Uploader($multifs, 'fs2');
        $uploader->setFsPrefix('fs1');
        $result = $uploader->delete($path);
        verify($result)->true();
        verify($this->fs->has($path))->false();
    }
    
    /**
     *
     */
    public function testDeleteAll()
    {
        $path1 = '/a/b/deleteMe.txt';
        $path2 = '/c/d/deleteMe.txt';
        $this->fs2->put($path1, 'qwertyui');
        $this->fs2->put($path2, 'qwertyui');
        $multifs = new MultiFsManager(['fs1' => $this->fs, 'fs2' => $this->fs2]);
        $uploader = Stub::construct(
            Uploader::class,
            [$multifs, 'fs2'],
            [
                'save' => Stub::consecutive('a/b/c/file1.txt', 'a/b/c/file2.txt'),
            ]
        );
        $uploader->deleteAll([$path1, $path2]);
        verify($this->fs->has($path1))->false();
        verify($this->fs->has($path2))->false();
    }
    
    /**
     *
     */
    public function testSaveEvents()
    {
        $multifs = new MultiFsManager(['fs1' => $this->fs, 'fs2' => $this->fs2]);
        $contextBuilder = Stub::make(
            ContextBuilder::class,
            [
                'build' => Stub::once(
                    function () {
                        return new Context('', 'dummy/route', [], new DummyUser());
                    }
                ),
            ],
            $this
        );
        $nameStrategy = Stub::make(
            AsIsStrategy::class,
            [
                'resolveFileName' =>
                    function () {
                        return 'resolvedName.txt';
                    }
                ,
            ],
            $this
        );
        $uploader = new Uploader($multifs, 'fs1', $nameStrategy, new DirLimitationStrategy(), null, $contextBuilder);
        $expectedPath = '1/resolvedName.txt';
        
        $beforeSaveTrigger = false;
        $afterSaveTrigger = false;
        $alternativeTrigger = false;
        Event::on(
            get_class($uploader),
            $uploader::EVENT_BEFORE_SAVE,
            function (BeforeSaveUploadEvent $e) use ($expectedPath, &$beforeSaveTrigger) {
                verify($e->fsPrefix)->equals('fs1');
                verify($e->targetPath)->equals($expectedPath);
                verify($e->uploadedFile)->isInstanceOf(IFileObject::class);
                $beforeSaveTrigger = true;
            }
        );
        Event::on(
            IUploader::class,
            IUploader::EVENT_BEFORE_SAVE,
            function (BeforeSaveUploadEvent $e) use (&$alternativeTrigger) {
                Debug::debug(['interface trigger' => $e]);
                $alternativeTrigger = true;
            }
        );
        Event::on(
            get_class($uploader),
            $uploader::EVENT_AFTER_SAVE,
            function (AfterSaveUploadEvent $e) use ($expectedPath, &$afterSaveTrigger) {
                verify($e->fsPrefix)->equals('fs1');
                verify($e->isSuccess)->true();
                verify($e->savedFile)->isInstanceOf(File::class);
                verify($e->savedFile->getPath())->equals($expectedPath);
                $afterSaveTrigger = true;
            }
        );
        $uploader->save($this->uploadedObject);
        verify($beforeSaveTrigger)->true();
        verify($afterSaveTrigger)->true();
        verify($alternativeTrigger)->true();
    }
    
    /**
     *
     */
    public function testDeleteEvents()
    {
        $path = '/a/b/deleteMe.txt';
        $multifs = new MultiFsManager(['fs1' => $this->fs, 'fs2' => $this->fs2]);
        $this->fs->put($path, 'qwertyui');
        verify($this->fs->has($path))->true();
        $uploader = new Uploader($multifs, 'fs1');
        $beforeDeleteTrigger = false;
        $afterDeleteTrigger = false;
        $alternativeTrigger = false;
        Event::on(
            get_class($uploader),
            $uploader::EVENT_BEFORE_DELETE,
            function (BeforeDeleteUploadEvent $e) use ($path, &$beforeDeleteTrigger) {
                verify($e->fsPrefix)->equals('fs1');
                verify($e->path)->equals($path);
                $beforeDeleteTrigger = true;
            }
        );
        Event::on(
            get_class($uploader),
            $uploader::EVENT_AFTER_DELETE,
            function (AfterDeleteUploadEvent $e) use ($path, &$afterDeleteTrigger) {
                verify($e->fsPrefix)->equals('fs1');
                verify($e->isSuccess)->true();
                verify($e->path)->equals($path);
                $afterDeleteTrigger = true;
            }
        );
        Event::on(
            IUploader::class,
            IUploader::EVENT_AFTER_DELETE,
            function (AfterDeleteUploadEvent $e) use ($path, &$alternativeTrigger) {
                $alternativeTrigger = true;
                Debug::debug(['interface trigger' => $e]);
            }
        );
        $uploader->delete($path);
        verify($beforeDeleteTrigger)->true();
        verify($afterDeleteTrigger)->true();
        verify($alternativeTrigger)->true();
    }
    
    /**
     *
     */
    public function testAlternativeContext()
    {
        $this->specify(
            'test with alternative context',
            function () {
                $multifs = new MultiFsManager(['fs1' => $this->fs, 'fs2' => $this->fs2]);
                $contextBuilder = Stub::make(
                    ContextBuilder::class,
                    [
                        'build' => function () {
                            return new CustomContext('fooBar');
                        },
                    ],
                    $this
                );
                $nameStrategy = Stub::make(
                    RandomStringStrategy::class,
                    [
                        'resolveFileName' => Stub::once(
                            function (
                                IFileObject $obj,
                                $context
                            ) {
                                verify($context->getRoute())->equals('');
                                verify($context->getMyParam())->equals('fooBar');
                                return $context->getMyParam() . '.txt';
                            }
                        ),
                    ],
                    $this
                );
                $uploader = new Uploader(
                    $multifs,
                    'fs1',
                    $nameStrategy,
                    new DirLimitationStrategy(),
                    null,
                    $contextBuilder
                );
                $expectedPath = '1/fooBar.txt';
                $path = $uploader->save($this->uploadedObject);
                list($prefix, $path) = explode('://', $path);
                verify($path)->internalType('string');
                verify($path)->equals($expectedPath);
                verify($this->fs->has($path));
            }
        );
        
    }
    
    /**
     *
     */
    protected function _before()
    {
        Event::offAll();
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
