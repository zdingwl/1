<?php

declare(strict_types=1);

namespace app\controller\Api\V1;

use app\common\BaseApiController;

class AudioController extends BaseApiController
{
    public function extract()
    {
        return $this->success(['message' => 'mock audio extraction queued']);
    }

    public function batchExtract()
    {
        return $this->success(['message' => 'mock batch audio extraction queued']);
    }
}
