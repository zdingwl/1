<?php

declare(strict_types=1);

namespace app\common;

use think\Request;

/**
 * 请求体解析 Trait。
 *
 * 背景：
 * - 前端/调用方可能混用 query/form/json；
 * - PUT/PATCH 下某些参数读取方式在不同环境中不稳定；
 *
 * 目标：
 * - 给控制器提供统一入口 `payload()`；
 * - 优先兼容所有输入源，减少“同接口不同提交方式行为不一致”。
 */
trait RequestPayload
{
    /**
     * 解析并合并请求参数。
     *
     * 合并策略：
     * 1) 先取框架层 `param()`（通常覆盖 query + form + route param）；
     * 2) 再解析原始 body 的 JSON，若有效则覆盖同名字段；
     *
     * 这样可确保 JSON body 在多数场景拥有更高优先级。
     */
    protected function payload(Request $request): array
    {
        $data = $request->param();
        if (!is_array($data)) {
            $data = [];
        }

        $raw = $request->getInput();
        if (is_string($raw) && trim($raw) !== '') {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $data = array_merge($data, $json);
            }
        }

        return $data;
    }
}
