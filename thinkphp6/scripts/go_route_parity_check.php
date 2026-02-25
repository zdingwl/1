<?php

declare(strict_types=1);

$repoRoot = dirname(__DIR__, 2);
$goFile = $repoRoot . '/api/routes/routes.go';
$tpFile = dirname(__DIR__) . '/route/app.php';

if (!is_file($goFile) || !is_file($tpFile)) {
    fwrite(STDERR, "missing route source files\n");
    exit(1);
}

$go = file_get_contents($goFile);
$tp = file_get_contents($tpFile);
if ($go === false || $tp === false) {
    fwrite(STDERR, "failed to read route files\n");
    exit(1);
}

$groupMap = [
    'dramas' => '/api/v1/dramas',
    'aiConfigs' => '/api/v1/ai-configs',
    'generation' => '/api/v1/generation',
    'characterLibrary' => '/api/v1/character-library',
    'characters' => '/api/v1/characters',
    'props' => '/api/v1/props',
    'upload' => '/api/v1/upload',
    'episodes' => '/api/v1/episodes',
    'tasks' => '/api/v1/tasks',
    'scenes' => '/api/v1/scenes',
    'images' => '/api/v1/images',
    'videos' => '/api/v1/videos',
    'videoMerges' => '/api/v1/video-merges',
    'assets' => '/api/v1/assets',
    'storyboards' => '/api/v1/storyboards',
    'audio' => '/api/v1/audio',
    'settings' => '/api/v1/settings',
];

$goRoutes = [];
preg_match_all('/\b([A-Za-z_][A-Za-z0-9_]*)\.(GET|POST|PUT|DELETE)\("([^"]*)"/', $go, $matches, PREG_SET_ORDER);
foreach ($matches as $m) {
    [$all, $group, $method, $path] = $m;
    if (!isset($groupMap[$group])) {
        continue;
    }
    $full = $groupMap[$group] . $path;
    $goRoutes[] = strtoupper($method) . ' ' . normalizePath($full);
}
$goRoutes[] = 'GET /health';
$goRoutes = array_values(array_unique($goRoutes));
sort($goRoutes);

$tpRoutes = [];
preg_match_all("/Route::(get|post|put|delete|any)\('([^']+)'/", $tp, $tm, PREG_SET_ORDER);
foreach ($tm as $m) {
    $method = strtoupper($m[1]);
    $path = $m[2];
    if ($method === 'ANY') {
        continue;
    }
    $full = '/' . ltrim($path, '/');
    if ($full !== '/health' && !str_starts_with($full, '/api/v1')) {
        $full = '/api/v1' . $full;
    }
    $tpRoutes[] = $method . ' ' . normalizePath($full);
}
$tpRoutes = array_values(array_unique($tpRoutes));
sort($tpRoutes);

$goSet = array_flip($goRoutes);
$tpSet = array_flip($tpRoutes);

$missingInTp = [];
foreach ($goRoutes as $r) {
    if (!isset($tpSet[$r])) {
        $missingInTp[] = $r;
    }
}

$extraInTp = [];
foreach ($tpRoutes as $r) {
    if (!isset($goSet[$r])) {
        $extraInTp[] = $r;
    }
}

if ($missingInTp) {
    fwrite(STDERR, "Go->ThinkPHP route parity failed, missing routes in ThinkPHP:\n");
    foreach ($missingInTp as $r) {
        fwrite(STDERR, " - {$r}\n");
    }
    exit(1);
}

echo "Go route parity passed. Matched " . count($goRoutes) . " Go routes in ThinkPHP.\n";
if ($extraInTp) {
    echo "Note: ThinkPHP has " . count($extraInTp) . " additional routes (intentional extensions), examples:\n";
    foreach (array_slice($extraInTp, 0, 10) as $r) {
        echo " - {$r}\n";
    }
}

function normalizePath(string $path): string
{
    $path = preg_replace('#//+#', '/', $path) ?? $path;
    // Go params /:id ; TP params /:id ; normalize both to /{param}
    $path = preg_replace('#/:([A-Za-z_][A-Za-z0-9_]*)#', '/{param}', $path) ?? $path;
    return $path;
}
