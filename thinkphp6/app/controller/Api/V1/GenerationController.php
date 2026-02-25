<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;

class GenerationController extends BaseApiController
{
    public function characters()
    {
        return $this->success([
            'items' => [
                ['name' => '主角', 'profile' => '由剧情自动生成（mock）'],
                ['name' => '配角', 'profile' => '由剧情自动生成（mock）'],
            ],
            'message' => 'mock character generation success',
        ]);
    }
}
