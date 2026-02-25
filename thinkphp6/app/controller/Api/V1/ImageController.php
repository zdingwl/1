<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\ImageGeneration;
use app\service\SchemaService;
use think\Request;

class ImageController extends BaseApiController
{
    use RequestPayload;

    public function index()
    {
        SchemaService::ensureCoreTables();
        return $this->success(['items' => ImageGeneration::order('id', 'desc')->select()->toArray()]);
    }

    public function create(Request $request)
    {
        SchemaService::ensureCoreTables();
        $payload = $this->payload($request);
        $prompt = trim((string)($payload['prompt'] ?? ''));
        if ($prompt === '') {
            return $this->error('prompt is required', 422);
        }
        $now = $this->now();
        $item = ImageGeneration::create([
            'scene_id' => isset($payload['scene_id']) ? (int)$payload['scene_id'] : null,
            'prompt' => $prompt,
            'image_url' => '/static/mock/image-' . time() . '.png',
            'status' => 'completed',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return $this->success($item->toArray(), 'created', 0, 201);
    }

    public function read(int $id)
    {
        SchemaService::ensureCoreTables();
        $item = ImageGeneration::find($id);
        if (!$item) {
            return $this->error('image generation not found', 404);
        }
        return $this->success($item->toArray());
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();
        $item = ImageGeneration::find($id);
        if (!$item) {
            return $this->error('image generation not found', 404);
        }
        $item->delete();
        return $this->success(['id' => $id], 'deleted');
    }

    public function generateByScene(int $sceneId)
    {
        return $this->success(['scene_id' => $sceneId, 'message' => 'mock scene image generation queued']);
    }

    public function upload()
    {
        return $this->success(['message' => 'upload accepted (mock)']);
    }

    public function backgrounds(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'items' => []]);
    }

    public function extractBackgrounds(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'message' => 'mock backgrounds extracted']);
    }

    public function batch(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'message' => 'mock batch generation queued']);
    }
}
