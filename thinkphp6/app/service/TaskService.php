<?php

declare(strict_types=1);

namespace app\service;

use app\model\Task;

class TaskService
{
    public function create(string $taskType, array $payload = [], string $status = 'pending'): Task
    {
        SchemaService::ensureCoreTables();

        $status = $this->normalizeStatus($status);
        $now = date('Y-m-d H:i:s');
        $taskKey = $this->generateUniqueTaskKey();

        return Task::create([
            'task_key' => $taskKey,
            'task_type' => $taskType,
            'status' => $status,
            'progress' => $status === 'completed' ? 100 : 0,
            'payload' => $this->encodeJson($payload),
            'result' => $this->encodeJson([]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function complete(Task $task, array $result = []): void
    {
        $task->save([
            'status' => 'completed',
            'progress' => 100,
            'result' => $this->encodeJson($result),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function fail(Task $task, array $result = []): void
    {
        $task->save([
            'status' => 'failed',
            'progress' => 100,
            'result' => $this->encodeJson($result),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function decodeField($value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));
        $allowed = ['pending', 'running', 'completed', 'failed'];
        return in_array($status, $allowed, true) ? $status : 'pending';
    }

    private function encodeJson(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function generateUniqueTaskKey(): string
    {
        do {
            $key = 'task_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
        } while (Task::where('task_key', $key)->find());

        return $key;
    }
}
