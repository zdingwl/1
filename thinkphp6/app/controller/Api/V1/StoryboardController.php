<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Episode;
use app\model\Scene;
use app\model\Storyboard;
use app\service\SchemaService;
use think\Request;

class StoryboardController extends BaseApiController
{
    use RequestPayload;

    public function index(int $episodeId)
    {
        SchemaService::ensureCoreTables();

        if (!Episode::find($episodeId)) {
            return $this->error('episode not found', 404);
        }

        $items = Storyboard::where('episode_id', $episodeId)->order('id', 'asc')->select()->toArray();
        return $this->success(['items' => $items]);
    }

    public function create(Request $request, int $episodeId)
    {
        SchemaService::ensureCoreTables();

        if (!Episode::find($episodeId)) {
            return $this->error('episode not found', 404);
        }

        $payload = $this->payload($request);
        $shotName = trim((string) ($payload['shot_name'] ?? ''));
        if ($shotName === '') {
            return $this->error('shot_name is required', 422);
        }

        $sceneId = isset($payload['scene_id']) ? (int) $payload['scene_id'] : null;
        if ($sceneId !== null && $sceneId <= 0) {
            $sceneId = null;
        }

        if ($sceneId !== null && !Scene::find($sceneId)) {
            return $this->error('scene not found', 404);
        }

        $now = $this->now();
        $storyboard = Storyboard::create([
            'episode_id' => $episodeId,
            'scene_id' => $sceneId,
            'shot_name' => $shotName,
            'description' => trim((string) ($payload['description'] ?? '')),
            'duration_seconds' => max(1, (int) ($payload['duration_seconds'] ?? 3)),
            'frame_type' => trim((string) ($payload['frame_type'] ?? 'keyframe')),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->success($storyboard->toArray(), 'created', 0, 201);
    }

    public function update(Request $request, int $id)
    {
        SchemaService::ensureCoreTables();

        $storyboard = Storyboard::find($id);
        if (!$storyboard) {
            return $this->error('storyboard not found', 404);
        }

        $payload = $this->payload($request);
        $sceneId = isset($payload['scene_id']) ? (int) $payload['scene_id'] : $storyboard['scene_id'];
        if ($sceneId !== null && (int) $sceneId <= 0) {
            $sceneId = null;
        }

        if ($sceneId !== null && !Scene::find((int) $sceneId)) {
            return $this->error('scene not found', 404);
        }

        $storyboard->save([
            'scene_id' => $sceneId,
            'shot_name' => trim((string) ($payload['shot_name'] ?? $storyboard['shot_name'])),
            'description' => trim((string) ($payload['description'] ?? $storyboard['description'])),
            'duration_seconds' => max(1, (int) ($payload['duration_seconds'] ?? $storyboard['duration_seconds'])),
            'frame_type' => trim((string) ($payload['frame_type'] ?? $storyboard['frame_type'])),
            'updated_at' => $this->now(),
        ]);

        return $this->success($storyboard->refresh()->toArray(), 'updated');
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();

        $storyboard = Storyboard::find($id);
        if (!$storyboard) {
            return $this->error('storyboard not found', 404);
        }

        $storyboard->delete();
        return $this->success(['id' => $id], 'deleted');
    }


    public function createStandalone(Request $request)
    {
        $episodeId = (int)$request->post('episode_id', 0);
        if ($episodeId <= 0) {
            return $this->error('episode_id is required', 422);
        }
        return $this->create($request, $episodeId);
    }

    public function framePrompt(int $id)
    {
        return $this->success([
            'storyboard_id' => $id,
            'prompt' => '镜头提示词（mock）: cinematic, dramatic lighting, 4k',
        ]);
    }

    public function framePrompts(int $id)
    {
        return $this->success([
            'storyboard_id' => $id,
            'items' => [
                ['type' => 'start', 'prompt' => '起始帧提示词（mock）'],
                ['type' => 'keyframe', 'prompt' => '关键帧提示词（mock）'],
                ['type' => 'end', 'prompt' => '结束帧提示词（mock）'],
            ],
        ]);
    }

}
