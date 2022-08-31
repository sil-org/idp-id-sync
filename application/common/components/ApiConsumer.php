<?php

namespace Sil\Idp\IdSync\common\components;

use yii\base\Component;
use yii\web\IdentityInterface;
use Yii;

class ApiConsumer extends Component implements IdentityInterface
{
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (self::isValidToken($token)) {
            return new ApiConsumer();
        }

        return null;
    }

    public static function findIdentity($id)
    {
        // since this app is a stateless RESTful app, this is not applicable
        return null;
    }

    public function getId()
    {
        // since this app is a stateless RESTful app, this is not applicable
        return null;
    }

    public function getAuthKey()
    {
        // since this app is a stateless RESTful app, this is not applicable (no cookies)
        return null;
    }

    protected static function isValidToken($token)
    {
        $validTokensString = Yii::$app->params['idSyncAccessTokens'];
        if ($validTokensString !== null) {
            $validTokens = explode(',', $validTokensString);
            return in_array($token, $validTokens, true);
        }
        return false;
    }

    public function validateAuthKey($authKey)
    {
        // since this app is a stateless RESTful app, this is not applicable (no cookies)
        return false;
    }
}
