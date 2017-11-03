Multifs Manager
===============

 - Provide Multifs Manager based on (League\Flysystem\MountManager) for work with set of filesystems (League\Flysystem\Filesystem);
 - Provide flexible Uploader service with support different strategies for resolve file naming, file path structure, and file saving
 - Provide sample Upload,View and Delete Actions with output compatible with trntv\filekit\widget\Upload

![Status](https://travis-ci.org/Insolita/yii2-multifs.svg?branch=master)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist insolita/yii2-multifs "~1.0.0"
```

or add

```
"insolita/yii2-multifs": "~0.0.1"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :


Define in bootstrap neccessary filesystems
```php
 $avatars = (new \insolita\multifs\builders\LocalFsBuilder('@app/web/uploads/avatars'))->build();
 $covers = (new \insolita\multifs\builders\LocalFsBuilder('@app/web/uploads/covers'))->build();
 $attach = (new \insolita\multifs\builders\LocalFsBuilder('@app/web/uploads/attach'))->build();
 $data = (new \insolita\multifs\builders\LocalFsBuilder('@data'))->build();
```
(Ensure that aliases already defined)

Register in container:
```php
 \insolita\multifs\contracts\IMultifsManager::class                                              => [
                \insolita\multifs\MultiFsManager::class,
                [
                    [
                        'avatars'  => $avatars,
                        'covers'   => $covers,
                        'attach'   => $attach,
                        'internal' => $data,
                    ],
                ],
            ],
            \insolita\multifs\contracts\IUploader::class=>[
                \insolita\multifs\Uploader::class,
                [
                    \yii\di\Instance::of(\insolita\multifs\contracts\IMultifsManager::class),
                    'attach'
                ]
            ],

```

also you can add components aliases

```php

'components'=>[
   'multifs'=>\insolita\multifs\contracts\IMultifsManager::class,
   'uploader'=>\insolita\multifs\contracts\IUploader::class,
]
 ```

  On fly usage

  ```php

      echo Yii::$app->multifs->listPrefixes();
      Yii::$app->multifs->mountFilesystem('special', new Filesystem(new Adapter(...)));
      Yii::$app->multifs->write('special://some/file/path/name.txt','Hello Test');
      $fs = Yii::$app->multifs->getFilesystem('internal');
      Vardumper::dump($fs->listConents('',true));

  ```

  Uploader

  ```php

     $file = \yii\web\UploadedFile::getInstanceByName('file');
     $path = Yii::$app->uploader->setFsPrefix('avatars')
               ->setFileNameStrategy(new insolita\multifs\strategy\filename\AsIsStrategy())
               ->setFilePathStrategy(new insolita\multifs\strategy\filename\NameHashStrategy())
               ->setFileSaveStrategy(new insolita\multifs\strategy\filename\ExceptionSaveExistsStrategy())
               ->save($file);

    \Yii::$app->response->sendStreamAsFile(Yii::$app->multifs->readStream($path),
                                            pathinfo($path, PATHINFO_BASENAME),
                                            [
                                               'mimeType' => $file->getType(),
                                               'inline'=>true
                                            ]);
  ```

 Other documentation will be later; see tests
