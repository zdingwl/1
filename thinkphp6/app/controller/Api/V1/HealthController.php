<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\service\SchemaService;
use Throwable;
use think\facade\Db;

class HealthController extends BaseApiController
{
    public function index()
    {
        $checks = [
            'database' => [
                'ok' => false,
                'error' => null,
                'sqlite_version' => null,
                'table_count' => 0,
            ],
            'schema' => [
                'ok' => false,
                'error' => null,
            ],
        ];

        try {
            $versionRow = Db::query('SELECT sqlite_version() AS version');
            $tableRow = Db::query("SELECT COUNT(*) AS count FROM sqlite_master WHERE type='table'");

            $checks['database']['ok'] = true;
            $checks['database']['sqlite_version'] = (string)($versionRow[0]['version'] ?? 'unknown');
            $checks['database']['table_count'] = (int)($tableRow[0]['count'] ?? 0);
        } catch (Throwable $e) {
            $checks['database']['error'] = $e->getMessage();
        }

        try {
            SchemaService::ensureCoreTables();
            $checks['schema']['ok'] = true;
        } catch (Throwable $e) {
            $checks['schema']['error'] = $e->getMessage();
        }

        $healthy = $checks['database']['ok'] && $checks['schema']['ok'];

        return $this->success([
            'status' => $healthy ? 'ok' : 'degraded',
            'app' => config('app.app_name', 'Huobao Drama API'),
            'version' => config('app.app_version', '1.0.0-thinkphp6'),
            'timestamp' => $this->now(),
            'checks' => $checks,
        ], $healthy ? 'ok' : 'degraded', 0, $healthy ? 200 : 503);
    }
}
