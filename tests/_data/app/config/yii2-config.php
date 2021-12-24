<?php
declare(strict_types=1);

use yii\db\Connection;
use yii\web\Application;

return [
    'class' => Application::class,
    'id' => 'yii2-maintenance-test',
    'basePath' => dirname(dirname(__DIR__)),
    'components' => [
        'cache' => [
            
        ],
    ]
];
