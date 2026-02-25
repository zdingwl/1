<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\model\Drama;
use app\service\SchemaService;
use think\Request;

class DramaController extends BaseApiController
{
    public function index(Request $request)
    {
        SchemaService::ensureCoreTables();

        $page = max(1, (int) $request->get('page', 1));
        $pageSize = max(1, min(100, (int) $request->get('page_size', 20)));

        $query = Drama::order('id', 'desc');
        $total = $query->count();
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

        $payload = $request->post();
        $title = trim((string)($payload['title'] ?? ''));
        if ($title === '') {
            return $this->error('title is required', 422);
        }

        $now = date('Y-m-d H:i:s');
        $drama = Drama::create([
            'title' => $title,
            'genre' => (string)($payload['genre'] ?? ''),
            'synopsis' => (string)($payload['synopsis'] ?? ''),
            'progress' => (int)($payload['progress'] ?? 0),
            'status' => (string)($payload['status'] ?? 'draft'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->success($drama->toArray(), 'created');
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

        $payload = $request->put();
        $drama->save([
            'title' => (string)($payload['title'] ?? $drama['title']),
            'genre' => (string)($payload['genre'] ?? $drama['genre']),
            'synopsis' => (string)($payload['synopsis'] ?? $drama['synopsis']),
            'progress' => (int)($payload['progress'] ?? $drama['progress']),
            'status' => (string)($payload['status'] ?? $drama['status']),
            'updated_at' => date('Y-m-d H:i:s'),
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

        $total = Drama::count();
        $completed = Drama::where('status', 'completed')->count();
        $draft = Drama::where('status', 'draft')->count();

        return $this->success([
            'total' => $total,
            'completed' => $completed,
            'draft' => $draft,
        ]);
    }
}
