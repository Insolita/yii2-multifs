<?php
/**
 * Created by solly [05.06.17 1:33]
 */

namespace tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Codeception\Util\Stub;
use insolita\multifs\actions\DeleteAction;
use insolita\multifs\actions\UploadAction;
use insolita\multifs\actions\ViewAction;
use insolita\multifs\MultiFsManager;
use insolita\multifs\Uploader;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\Session;
use yii\web\UploadedFile;
use yii\web\UrlManager;

/**
 * Class ActionsTest
 * @mixin \tests\unit\TestFsBuildTrait
 */
class ActionsTest extends Unit
{
    use Specify;
    use TestFsBuildTrait;
    
    /**
     * @var \UnitTester $tester
     **/
    protected $tester;
    
    public function testUploadAction()
    {
        $controller = Stub::make(
            Controller::class,
            [
                'getRoute' => Stub::atLeastOnce(
                    function () {
                        return 'some/route';
                    }
                ),
            ],
            $this
        );
        $urlManager = Stub::make(
            UrlManager::class,
            [
                'createUrl' => function () {
                    return 'some/route';
                },
            ],
            $this
        );
        $session = Stub::make(
            Session::class,
            [
                'get' => Stub::once(
                    function () {
                        return [];
                    }
                ),
                'set' => Stub::once(
                    function ($key, $path) {
                        verify($path)->contains('fs://some/path/file.txt');
                    }
                ),
            ],
            $this
        );
        $request = Stub::make(
            Request::class,
            [
                'get' => Stub::once(
                    function () {
                        return [];
                    }
                ),
            ],
            $this
        );
        \Yii::$app->set('request', $request);
        \Yii::$container->set(Session::class, $session);
        \Yii::$container->set(UrlManager::class, $urlManager);
        \Yii::$app->controller = $controller;
        $uploader = Stub::make(
            Uploader::class,
            [
                'save' => Stub::once(
                    function () {
                        return 'fs://some/path/file.txt';
                    }
                ),
            ],
            $this
        );
        $action = Stub::make(
            UploadAction::class,
            [
                'baseUrl'              => 'http://localhost',
                'deleteRoute'          => '',
                'uploader'             => $uploader,
                'afterSaveCallback'    => function ($e) {
                    Debug::debug($e);
                },
                'attachAfterSaveEvent' => Stub::once(
                    function () {
                    }
                ),
                'validationRules'      => [['file', 'required']],
                'getUploadedFiles'     => [
                    new UploadedFile(
                        [
                            'name'     => 'text.txt',
                            'type'     => 'image/jpeg',
                            'error'    => UPLOAD_ERR_OK,
                            'size'     => '12345',
                            'tempName' => 'text.txt',
                        ]
                    ),
                ],
            ],
            $this
        );
        $action->init();
        $result = $action->run();
        Debug::debug($result);
    }
    public function testUploadActionWithCallback()
    {
        $controller = Stub::make(
            Controller::class,
            [
                'getRoute' => Stub::atLeastOnce(
                    function () {
                        return 'some/route';
                    }
                ),
            ],
            $this
        );
        $urlManager = Stub::make(
            UrlManager::class,
            [
                'createUrl' => function () {
                    return 'some/route';
                },
            ],
            $this
        );
        $session = Stub::make(
            Session::class,
            [
                'get' => Stub::once(
                    function () {
                        return [];
                    }
                ),
                'set' => Stub::once(
                    function ($key, $path) {
                        verify($path)->contains('images://some/path/file.txt');
                    }
                ),
            ],
            $this
        );
        $request = Stub::make(
            Request::class,
            [
                'get' => Stub::once(
                    function () {
                        return [];
                    }
                ),
            ],
            $this
        );
        \Yii::$app->set('request', $request);
        \Yii::$container->set(Session::class, $session);
        \Yii::$container->set(UrlManager::class, $urlManager);
        \Yii::$app->controller = $controller;
        $uploader = Stub::make(
            Uploader::class,
            [
                'setFsPrefix'=>Stub::once(function($prefix){
                    verify($prefix)->equals('images');
                }),
                'save' => Stub::once(
                    function () {
                        return 'images://some/path/file.txt';
                    }
                ),
            ],
            $this
        );
        $action = Stub::make(
            UploadAction::class,
            [
                'fsPrefix'=>function(UploadedFile $file){
                    if(in_array($file->type, ['image/png','image/jpg','image/jpeg'])){
                        return 'images';
                    }else{
                        return 'files';
                    }
                },
                'baseUrl'              => function($prefix){
                    if($prefix === 'images'){
                        return 'http://localhost/images';
                    }else{
                        return 'http://localhost/files';
                    }
                },
                'deleteRoute'          => '',
                'uploader'             => $uploader,
                'afterSaveCallback'    => function ($e) {
                    Debug::debug($e);
                },
                'attachAfterSaveEvent' => Stub::once(
                    function () {
                    }
                ),
                'validationRules'      => [['file', 'required']],
                'getUploadedFiles'     => [
                    new UploadedFile(
                        [
                            'name'     => 'text.txt',
                            'type'     => 'image/jpeg',
                            'error'    => UPLOAD_ERR_OK,
                            'size'     => '12345',
                            'tempName' => 'text.txt',
                        ]
                    ),
                ],
            ],
            $this
        );
        $action->init();
        $result = $action->run();
        verify($result)->hasKey('files');
        $result = reset($result['files']);
        verify($result)->hasKey('path');
        verify($result)->hasKey('base_url');
        verify($result)->hasKey('prefix');
        verify($result['base_url'])->equals('http://localhost/images');
        verify($result['prefix'])->equals('images');
        verify($result['path'])->equals('some/path/file.txt');
        Debug::debug($result);
    }
    public function testDeleteSuccessAction()
    {
        $session = Stub::make(
            Session::class,
            [
                'get' => Stub::once(
                    function () {
                        return ['some://foo/path'];
                    }
                ),
                'set' => Stub::never(),
            ],
            $this
        );
        $request = Stub::make(
            Request::class,
            [
                'get' => Stub::exactly(2,
                    function ($param) {
                        return $param == 'prefix'?'some':'foo/path';
                    }
                ),
            ],
            $this
        );
        \Yii::$app->set('request', $request);
        \Yii::$container->set(Session::class, $session);
        $uploader = Stub::make(
            Uploader::class,
            [
                'setFsPrefix'=>Stub::once(function($prefix){
                    verify($prefix)->equals('some');
                }),
                'delete' => Stub::once(
                    function () {
                        return true;
                    }
                ),
            ],
            $this
        );
        $action = Stub::make(
            DeleteAction::class,
            [
                'uploader'               => $uploader,
                'attachAfterDeleteEvent' => Stub::once(
                    function () {
                    }
                ),
                'afterDeleteCallback'    => function ($e) {
                    Debug::debug($e);
                },
            ],
            $this
        );
        
        $action->init();
        $result = $action->run();
        Debug::debug($result);
    }
    
