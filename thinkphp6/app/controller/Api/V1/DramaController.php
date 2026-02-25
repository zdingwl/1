<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Drama;
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
}
