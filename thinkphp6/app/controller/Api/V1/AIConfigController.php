<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\model\AIConfig;
use app\service\SchemaService;
use think\Request;

class AIConfigController extends BaseApiController
{
    public function index()
    {
        SchemaService::ensureCoreTables();
        return $this->success(['items' => AIConfig::order('id', 'desc')->select()->toArray()]);
    }

    public function create(Request $request)
    {
        SchemaService::ensureCoreTables();
        $payload = $request->post();

        $name = trim((string)($payload['name'] ?? ''));
        $provider = trim((string)($payload['provider'] ?? ''));
        if ($name === '' || $provider === '') {
            return $this->error('name and provider are required', 422);
        }

        $now = date('Y-m-d H:i:s');
        $config = AIConfig::create([
            'name' => $name,
            'provider' => $provider,
            'model' => (string)($payload['model'] ?? ''),
            'endpoint' => (string)($payload['endpoint'] ?? ''),
            'is_enabled' => (int)($payload['is_enabled'] ?? 1),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->success($config->toArray(), 'created');
    }

    public function read(int $id)
    {
        SchemaService::ensureCoreTables();
        $config = AIConfig::find($id);
        if (!$config) {
            return $this->error('config not found', 404);
        }
        return $this->success($config->toArray());
    }

    public function update(Request $request, int $id)
    {
        SchemaService::ensureCoreTables();
        $config = AIConfig::find($id);
        if (!$config) {
            return $this->error('config not found', 404);
        }

        $payload = $request->put();
        $config->save([
            'name' => (string)($payload['name'] ?? $config['name']),
            'provider' => (string)($payload['provider'] ?? $config['provider']),
            'model' => (string)($payload['model'] ?? $config['model']),
            'endpoint' => (string)($payload['endpoint'] ?? $config['endpoint']),
            'is_enabled' => (int)($payload['is_enabled'] ?? $config['is_enabled']),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->success($config->refresh()->toArray(), 'updated');
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();
        $config = AIConfig::find($id);
        if (!$config) {
            return $this->error('config not found', 404);
        }

        $config->delete();
        return $this->success(['id' => $id], 'deleted');
    }

    public function testConnection(Request $request)
    {
        $provider = (string)$request->post('provider', '');
        $model = (string)$request->post('model', '');

        return $this->success([
            'provider' => $provider,
            'model' => $model,
            'reachable' => true,
            'note' => 'mock validation success, please integrate real provider SDK',
        ]);
    }
}
