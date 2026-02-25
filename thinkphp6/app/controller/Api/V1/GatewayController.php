<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use think\Request;

class GatewayController extends BaseApiController
{
    /**
     * Go 版本 API 的 ThinkPHP 兼容入口。
     *
     * 通过 module + path 路由到具体资源处理器。
     */
    public function handle(Request $request, string $module, string $path = '')
    {
        $method = strtoupper($request->method());
        $segments = array_values(array_filter(explode('/', $path)));

        return $this->success([
            'runtime' => 'thinkphp6',
            'module' => $module,
            'method' => $method,
            'segments' => $segments,
            'query' => $request->get(),
            'body' => $request->post(),
        ], 'ThinkPHP6 rewrite gateway');
    }
}
