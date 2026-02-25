<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Episode;
use app\model\ImageGeneration;
use app\model\VideoGeneration;
use app\service\SchemaService;
use app\service\TaskService;
use think\Request;
use think\facade\Db;

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

        $imageGenId = isset($payload['image_gen_id']) ? (int)$payload['image_gen_id'] : null;
        if ($imageGenId !== null && $imageGenId <= 0) {
            $imageGenId = null;
        }
        if ($imageGenId !== null && !ImageGeneration::find($imageGenId)) {
            return $this->error('image generation not found', 404);
        }

        $result = Db::transaction(function () use ($imageGenId, $payload) {
            $now = $this->now();
            $item = VideoGeneration::create([
                'image_gen_id' => $imageGenId,
                'prompt' => trim((string)($payload['prompt'] ?? '')),
                'video_url' => '/static/mock/video-' . time() . '.mp4',
                'status' => 'completed',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $task = (new TaskService())->create('video.generate', [
                'video_generation_id' => $item['id'],
                'image_gen_id' => $imageGenId,
            ], 'completed');

            return ['video' => $item->toArray(), 'task' => $task->toArray()];
        });

        return $this->success($result, 'created', 0, 201);
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
        SchemaService::ensureCoreTables();
        $image = ImageGeneration::find($imageGenId);
        if (!$image) {
            return $this->error('image generation not found', 404);
        }

        $result = Db::transaction(function () use ($imageGenId) {
            $video = VideoGeneration::create([
                'image_gen_id' => $imageGenId,
                'prompt' => 'generated from image ' . $imageGenId,
                'video_url' => '/static/mock/from-image-' . $imageGenId . '-' . time() . '.mp4',
                'status' => 'completed',
                'created_at' => $this->now(),
                'updated_at' => $this->now(),
            ]);

            $task = (new TaskService())->create('video.generate.from-image', [
                'image_gen_id' => $imageGenId,
                'video_generation_id' => $video['id'],
            ], 'completed');

            return ['video' => $video->toArray(), 'task' => $task->toArray()];
        });

        return $this->success($result, 'generated');
    }

    public function batch(int $episodeId)
    {
        SchemaService::ensureCoreTables();
        if (!Episode::find($episodeId)) {
            return $this->error('episode not found', 404);
        }

        $result = Db::transaction(function () use ($episodeId) {
            $images = ImageGeneration::alias('ig')
                ->join('scenes s', 's.id = ig.scene_id')
                ->where('s.episode_id', $episodeId)
                ->field('ig.*')
                ->select();

            $items = [];
            foreach ($images as $image) {
                $items[] = VideoGeneration::create([
                    'image_gen_id' => (int)$image['id'],
                    'prompt' => 'batch video for image ' . $image['id'],
                    'video_url' => '/static/mock/batch-video-' . $image['id'] . '-' . time() . '.mp4',
                    'status' => 'completed',
                    'created_at' => $this->now(),
                    'updated_at' => $this->now(),
                ])->toArray();
            }

            $task = (new TaskService())->create('video.batch.generate', [
                'episode_id' => $episodeId,
                'count' => count($items),
            ], 'completed');

            return [
                'episode_id' => $episodeId,
                'count' => count($items),
                'items' => $items,
                'task' => $task->toArray(),
            ];
        });

        return $this->success($result);
    }
}
