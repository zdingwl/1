<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Prop;
use app\service\SchemaService;
use think\Request;

class PropController extends BaseApiController
{
    use RequestPayload;

    public function create(Request $request)
    {
        SchemaService::ensureCoreTables();
        $payload = $this->payload($request);
        $name = trim((string)($payload['name'] ?? ''));
        if ($name === '') {
            return $this->error('name is required', 422);
        }

        $now = $this->now();
        $prop = Prop::create([
            'drama_id' => isset($payload['drama_id']) ? (int)$payload['drama_id'] : null,
            'name' => $name,
            'description' => trim((string)($payload['description'] ?? '')),
            'image_url' => trim((string)($payload['image_url'] ?? '')),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->success($prop->toArray(), 'created', 0, 201);
    }

    public function update(Request $request, int $id)
    {
        SchemaService::ensureCoreTables();
        $prop = Prop::find($id);
        if (!$prop) {
            return $this->error('prop not found', 404);
        }
        $payload = $this->payload($request);
        $prop->save([
            'name' => trim((string)($payload['name'] ?? $prop['name'])),
            'description' => trim((string)($payload['description'] ?? $prop['description'])),
            'image_url' => trim((string)($payload['image_url'] ?? $prop['image_url'])),
            'updated_at' => $this->now(),
        ]);
        return $this->success($prop->refresh()->toArray(), 'updated');
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();
        $prop = Prop::find($id);
        if (!$prop) {
            return $this->error('prop not found', 404);
        }
        $prop->delete();
        return $this->success(['id' => $id], 'deleted');
    }

    public function generate(int $id)
    {
        return $this->success(['id' => $id, 'image_url' => '/static/mock/prop-' . $id . '.png']);
    }

    public function listByDrama(int $dramaId)
    {
        SchemaService::ensureCoreTables();
        return $this->success(['items' => Prop::where('drama_id', $dramaId)->order('id', 'desc')->select()->toArray()]);
    }

    public function extract(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'message' => 'mock prop extraction done']);
    }

    public function associate(int $id, Request $request)
    {
        return $this->success(['storyboard_id' => $id, 'prop_ids' => (array)$request->post('prop_ids', [])], 'associated');
    }
}
