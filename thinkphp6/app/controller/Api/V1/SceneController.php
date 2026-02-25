<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Episode;
use app\model\Scene;
use app\service\SchemaService;
use think\Request;

class SceneController extends BaseApiController
{
    use RequestPayload;

    public function index(int $episodeId)
    {
        SchemaService::ensureCoreTables();

        if (!Episode::find($episodeId)) {
            return $this->error('episode not found', 404);
        }

        $items = Scene::where('episode_id', $episodeId)->order('sort_order', 'asc')->order('id', 'asc')->select()->toArray();
        return $this->success(['items' => $items]);
    }

    public function create(Request $request, int $episodeId)
    {
        SchemaService::ensureCoreTables();

        if (!Episode::find($episodeId)) {
            return $this->error('episode not found', 404);
        }

        $payload = $this->payload($request);
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return $this->error('title is required', 422);
        }

        $now = $this->now();
        $scene = Scene::create([
            'episode_id' => $episodeId,
            'title' => $title,
            'prompt' => trim((string) ($payload['prompt'] ?? '')),
            'image_url' => trim((string) ($payload['image_url'] ?? '')),
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->success($scene->toArray(), 'created', 0, 201);
    }

    public function update(Request $request, int $id)
    {
        SchemaService::ensureCoreTables();

        $scene = Scene::find($id);
        if (!$scene) {
            return $this->error('scene not found', 404);
        }

        $payload = $this->payload($request);
        $scene->save([
            'title' => trim((string) ($payload['title'] ?? $scene['title'])),
            'prompt' => trim((string) ($payload['prompt'] ?? $scene['prompt'])),
            'image_url' => trim((string) ($payload['image_url'] ?? $scene['image_url'])),
            'sort_order' => (int) ($payload['sort_order'] ?? $scene['sort_order']),
            'updated_at' => $this->now(),
        ]);

        return $this->success($scene->refresh()->toArray(), 'updated');
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();

        $scene = Scene::find($id);
        if (!$scene) {
            return $this->error('scene not found', 404);
        }

        $scene->delete();
        return $this->success(['id' => $id], 'deleted');
    }


    public function createStandalone(Request $request)
    {
        SchemaService::ensureCoreTables();
        $payload = $this->payload($request);
        $episodeId = (int)($payload['episode_id'] ?? 0);
        if ($episodeId <= 0 || !Episode::find($episodeId)) {
            return $this->error('episode not found', 404);
        }
        return $this->create($request, $episodeId);
    }

    public function updatePrompt(Request $request, int $id)
    {
        SchemaService::ensureCoreTables();
        $scene = Scene::find($id);
        if (!$scene) {
            return $this->error('scene not found', 404);
        }
        $payload = $this->payload($request);
        $prompt = trim((string)($payload['prompt'] ?? $scene['prompt']));
        $scene->save(['prompt' => $prompt, 'updated_at' => $this->now()]);
        return $this->success($scene->refresh()->toArray(), 'updated');
    }

    public function generateImage(Request $request)
    {
        $payload = $this->payload($request);
        $sceneId = (int)($payload['scene_id'] ?? 0);
        return $this->success([
            'scene_id' => $sceneId,
            'image_url' => '/static/mock/scene-' . ($sceneId ?: time()) . '.png',
            'message' => 'mock scene image generated',
        ]);
    }

}
