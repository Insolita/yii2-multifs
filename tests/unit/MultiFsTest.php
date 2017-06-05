<?php
/**
 * Created by solly [05.06.17 1:33]
 */

namespace tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use insolita\multifs\builders\LocalFsBuilder;
use insolita\multifs\MultiFsManager;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemNotFoundException;

/**
 * Class MultiFsTest
 * @mixin \tests\unit\TestFsBuildTrait
 */
class MultiFsTest extends Unit
{
    use Specify;
    use TestFsBuildTrait;
    
    /**
     *
     */
    public function testBuildAndMount()
    {
        $multifs = new MultiFsManager();
        verify($multifs->listPrefixes())->count(0);
        $multifs->buildAndMountFilesystem('temp', new LocalFsBuilder($this->tempDir));
        verify($multifs->listPrefixes())->count(1);
        verify($multifs->hasFilesystem('temp'));
        verify($multifs->has('temp://test.txt'));
    }
    
    /**
     *
     */
    public function testMounts()
    {
        $this->fs->put('fs1file.txt', 'dummy');
        $this->fs2->put('fs2file.txt', 'dummy');
        
        $multifs = new MultiFsManager();
        
        $this->specify(
            'check mounts',
            function () use ($multifs) {
                verify($multifs->listPrefixes())->count(0);
                
                $multifs->mountFilesystem('fspath1', $this->fs);
                $multifs->mountFilesystem('fspath2', $this->fs2);
                
                verify($multifs->listPrefixes())->count(2);
                verify($multifs->hasFilesystem('fspath1'))->true();
                verify($multifs->hasFilesystem('fspath2'))->true();
            }
        );
        
        $this->specify(
            'compare fs access ',
            function () use ($multifs) {
                verify($multifs->has('fspath1://fs1file.txt'))->true();
                verify($multifs->has('fspath2://fs2file.txt'))->true();
                
                verify($multifs->has('fspath2://fs1file.txt'))->false();
                verify($multifs->has('fspath1://fs2file.txt'))->false();
                
                $multifsList = $multifs->listContents('fspath1://');
                $directFsList = $multifs->getFilesystem('fspath1')->listContents();
                
                verify(count($multifsList))->equals(count($directFsList));
                verify($multifsList)->notEquals($directFsList);
                
                verify($multifsList[0])->hasKey('filesystem');
                verify($directFsList[0])->hasntKey('filesystem');
                unset($multifsList[0]['filesystem']);
                
                verify($multifsList)->equals($directFsList);
                
                Debug::debug(
                    [
                        'multifsContents'  => $multifsList,
                        'directfsContents' => $directFsList,
                    ]
                );
                
                $multifs->put('fspath1://some/path/new.txt', 'foobarbaz');
                $dir = $this->fs->get('some');
                verify($dir->isDir())->true();
                verify($this->fs->read('some/path/new.txt'))->contains('foobarbaz');
            }
        );
        
        $this->specify(
            'wrong fs',
            function () use ($multifs) {
                $multifs->getFilesystem('foo');
            },
            ['throws' => FilesystemNotFoundException::class]
        );
        
        $this->specify(
            'wrong fs',
            function () use ($multifs) {
                $unexisted = $multifs->get('foo://file.txt');
            },
            ['throws' => FilesystemNotFoundException::class]
        );
        
        $this->specify(
            'access not own file',
            function () use ($multifs) {
                $unexisted = $multifs->get('fspath1://fs2file.txt');
            },
            ['throws' => FileNotFoundException::class]
        );
    }
    
    /**
     *
     */
    protected function _before()
    {
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
