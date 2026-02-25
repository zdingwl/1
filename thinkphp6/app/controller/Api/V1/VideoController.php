<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\VideoGeneration;
use app\service\SchemaService;
use think\Request;

class VideoController extends BaseApiController
{
    use RequestPayload;

    public function index()
    {
        SchemaService::ensureCoreTables();
        return $this->success(['items' => VideoGeneration::order('id', 'desc')->select()->toArray()]);
    }

    public function create(Request $request)
    {
        SchemaService::ensureCoreTables();
        $payload = $this->payload($request);
        $now = $this->now();
        $item = VideoGeneration::create([
            'image_gen_id' => isset($payload['image_gen_id']) ? (int)$payload['image_gen_id'] : null,
            'prompt' => trim((string)($payload['prompt'] ?? '')),
            'video_url' => '/static/mock/video-' . time() . '.mp4',
            'status' => 'completed',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return $this->success($item->toArray(), 'created', 0, 201);
    }

    public function read(int $id)
    {
        SchemaService::ensureCoreTables();
        $item = VideoGeneration::find($id);
        if (!$item) {
            return $this->error('video generation not found', 404);
        }
        return $this->success($item->toArray());
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();
        $item = VideoGeneration::find($id);
        if (!$item) {
            return $this->error('video generation not found', 404);
        }
        $item->delete();
        return $this->success(['id' => $id], 'deleted');
    }

    public function fromImage(int $imageGenId)
    {
        return $this->success(['image_gen_id' => $imageGenId, 'message' => 'mock video generation queued']);
    }

    public function batch(int $episodeId)
    {
        return $this->success(['episode_id' => $episodeId, 'message' => 'mock batch video generation queued']);
    }
}
