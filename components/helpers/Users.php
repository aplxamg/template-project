<?php

namespace app\components\helpers;

use Yii;

class Users
{
    public function initUser()
    {
        $session = Yii::$app->session;
        $sessionKey = $session['currentSessionKey'];
        if ($session->has($sessionKey)) {
            return (object)$session->get($sessionKey);
        } else {
            return Yii::$app->user->identity;
        }
    }
}

?>
