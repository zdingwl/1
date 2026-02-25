<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;
use think\Request;

class NotImplementedController extends BaseApiController
{
    public function handle(Request $request, string $module, string $path = '')
    {
        return $this->error('module not migrated to thinkphp6 yet', 501, [
            'module' => $module,
            'path' => $path,
            'method' => strtoupper($request->method()),
        ]);
    }
}
