<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
$file = $baseDir . '/app/controller/Api/V1/TaskController.php';

if (!is_file($file)) {
    fwrite(STDERR, "TaskController not found\n");
    exit(1);
}

$code = file_get_contents($file);
if ($code === false) {
    fwrite(STDERR, "failed to read TaskController\n");
    exit(1);
}

$checks = [
    'formatTask method exists' => '/function\s+formatTask\s*\(/',
    'payload decode present' => '/decodeField\(\$task\[\'payload\'\]/',
    'result decode present' => '/decodeField\(\$task\[\'result\'\]/',
    'task_key conflict guard present' => '/task_key already exists/',
];

$errors = [];
foreach ($checks as $name => $pattern) {
    if (!preg_match($pattern, $code)) {
        $errors[] = $name;
    }
}

if ($errors) {
    fwrite(STDERR, "Task contract check failed:\n");
    foreach ($errors as $err) {
        fwrite(STDERR, " - {$err}\n");
    }
    exit(1);
}

echo "Task contract check passed.\n";
