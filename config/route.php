<?php

return [
    'class' => 'yii\web\UrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => [
        /* Automatic referencing for urls <module>/controller/action format */
        ''
        => '/account/session/login',
        '/<action:(login|logout|dashboard)>'
        => '/account/session/<action>',
        /* Asset Management */
        '/asset'
        => '/asset/manage/index',
        '/asset/add'
        => '/asset/manage/add',

    ]
];
?>
