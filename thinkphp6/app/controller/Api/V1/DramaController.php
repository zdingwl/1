<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Character;
use app\model\Drama;
use app\model\Episode;
use app\service\SchemaService;
use think\Request;

class DramaController extends BaseApiController
{
    use RequestPayload;

    public function index(Request $request)
    {
        SchemaService::ensureCoreTables();

        $page = max(1, (int) $request->get('page', 1));
        $pageSize = max(1, min(100, (int) $request->get('page_size', 20)));
        $keyword = trim((string) $request->get('keyword', ''));

        $query = Drama::order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('title', '%' . $keyword . '%');
        }

        $total = (clone $query)->count();
        $items = $query->page($page, $pageSize)->select()->toArray();

        return $this->success([
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total' => $total,
            ],
        ]);
    }

    public function create(Request $request)
    {
        SchemaService::ensureCoreTables();

        $payload = $this->payload($request);
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return $this->error('title is required', 422);
        }

        $now = $this->now();
        $drama = Drama::create([
            'title' => $title,
            'genre' => trim((string) ($payload['genre'] ?? '')),
            'synopsis' => trim((string) ($payload['synopsis'] ?? '')),
            'progress' => max(0, min(100, (int) ($payload['progress'] ?? 0))),
            'status' => trim((string) ($payload['status'] ?? 'draft')),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->success($drama->toArray(), 'created', 0, 201);
    }

    public function read(int $id)
    {
        SchemaService::ensureCoreTables();

        $drama = Drama::find($id);
        if (!$drama) {
            return $this->error('drama not found', 404);
        }

        return $this->success($drama->toArray());
    }

    public function update(Request $request, int $id)
    {
        SchemaService::ensureCoreTables();

        $drama = Drama::find($id);
        if (!$drama) {
            return $this->error('drama not found', 404);
        }

        $payload = $this->payload($request);
        $title = trim((string) ($payload['title'] ?? $drama['title']));
        if ($title === '') {
            return $this->error('title is required', 422);
        }

        $drama->save([
            'title' => $title,
            'genre' => trim((string) ($payload['genre'] ?? $drama['genre'])),
            'synopsis' => trim((string) ($payload['synopsis'] ?? $drama['synopsis'])),
            'progress' => max(0, min(100, (int) ($payload['progress'] ?? $drama['progress']))),
            'status' => trim((string) ($payload['status'] ?? $drama['status'])),
            'updated_at' => $this->now(),
        ]);

        return $this->success($drama->refresh()->toArray(), 'updated');
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();

        $drama = Drama::find($id);
        if (!$drama) {
            return $this->error('drama not found', 404);
        }

        $drama->delete();
        return $this->success(['id' => $id], 'deleted');
    }

    public function stats()
    {
        SchemaService::ensureCoreTables();

        return $this->success([
            'total' => Drama::count(),
            'completed' => Drama::where('status', 'completed')->count(),
            'draft' => Drama::where('status', 'draft')->count(),
            'avg_progress' => (float) Drama::avg('progress'),
        ]);
    }

    public function saveOutline(int $id, Request $request)
    {
        SchemaService::ensureCoreTables();
        $drama = Drama::find($id);
        if (!$drama) {
            return $this->error('drama not found', 404);
        }

        $payload = $this->payload($request);
        $outline = trim((string)($payload['outline'] ?? ''));
        $drama->save([
            'synopsis' => $outline !== '' ? $outline : (string)$drama['synopsis'],
            'updated_at' => $this->now(),
        ]);

        return $this->success($drama->refresh()->toArray(), 'updated');
    }

    public function getCharacters(int $id)
    {
        SchemaService::ensureCoreTables();
        $items = Character::where('drama_id', $id)->order('id', 'desc')->select()->toArray();
        return $this->success(['items' => $items]);
    }

    public function saveCharacters(int $id, Request $request)
    {
        SchemaService::ensureCoreTables();
        $payload = $this->payload($request);
        $items = (array)($payload['characters'] ?? []);
        $now = $this->now();

        foreach ($items as $item) {
            if (!is_array($item) || trim((string)($item['name'] ?? '')) === '') {
                continue;
            }
            Character::create([
                'drama_id' => $id,
                'name' => trim((string)$item['name']),
                'profile' => trim((string)($item['profile'] ?? '')),
                'image_url' => trim((string)($item['image_url'] ?? '')),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $this->success(['count' => count($items)], 'saved');
    }

    public function saveEpisodes(int $id, Request $request)
    {
        SchemaService::ensureCoreTables();
        $payload = $this->payload($request);
        $items = (array)($payload['episodes'] ?? []);
        $now = $this->now();

        foreach ($items as $idx => $item) {
            if (!is_array($item) || trim((string)($item['title'] ?? '')) === '') {
                continue;
            }
            Episode::create([
                'drama_id' => $id,
                'title' => trim((string)$item['title']),
                'episode_no' => (int)($item['episode_no'] ?? ($idx + 1)),
                'summary' => trim((string)($item['summary'] ?? '')),
                'status' => trim((string)($item['status'] ?? 'draft')),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $this->success(['count' => count($items)], 'saved');
    }

    public function saveProgress(int $id, Request $request)
    {
        SchemaService::ensureCoreTables();
        $drama = Drama::find($id);
        if (!$drama) {
            return $this->error('drama not found', 404);
        }

        $progress = max(0, min(100, (int)$request->put('progress', $request->post('progress', 0))));
        $drama->save(['progress' => $progress, 'updated_at' => $this->now()]);

        return $this->success($drama->refresh()->toArray(), 'updated');
    }
}
