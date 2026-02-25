<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;

class TaskController extends BaseApiController
{
    public function index()
    {
        return $this->success([
            'items' => [],
            'message' => 'task queue migration pending',
        ]);
    }

    public function read(string $taskId)
    {
        return $this->success([
            'task_id' => $taskId,
            'status' => 'pending',
            'progress' => 0,
            'note' => 'task subsystem not migrated yet',
        ]);
    }
}
