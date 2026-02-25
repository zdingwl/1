<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\AIConfig;
use app\service\SchemaService;
use think\Request;

class AIConfigController extends BaseApiController
{
    use RequestPayload;

    public function index()
    {
        SchemaService::ensureCoreTables();
        return $this->success(['items' => AIConfig::order('id', 'desc')->select()->toArray()]);
    }

    public function create(Request $request)
    {
        SchemaService::ensureCoreTables();

        $payload = $this->payload($request);
        $name = trim((string) ($payload['name'] ?? ''));
        $provider = trim((string) ($payload['provider'] ?? ''));

        if ($name === '' || $provider === '') {
            return $this->error('name and provider are required', 422);
        }

        $now = $this->now();
        $config = AIConfig::create([
            'name' => $name,
            'provider' => $provider,
            'model' => trim((string) ($payload['model'] ?? '')),
            'endpoint' => trim((string) ($payload['endpoint'] ?? '')),
            'api_key_masked' => $this->maskApiKey((string) ($payload['api_key'] ?? '')),
            'is_enabled' => (int) ($payload['is_enabled'] ?? 1) === 1 ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->success($config->toArray(), 'created', 0, 201);
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

        $payload = $this->payload($request);
        $config->save([
            'name' => trim((string) ($payload['name'] ?? $config['name'])),
            'provider' => trim((string) ($payload['provider'] ?? $config['provider'])),
            'model' => trim((string) ($payload['model'] ?? $config['model'])),
            'endpoint' => trim((string) ($payload['endpoint'] ?? $config['endpoint'])),
            'api_key_masked' => isset($payload['api_key'])
                ? $this->maskApiKey((string) $payload['api_key'])
                : $config['api_key_masked'],
            'is_enabled' => isset($payload['is_enabled']) ? ((int) $payload['is_enabled'] === 1 ? 1 : 0) : $config['is_enabled'],
            'updated_at' => $this->now(),
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
        $payload = $this->payload($request);
        $provider = trim((string) ($payload['provider'] ?? ''));
        $model = trim((string) ($payload['model'] ?? ''));

        if ($provider === '') {
            return $this->error('provider is required', 422);
        }

        return $this->success([
            'provider' => $provider,
            'model' => $model,
            'reachable' => true,
            'latency_ms' => 35,
            'note' => 'mock check passed, replace with real provider SDK integration',
        ]);
    }

    private function maskApiKey(string $apiKey): string
    {
        $apiKey = trim($apiKey);
        if ($apiKey === '') {
            return '';
        }
        if (strlen($apiKey) <= 8) {
            return str_repeat('*', strlen($apiKey));
        }
        return substr($apiKey, 0, 4) . str_repeat('*', max(1, strlen($apiKey) - 8)) . substr($apiKey, -4);
    }
}
