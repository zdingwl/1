<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Drama;
use app\model\Episode;
use app\service\SchemaService;
use think\Request;

class EpisodeController extends BaseApiController
{
    use RequestPayload;

    public function index(int $dramaId)
    {
        SchemaService::ensureCoreTables();

        if (!Drama::find($dramaId)) {
            return $this->error('drama not found', 404);
        }

        $items = Episode::where('drama_id', $dramaId)->order('episode_no', 'asc')->order('id', 'asc')->select()->toArray();
        return $this->success(['items' => $items]);
    }

    public function create(Request $request, int $dramaId)
    {
        SchemaService::ensureCoreTables();

        if (!Drama::find($dramaId)) {
            return $this->error('drama not found', 404);
        }

        $payload = $this->payload($request);
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return $this->error('title is required', 422);
        }

        $now = $this->now();
        $episode = Episode::create([
            'drama_id' => $dramaId,
            'title' => $title,
            'episode_no' => max(1, (int) ($payload['episode_no'] ?? 1)),
            'summary' => trim((string) ($payload['summary'] ?? '')),
            'status' => trim((string) ($payload['status'] ?? 'draft')),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->success($episode->toArray(), 'created', 0, 201);
    }

    public function update(Request $request, int $id)
    {
        SchemaService::ensureCoreTables();

        $episode = Episode::find($id);
        if (!$episode) {
            return $this->error('episode not found', 404);
        }

        $payload = $this->payload($request);
        $episode->save([
            'title' => trim((string) ($payload['title'] ?? $episode['title'])),
            'episode_no' => max(1, (int) ($payload['episode_no'] ?? $episode['episode_no'])),
            'summary' => trim((string) ($payload['summary'] ?? $episode['summary'])),
            'status' => trim((string) ($payload['status'] ?? $episode['status'])),
            'updated_at' => $this->now(),
        ]);

        return $this->success($episode->refresh()->toArray(), 'updated');
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();

        $episode = Episode::find($id);
        if (!$episode) {
            return $this->error('episode not found', 404);
        }

        $episode->delete();
        return $this->success(['id' => $id], 'deleted');
    }
}
