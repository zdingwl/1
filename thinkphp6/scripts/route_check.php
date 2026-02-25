<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
$routeFile = $baseDir . '/route/app.php';

if (!is_file($routeFile)) {
    fwrite(STDERR, "route file not found: {$routeFile}\n");
    exit(1);
}

$routeContent = file_get_contents($routeFile);
if ($routeContent === false) {
    fwrite(STDERR, "failed to read route file\n");
    exit(1);
}

preg_match_all("/Route::(?:get|post|put|delete|any)\\([^\n]*'Api\\.V1\\.([A-Za-z0-9_]+)\\/([A-Za-z0-9_]+)'/", $routeContent, $matches, PREG_SET_ORDER);

if (empty($matches)) {
    fwrite(STDERR, "no routes matched for Api.V1 controllers\n");
    exit(1);
}

$errors = [];
$checked = 0;

foreach ($matches as $m) {
    $controllerName = $m[1];
    $method = $m[2];
    $file = $baseDir . '/app/controller/Api/V1/' . $controllerName . '.php';

    if (!is_file($file)) {
        $errors[] = "missing controller file: {$controllerName}.php";
        continue;
    }

    $code = file_get_contents($file);
    if ($code === false) {
        $errors[] = "failed to read controller file: {$controllerName}.php";
        continue;
    }

    if (!preg_match('/function\\s+' . preg_quote($method, '/') . '\\s*\\(/', $code)) {
        $errors[] = "missing method {$controllerName}::{$method}";
        continue;
    }

    $checked++;
}

if ($errors) {
    fwrite(STDERR, "Route check failed with " . count($errors) . " error(s):\n");
    foreach ($errors as $err) {
        fwrite(STDERR, " - {$err}\n");
    }
    exit(1);
}

echo "Route check passed. Verified {$checked} route handlers.\n";
