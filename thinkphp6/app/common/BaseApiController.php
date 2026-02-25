<?php

declare(strict_types=1);

namespace app\common;

use think\Response;

abstract class BaseApiController
{
    protected function success(array $data = [], string $message = 'ok', int $code = 0): Response
    {
        return json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);
    }

    protected function error(string $message = 'error', int $code = 1, array $data = []): Response
    {
        return json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);
    }
}
