<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\CharacterLibrary;
use app\service\SchemaService;
use think\Request;

class CharacterLibraryController extends BaseApiController
{
    use RequestPayload;

    public function index()
    {
        SchemaService::ensureCoreTables();
        return $this->success(['items' => CharacterLibrary::order('id', 'desc')->select()->toArray()]);
    }

    public function create(Request $request)
    {
        SchemaService::ensureCoreTables();
        $payload = $this->payload($request);
        $name = trim((string)($payload['name'] ?? ''));
        if ($name === '') {
            return $this->error('name is required', 422);
        }

        $now = $this->now();
        $item = CharacterLibrary::create([
            'name' => $name,
            'description' => trim((string)($payload['description'] ?? '')),
            'image_url' => trim((string)($payload['image_url'] ?? '')),
            'tags' => trim((string)($payload['tags'] ?? '')),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->success($item->toArray(), 'created', 0, 201);
    }

    public function read(int $id)
    {
        SchemaService::ensureCoreTables();
        $item = CharacterLibrary::find($id);
        if (!$item) {
            return $this->error('item not found', 404);
        }
        return $this->success($item->toArray());
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();
        $item = CharacterLibrary::find($id);
        if (!$item) {
            return $this->error('item not found', 404);
        }
        $item->delete();
        return $this->success(['id' => $id], 'deleted');
    }
}
