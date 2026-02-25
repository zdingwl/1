<?php

declare(strict_types=1);

namespace app\service;

use app\model\Task;

class TaskService
{
    public function create(string $taskType, array $payload = [], string $status = 'pending'): Task
    {
        SchemaService::ensureCoreTables();

        $now = date('Y-m-d H:i:s');
        $taskKey = $this->generateUniqueTaskKey();

        return Task::create([
            'task_key' => $taskKey,
            'task_type' => $taskType,
            'status' => $status,
            'progress' => $status === 'completed' ? 100 : 0,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'result' => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function complete(Task $task, array $result = []): void
    {
        $task->save([
            'status' => 'completed',
            'progress' => 100,
            'result' => json_encode($result, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function generateUniqueTaskKey(): string
    {
        do {
            $key = 'task_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
        } while (Task::where('task_key', $key)->find());

        return $key;
    }
}
