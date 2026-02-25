<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;

class UploadController extends BaseApiController
{
    public function image()
    {
        return $this->success([
            'url' => '/static/mock/upload-' . time() . '.png',
            'message' => 'mock upload success',
        ]);
    }
}
