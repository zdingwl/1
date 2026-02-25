<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;

class VideoMergeController extends BaseApiController
{
    public function index()
    {
        return $this->success(['items' => []]);
    }

    public function create()
    {
        return $this->success(['merge_id' => uniqid('merge_', true)], 'created', 0, 201);
    }

    public function read(string $mergeId)
    {
        return $this->success(['merge_id' => $mergeId, 'status' => 'completed']);
    }

    public function delete(string $mergeId)
    {
        return $this->success(['merge_id' => $mergeId], 'deleted');
    }
}
