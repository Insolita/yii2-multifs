<?php
/**
 * Created by solly [28.05.17 6:22]
 */

namespace tests\unit;

use yii\web\IdentityInterface;

class DummyUser implements IdentityInterface
{
    public $id = 100500;
    
    public $name = 'DummyUser';
    
    public function getId()
    {
        return 100500;
    }
    
    public function getAuthKey()
    {
        return 'valid';
    }
    
    public function validateAuthKey($authKey)
    {
        return ($authKey == 'valid');
    }
    
    public static function findIdentity($id)
    {
        if ($id == 100500) {
            return new self(['id' => 100500, 'name' => 'DummyUser']);
        } else {
            return null;
        }
    }
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if ($token == 'valid') {
            return new self(['id' => 100500, 'name' => 'DummyUser']);
        } else {
            return null;
        }
    }
    
}
