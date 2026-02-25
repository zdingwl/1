<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
$schemaFile = $baseDir . '/app/service/SchemaService.php';
$modelDir = $baseDir . '/app/model';

if (!is_file($schemaFile)) {
    fwrite(STDERR, "schema file not found: {$schemaFile}\n");
    exit(1);
}
if (!is_dir($modelDir)) {
    fwrite(STDERR, "model dir not found: {$modelDir}\n");
    exit(1);
}

$schemaCode = file_get_contents($schemaFile);
if ($schemaCode === false) {
    fwrite(STDERR, "failed to read schema service\n");
    exit(1);
}

preg_match_all('/CREATE TABLE IF NOT EXISTS\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $schemaCode, $schemaMatches);
$schemaTables = array_values(array_unique(array_map('strtolower', $schemaMatches[1] ?? [])));

if (!$schemaTables) {
    fwrite(STDERR, "no tables found in SchemaService\n");
    exit(1);
}

$modelFiles = glob($modelDir . '/*.php') ?: [];
$modelMap = [];
$errors = [];

foreach ($modelFiles as $file) {
    $code = file_get_contents($file);
    if ($code === false) {
        $errors[] = 'failed to read model file: ' . basename($file);
        continue;
    }

    $modelName = basename($file, '.php');

    if (!preg_match('/protected\s+\$name\s*=\s*[\'\"]([^\'\"]+)[\'\"]\s*;/', $code, $m)) {
        $errors[] = 'model ' . $modelName . ' missing protected $name mapping';
        continue;
    }

    $table = strtolower($m[1]);
    $modelMap[$modelName] = $table;

    if (!in_array($table, $schemaTables, true)) {
        $errors[] = "model {$modelName} maps to table '{$table}' not found in SchemaService";
    }
}

$requiredTables = [
    'dramas', 'episodes', 'scenes', 'storyboards',
    'ai_configs', 'tasks', 'characters', 'props',
    'image_generations', 'video_generations', 'assets', 'app_settings',
];

foreach ($requiredTables as $table) {
    if (!in_array($table, $schemaTables, true)) {
        $errors[] = "required table missing in SchemaService: {$table}";
    }
}

if ($errors) {
    fwrite(STDERR, "Schema check failed with " . count($errors) . " error(s):\n");
    foreach ($errors as $err) {
        fwrite(STDERR, " - {$err}\n");
    }
    exit(1);
}

echo "Schema check passed. "
    . count($schemaTables) . " tables in schema, "
    . count($modelMap) . " models verified.\n";
