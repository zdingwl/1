<?php

declare(strict_types=1);

namespace app\common;

use think\Request;

trait RequestPayload
{
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
