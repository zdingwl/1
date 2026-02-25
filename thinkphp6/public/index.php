<?php

declare(strict_types=1);

use think\App;

define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

require BASE_PATH . 'vendor/autoload.php';
require BASE_PATH . 'vendor/topthink/framework/src/helper.php';

$app = new App();
$app->http->run()->send();
$app->http->end();
