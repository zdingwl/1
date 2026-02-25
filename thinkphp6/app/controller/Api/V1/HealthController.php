<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;

class HealthController extends BaseApiController
{
    public function index()
    {
        return $this->success([
            'status' => 'ok',
            'app' => config('app.app_name', 'Huobao Drama API'),
            'version' => config('app.app_version', '1.0.0'),
        ]);
    }
}
