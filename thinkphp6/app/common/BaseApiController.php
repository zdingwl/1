<?php

declare(strict_types=1);

namespace app\common;

use think\Response;

/**
 * API 控制器基类。
 *
 * 设计目标：
 * 1) 统一所有接口的 JSON 响应结构；
 * 2) 降低业务控制器重复代码；
 * 3) 保留扩展点（HTTP 状态码、业务码、统一时间函数）。
 */
abstract class BaseApiController
{
    /**
     * 成功响应。
     *
     * 输出格式固定为：
     * {
     *   "code": 0,
     *   "message": "ok",
     *   "data": {...}
     * }
     *
     * @param array  $data       业务数据
     * @param string $message    提示信息
     * @param int    $code       业务码（默认 0 表示成功）
     * @param int    $httpStatus HTTP 状态码（默认 200）
     */
    protected function success(array $data = [], string $message = 'ok', int $code = 0, int $httpStatus = 200): Response
    {
        return json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $httpStatus);
    }

    /**
     * 失败响应。
     *
     * 输出格式与 success 保持一致，只是 code / message / HTTP 状态码不同。
     *
     * @param string $message    错误信息
     * @param int    $httpStatus HTTP 状态码（如 400/404/422/500）
     * @param array  $data       附加错误数据
     * @param int    $code       业务错误码（默认 1）
     */
    protected function error(string $message = 'error', int $httpStatus = 400, array $data = [], int $code = 1): Response
    {
        return json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $httpStatus);
    }

    /**
     * 当前时间（统一格式）。
     *
     * 统一使用 `Y-m-d H:i:s`，避免业务层到处写 date()。
     */
    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
