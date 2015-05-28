<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => require(__DIR__ . '/components.php'),
    'bootstrap' => ['log'],
];
