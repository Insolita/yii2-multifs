<?php
/**
 * Created by solly [05.06.17 1:13]
 */

namespace insolita\multifs\actions;

use insolita\multifs\contracts\IMultifsManager;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\base\UserException;
use yii\di\Instance;
use yii\web\HttpException;

/**
 * Class ViewAction
 */
class ViewAction extends Action
{
    /**
     * @var string path request param
     */
    public $pathParam = 'path';
    
    public $prefixParam = 'prefix';
    
    /**
     * @var \insolita\multifs\MultiFsManager|\insolita\multifs\contracts\IMultifsManager $multifs
     */
    public $multifs;
    
    /**
     * @var string|callable
    **/
    public $forcePrefix;
    
    /**
     * @var bool
     */
    public $validatePrefix = false;
    /**
     * @var bool
     */
    public $forDownload = false;

    
    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->multifs = Instance::ensure($this->multifs, IMultifsManager::class);
        
        if ($this->forcePrefix) {
            if (is_callable($this->forcePrefix)) {
                $this->forcePrefix = call_user_func($this->forcePrefix);
            }
        }
    }
    
    /**
     * @throws \yii\web\HttpException
     * @return \yii\web\Response
     */
    public function run()
    {
        $path = \Yii::$app->request->get($this->pathParam);
        $prefix = $this->forcePrefix?:\Yii::$app->request->get($this->prefixParam);
        $fullPath = implode('://', [$prefix,$path]);
        if($this->validatePrefix === true){
            if (!in_array($prefix, $this->multifs->listPrefixes())) {
                throw new UserException('wrong prefix param');
            }
        }
        if ($this->multifs->has($fullPath) === false) {
            throw new HttpException(404);
        }
        /**
         * @var \League\Flysystem\File $fileHandler
        **/
        $fileHandler = $this->multifs->get($fullPath);
        if(!$fileHandler->isFile()){
            throw new HttpException(404);
        }
        return \Yii::$app->response->sendStreamAsFile(
            $this->multifs->readStream($fullPath),
            pathinfo($path, PATHINFO_BASENAME),
            [
                'mimeType' => $fileHandler->getMimetype(),
                'inline'   => !$this->forDownload,
            ]
        );
    }
    
}