    public function testDeleteBadPathAction()
    {
        $this->specify(
            'path not in session',
            function () {
                $session = Stub::make(
                    Session::class,
                    [
                        'get' => Stub::once(
                            function () {
                                return ['other/path'];
                            }
                        ),
                        'set' => Stub::never(),
                    ],
                    $this
                );
                $request = Stub::make(
                    Request::class,
                    [
                        'get' => Stub::exactly(2,
                            function ($param) {
                                return $param == 'prefix'?'some':'foo/path';
                            }
                        ),
                    ],
                    $this
                );
                \Yii::$app->set('request', $request);
                \Yii::$container->set(Session::class, $session);
                $uploader = Stub::make(
                    Uploader::class,
                    [
                        'delete' => Stub::never(),
                        'setFsPrefix'=>Stub::once(function($prefix){
                            verify($prefix)->equals('some');
                        }),
                    ],
                    $this
                );
                $action = Stub::make(
                    DeleteAction::class,
                    [
                        'uploader'               => $uploader,
                        'attachAfterDeleteEvent' => Stub::once(
                            function () {
                            }
                        ),
                        'afterDeleteCallback'    => function ($e) {
                            Debug::debug($e);
                        },
                    ],
                    $this
                );
                
                $action->init();
                $action->run();
            },
            ['throws' => HttpException::class]
        );
        
    }
    
