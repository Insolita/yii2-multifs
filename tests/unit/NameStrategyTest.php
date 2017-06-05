<?php
/**
 * Created by solly [04.06.17 5:08]
 */

namespace tests\unit;

use Carbon\Carbon;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use insolita\multifs\entity\Context;
use insolita\multifs\strategy\filename\AsIsStrategy;
use insolita\multifs\strategy\filename\RandomContextPrefixedStrategy;
use insolita\multifs\strategy\filename\RandomStringStrategy;
use insolita\multifs\strategy\filename\SlugTimeStampedStrategy;

/**
 * Class NameStrategyTest
 * @mixin \tests\unit\TestFsBuildTrait
 */
class NameStrategyTest extends Unit
{
    use TestFsBuildTrait;
    
    /**
     *
     */
    public function testInitial()
    {
        Debug::debug($this->uploadedObject);
        Debug::debug($this->uploadedObject->getPathInfo());
        Debug::debug($this->uploadedObject->getExtension());
        Debug::debug($this->uploadedObject->getExtensionByMimeType());
    }
    
    /**
     *
     */
    public function testAsIsStrategy()
    {
        $strategy = new AsIsStrategy();
        $fileName = $strategy->resolveFileName($this->uploadedObject);
        verify($fileName)->equals('test.txt');
    }
    
    /**
     *
     */
    public function testRandomStringStrategy()
    {
        $strategy = new RandomStringStrategy();
        $fileName = $strategy->resolveFileName($this->uploadedObject);
        verify($fileName)->internalType('string');
        verify(strlen($fileName))->greaterOrEquals(32);
    }
    
    /**
     *
     */
    public function testSlugTimestampedStrategy()
    {
        $strategy = new SlugTimeStampedStrategy();
        $fileName = $strategy->resolveFileName($this->uploadedObject);
        $time = time();
        Carbon::setTestNow($time);
        verify($fileName)->internalType('string');
        verify($fileName)->equals('test_' . $time . '.txt');
    }
    
    /**
     *
     */
    public function testRandomContextPrefixedStrategy()
    {
        $strategy = new RandomContextPrefixedStrategy();
        $identity = new DummyUser();
        $context = new Context('', 'dummy/index', ['id' => 33], $identity);
        $fileName = $strategy->resolveFileName($this->uploadedObject, $context);
        verify($fileName)->contains('dummy');
        verify($fileName)->contains('.txt');
        verify($fileName)->contains('100500');
        
        $context = new Context('', '', ['id' => 33], $identity);
        $fileName = $strategy->resolveFileName($this->uploadedObject, $context);
        verify($fileName)->notContains('dummy');
        verify($fileName)->contains('.txt');
        verify($fileName)->contains('100500');
        
        $context = new Context('', 'dummy/index', ['id' => 33]);
        $fileName = $strategy->resolveFileName($this->uploadedObject, $context);
        verify($fileName)->contains('dummy');
        verify($fileName)->contains('.txt');
        
        $fileName = $strategy->resolveFileName($this->uploadedObject);
        verify($fileName)->contains('.txt');
        verify($fileName)->notContains('dummy');
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
