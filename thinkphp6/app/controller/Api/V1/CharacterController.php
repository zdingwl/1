<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Character;
use app\service\SchemaService;
use think\Request;

class CharacterController extends BaseApiController
{
    use RequestPayload;

    public function update(Request $request, int $id)
    {
        SchemaService::ensureCoreTables();
        $character = Character::find($id);
        if (!$character) {
            return $this->error('character not found', 404);
        }

        $payload = $this->payload($request);
        $character->save([
            'name' => trim((string)($payload['name'] ?? $character['name'])),
            'profile' => trim((string)($payload['profile'] ?? $character['profile'])),
            'image_url' => trim((string)($payload['image_url'] ?? $character['image_url'])),
            'library_id' => isset($payload['library_id']) ? (int)$payload['library_id'] : $character['library_id'],
            'updated_at' => $this->now(),
        ]);

        return $this->success($character->refresh()->toArray(), 'updated');
    }

    public function delete(int $id)
    {
        SchemaService::ensureCoreTables();
        $character = Character::find($id);
        if (!$character) {
            return $this->error('character not found', 404);
        }
        $character->delete();
        return $this->success(['id' => $id], 'deleted');
    }

    public function batchGenerateImages(Request $request)
    {
        $payload = $this->payload($request);
        $ids = (array)($payload['character_ids'] ?? []);
        return $this->success(['requested_ids' => $ids, 'message' => 'mock batch image generation queued']);
    }

    public function generateImage(int $id)
    {
        return $this->success(['id' => $id, 'image_url' => '/static/mock/character-' . $id . '.png']);
    }

    public function uploadImage(int $id)
    {
        return $this->success(['id' => $id, 'message' => 'please use /api/v1/upload/image endpoint'], 'ok');
    }

    public function applyLibrary(int $id, Request $request)
    {
        SchemaService::ensureCoreTables();
        $character = Character::find($id);
        if (!$character) {
            return $this->error('character not found', 404);
        }
        $payload = $this->payload($request);
        $libraryId = isset($payload['library_id']) ? (int)$payload['library_id'] : null;
        if ($libraryId !== null && $libraryId <= 0) {
            $libraryId = null;
        }
        $character->save(['library_id' => $libraryId, 'updated_at' => $this->now()]);
        return $this->success($character->refresh()->toArray(), 'updated');
    }

    public function addToLibrary(int $id)
    {
        return $this->success(['id' => $id, 'message' => 'mock add-to-library done']);
    }
}