    public function testDeleteFailAction()
    {
        $this->specify(
            'delete not success',
            function () {
                $session = Stub::make(
                    Session::class,
                    [
                        'get' => Stub::once(
                            function () {
                                return ['some://foo/path'];
                            }
                        ),
                        'set' => Stub::never(),
                    ],
                    $this
                );
                $request = Stub::make(
                    Request::class,
                    [
                        'get' => Stub::exactly(2,
                            function ($param) {
                                return $param == 'prefix'?'some':'foo/path';
                            }
                        ),
                    ],
                    $this
                );
                \Yii::$app->set('request', $request);
                \Yii::$container->set(Session::class, $session);
                $uploader = Stub::make(
                    Uploader::class,
                    [
                        'setFsPrefix'=>Stub::once(function($prefix){
                            verify($prefix)->equals('some');
                        }),
                        'delete' => Stub::once(
                            function () {
                                return false;
                            }
                        ),
                    ],
                    $this
                );
                $action = Stub::make(
                    DeleteAction::class,
                    [
                        'uploader'               => $uploader,
                        'attachAfterDeleteEvent' => Stub::once(
                            function () {
                            }
                        ),
                        'afterDeleteCallback'    => function ($e) {
                            Debug::debug($e);
                        },
                    ],
                    $this
                );
                
                $action->init();
                $action->run();
            },
            ['throws' => HttpException::class]
        );
        
    }
    
    public function testViewAction()
    {
        $multifs = Stub::make(
            MultiFsManager::class,
            [
                '__call'=>Stub::atLeastOnce(function($method,$path=null){
                    if($method==='has'){
                        verify($path[0])->equals('fs://some/path');
                        return true;
                    }elseif ($method==='get'){
                        verify($path[0])->equals('fs://some/path');
                        return $this->fs->get('test/view.txt');
                    }else{
                        return null;
                    }
                })
            ],
            $this
        );
        $response = Stub::make(Response::class, [
            'sendStreamAsFile'=>function(){return 'streeem';}
        ], $this);
        $request = Stub::make(
            Request::class,
            [
                'get' => Stub::exactly(2,
                    function ($param) {
                      if($param=='prefix'){
                          return 'fs';
                      }elseif ($param == 'path'){
                          return 'some/path';
                      }
                    }
                ),
            ],
            $this
        );
        \Yii::$app->set('request', $request);
        \Yii::$app->set('response', $response);
        $action = Stub::make(
            ViewAction::class,
            [
                'multifs'  => $multifs,
            ],
            $this
        );
        
        $action->init();
        $result = $action->run();
        verify($result)->equals('streeem');
    }
    
    public function testViewBadFileAction()
    {
        $this->specify(
            'not existed path',
            function () {
                $multifs = Stub::make(
                    MultiFsManager::class,
                    [
                        '__call'=>Stub::atLeastOnce(function($method){
                            if($method==='has'){
                                return false;
                            }else{
                                return null;
                            }
                        })
                    ],
                    $this
                );
                $request = Stub::make(
                    Request::class,
                    [
                        'get' => Stub::exactly(2,
                            function ($param) {
                                if($param=='prefix'){
                                    return 'fs';
                                }elseif ($param == 'path'){
                                    return 'some/path';
                                }
                            }
                        ),
                    ],
                    $this
                );
                \Yii::$app->set('request', $request);
                $action = Stub::make(
                    ViewAction::class,
                    [
                        'multifs' => $multifs,
                    ],
                    $this
                );
                
                $action->init();
                $action->run();
            },
            ['throws' => HttpException::class]
        );
        
    }
    
    protected function _before()
    {
        $this->initFileTestEnv();
        $this->fs->put('test/view.txt', '12345679');
    }
    
    protected function _after()
    {
        $this->clearFileTestEnv();
        parent::_after();
    }
}
