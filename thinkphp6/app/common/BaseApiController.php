<?php

declare(strict_types=1);

namespace app\common;

use think\Response;

abstract class BaseApiController
{
    protected function success(array $data = [], string $message = 'ok', int $code = 0, int $httpStatus = 200): Response
    {
        return json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $httpStatus);
    }

    protected function error(string $message = 'error', int $httpStatus = 400, array $data = [], int $code = 1): Response
    {
        return json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $httpStatus);
    }

    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
