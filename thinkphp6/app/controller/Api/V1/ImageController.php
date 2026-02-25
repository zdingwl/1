<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Episode;
use app\model\ImageGeneration;
use app\model\Scene;
use app\service\SchemaService;
use app\service\TaskService;
use think\Request;
use think\facade\Db;

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

        $sceneId = isset($payload['scene_id']) ? (int)$payload['scene_id'] : null;
        if ($sceneId !== null && $sceneId <= 0) {
            $sceneId = null;
        }
        if ($sceneId !== null && !Scene::find($sceneId)) {
            return $this->error('scene not found', 404);
        }

        $result = Db::transaction(function () use ($sceneId, $prompt) {
            $now = $this->now();
            $item = ImageGeneration::create([
                'scene_id' => $sceneId,
                'prompt' => $prompt,
                'image_url' => '/static/mock/image-' . time() . '.png',
                'status' => 'completed',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $task = (new TaskService())->create('image.generate', [
                'image_generation_id' => $item['id'],
                'prompt' => $prompt,
            ], 'completed');

            return ['image' => $item->toArray(), 'task' => $task->toArray()];
        });

        return $this->success($result, 'created', 0, 201);
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
        SchemaService::ensureCoreTables();
        $scene = Scene::find($sceneId);
        if (!$scene) {
            return $this->error('scene not found', 404);
        }

        $result = Db::transaction(function () use ($sceneId, $scene) {
            $item = ImageGeneration::create([
                'scene_id' => $sceneId,
                'prompt' => trim((string)$scene['prompt']) !== '' ? (string)$scene['prompt'] : 'auto generated scene image',
                'image_url' => '/static/mock/scene-' . $sceneId . '-' . time() . '.png',
                'status' => 'completed',
                'created_at' => $this->now(),
                'updated_at' => $this->now(),
            ]);

            $task = (new TaskService())->create('image.generate.scene', [
                'scene_id' => $sceneId,
                'image_generation_id' => $item['id'],
            ], 'completed');

            return ['image' => $item->toArray(), 'task' => $task->toArray()];
        });

        return $this->success($result, 'generated');
    }

    public function upload(Request $request)
    {
        $payload = $this->payload($request);
        return $this->success([
            'message' => 'upload accepted (mock)',
            'filename' => (string)($payload['filename'] ?? ('upload-' . time() . '.png')),
            'url' => '/static/mock/upload-' . time() . '.png',
        ]);
    }

    public function backgrounds(int $episodeId)
    {
        SchemaService::ensureCoreTables();
        if (!Episode::find($episodeId)) {
            return $this->error('episode not found', 404);
        }

        $sceneIds = Scene::where('episode_id', $episodeId)->column('id');
        if (!$sceneIds) {
            return $this->success(['episode_id' => $episodeId, 'items' => []]);
        }

        $items = ImageGeneration::whereIn('scene_id', $sceneIds)->order('id', 'desc')->select()->toArray();
        return $this->success(['episode_id' => $episodeId, 'items' => $items]);
    }

    public function extractBackgrounds(int $episodeId)
    {
        SchemaService::ensureCoreTables();
        if (!Episode::find($episodeId)) {
            return $this->error('episode not found', 404);
        }

        $task = (new TaskService())->create('image.background.extract', ['episode_id' => $episodeId], 'completed');
        return $this->success([
            'episode_id' => $episodeId,
            'task' => $task->toArray(),
            'message' => 'background extraction completed (mock)',
        ]);
    }

    public function batch(int $episodeId)
    {
        SchemaService::ensureCoreTables();
        if (!Episode::find($episodeId)) {
            return $this->error('episode not found', 404);
        }

        $result = Db::transaction(function () use ($episodeId) {
            $scenes = Scene::where('episode_id', $episodeId)->order('sort_order', 'asc')->select();
            $created = [];
            foreach ($scenes as $scene) {
                $created[] = ImageGeneration::create([
                    'scene_id' => (int)$scene['id'],
                    'prompt' => trim((string)$scene['prompt']) !== '' ? (string)$scene['prompt'] : 'batch generated image',
                    'image_url' => '/static/mock/batch-scene-' . $scene['id'] . '-' . time() . '.png',
                    'status' => 'completed',
                    'created_at' => $this->now(),
                    'updated_at' => $this->now(),
                ])->toArray();
            }

            $task = (new TaskService())->create('image.batch.generate', [
                'episode_id' => $episodeId,
                'count' => count($created),
            ], 'completed');

            return [
                'episode_id' => $episodeId,
                'count' => count($created),
                'items' => $created,
                'task' => $task->toArray(),
            ];
        });

        return $this->success($result);
    }
}
