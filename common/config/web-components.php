<?php
return [
    'components' => [
        'session' => [
            'class' => 'yii\web\DbSession',
            // 'db' => 'mydb',  // the application component ID of the DB connection. Defaults to 'db'.
            // 'sessionTable' => 'my_session', // session table name. Defaults to 'session'.
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
    		'showScriptName' => false,
            'suffix' => '/',
    		//'enableStrictParsing' => true,
            //'catchAll' => ['site/maintenance'],  // Put site into maintenance mode
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'view' => [
            'defaultExtension' => 'tpl',
            'renderers' => [
                'tpl' => [
                    'class'         => 'yii\smarty\ViewRenderer',
                    'cachePath'     => '@runtime/Smarty/cache',
                    'options'       => [
                        'caching'       => YII_ENV_PROD,
                        'debugging'     => YII_DEBUG,
                        'force_compile' => YII_ENV_DEV,
            ],
                ],
            ],
        ],
    ],
];
