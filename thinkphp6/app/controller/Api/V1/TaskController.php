<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use app\common\RequestPayload;
use app\model\Task;
use app\service\SchemaService;
use app\service\TaskService;
use think\Request;

class TaskController extends BaseApiController
{
    use RequestPayload;

    public function index(Request $request)
    {
        SchemaService::ensureCoreTables();

        $status = trim((string) $request->get('status', ''));
        $query = Task::order('id', 'desc');
        if ($status !== '') {
            $query->where('status', $status);
        }

        $service = new TaskService();
        $items = [];
        foreach ($query->select() as $task) {
            $items[] = $this->formatTask($task->toArray(), $service);
        }

        return $this->success(['items' => $items]);
    }

    public function read(string $taskId)
    {
        SchemaService::ensureCoreTables();

        $task = Task::where('task_key', $taskId)->find();
        if (!$task) {
            return $this->error('task not found', 404);
        }

        return $this->success($this->formatTask($task->toArray(), new TaskService()));
    }

    public function create(Request $request)
    {
        SchemaService::ensureCoreTables();

        $payload = $this->payload($request);
        $taskType = trim((string) ($payload['task_type'] ?? ''));
        if ($taskType === '') {
            return $this->error('task_type is required', 422);
        }

        $taskKey = trim((string) ($payload['task_key'] ?? ''));
        if ($taskKey === '') {
            $taskKey = $this->generateTaskKey();
        }

        if (Task::where('task_key', $taskKey)->find()) {
            return $this->error('task_key already exists', 409);
        }

        $service = new TaskService();
        $task = Task::create([
            'task_key' => $taskKey,
            'task_type' => $taskType,
            'status' => trim((string) ($payload['status'] ?? 'pending')),
            'progress' => max(0, min(100, (int) ($payload['progress'] ?? 0))),
            'payload' => json_encode($payload['payload'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'result' => json_encode($payload['result'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ]);

        return $this->success($this->formatTask($task->toArray(), $service), 'created', 0, 201);
    }

    public function update(Request $request, string $taskId)
    {
        SchemaService::ensureCoreTables();

        $task = Task::where('task_key', $taskId)->find();
        if (!$task) {
            return $this->error('task not found', 404);
        }

        $payload = $this->payload($request);
        $task->save([
            'status' => trim((string) ($payload['status'] ?? $task['status'])),
            'progress' => max(0, min(100, (int) ($payload['progress'] ?? $task['progress']))),
            'result' => isset($payload['result']) ? json_encode($payload['result'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $task['result'],
            'updated_at' => $this->now(),
        ]);

        return $this->success($this->formatTask($task->refresh()->toArray(), new TaskService()), 'updated');
    }

    private function formatTask(array $task, TaskService $service): array
    {
        $task['payload'] = $service->decodeField($task['payload'] ?? '');
        $task['result'] = $service->decodeField($task['result'] ?? '');
        return $task;
    }

    private function generateTaskKey(): string
    {
        return 'task_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
    }
}
