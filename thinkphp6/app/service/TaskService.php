<?php

declare(strict_types=1);

namespace app\service;

use app\model\Task;

/**
 * 任务服务。
 *
 * 主要职责：
 * - 统一任务创建与状态变更；
 * - 统一 JSON 字段编码/解码；
 * - 保证 task_key 唯一性生成策略集中管理。
 */
class TaskService
{
    /**
     * 创建任务记录。
     *
     * @param string $taskType  任务类型（如 image.generate / video.batch.generate）
     * @param array  $payload   入参快照（用于审计/排障）
     * @param string $status    初始状态（pending/running/completed/failed）
     */
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

    /**
     * 标记任务完成。
     */
    public function complete(Task $task, array $result = []): void
    {
        $task->save([
            'status' => 'completed',
            'progress' => 100,
            'result' => $this->encodeJson($result),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 标记任务失败。
     */
    public function fail(Task $task, array $result = []): void
    {
        $task->save([
            'status' => 'failed',
            'progress' => 100,
            'result' => $this->encodeJson($result),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 解码任务 JSON 字段（payload/result）。
     */
    public function decodeField($value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * 状态归一化，防止脏值落库。
     */
    private function normalizeStatus(string $status): string
    {
        $status = strtolower(trim($status));
        $allowed = ['pending', 'running', 'completed', 'failed'];
        return in_array($status, $allowed, true) ? $status : 'pending';
    }

    /**
     * 统一 JSON 编码。
     *
     * 注意：失败时兜底 `{}`，避免写入空字符串导致 decode 复杂化。
     */
    private function encodeJson(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    /**
     * 生成唯一 task_key。
     *
     * 策略：时间戳 + 随机后缀 + 数据库存在性校验。
     */
    private function generateUniqueTaskKey(): string
    {
        do {
            $key = 'task_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
        } while (Task::where('task_key', $key)->find());

        return $key;
    }
}
