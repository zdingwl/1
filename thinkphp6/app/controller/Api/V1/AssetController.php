<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Asset;
use app\service\SchemaService;
use think\Request;

class AssetController extends BaseApiController
{
    use RequestPayload;

    public function index()
    {
        SchemaService::ensureCoreTables();
        return $this->success(['items' => Asset::order('id', 'desc')->select()->toArray()]);
    }

    public function create(Request $request)
    {
        SchemaService::ensureCoreTables();
        $payload = $this->payload($request);
        $name = trim((string)($payload['name'] ?? ''));
        $type = trim((string)($payload['type'] ?? ''));
        if ($name === '' || $type === '') {
            return $this->error('name and type are required', 422);
        }

        $now = $this->now();
        $asset = Asset::create([
            'name' => $name,
            'type' => $type,
            'source' => trim((string)($payload['source'] ?? 'manual')),
            'url' => trim((string)($payload['url'] ?? '')),
            'meta' => json_encode($payload['meta'] ?? [], JSON_UNESCAPED_UNICODE),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->success($asset->toArray(), 'created', 0, 201);
    }

    public function read(int $id)
    {
        SchemaService::ensureCoreTables();
        $asset = Asset::find($id);
        if (!$asset) {
            return $this->error('asset not found', 404);
        }
        return $this->success($asset->toArray());
    }

    public function update(Request $request, int $id)
    {
        SchemaService::ensureCoreTables();
        $asset = Asset::find($id);
        if (!$asset) {
            return $this->error('asset not found', 404);
        }
        $payload = $this->payload($request);
        $asset->save([
            'name' => trim((string)($payload['name'] ?? $asset['name'])),
            'type' => trim((string)($payload['type'] ?? $asset['type'])),
            'source' => trim((string)($payload['source'] ?? $asset['source'])),
            'url' => trim((string)($payload['url'] ?? $asset['url'])),
            'meta' => isset($payload['meta']) ? json_encode($payload['meta'], JSON_UNESCAPED_UNICODE) : $asset['meta'],
            'updated_at' => $this->now(),
        ]);

        return $this->success($asset->refresh()->toArray(), 'updated');
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();
        $asset = Asset::find($id);
        if (!$asset) {
            return $this->error('asset not found', 404);
        }
        $asset->delete();
        return $this->success(['id' => $id], 'deleted');
    }

    public function importFromImage(int $imageGenId)
    {
        return $this->success(['image_gen_id' => $imageGenId, 'message' => 'mock imported to assets']);
    }

    public function importFromVideo(int $videoGenId)
    {
        return $this->success(['video_gen_id' => $videoGenId, 'message' => 'mock imported to assets']);
    }
}
