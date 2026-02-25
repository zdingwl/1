<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class AIConfig extends Model
{
    protected $name = 'ai_configs';
    protected $autoWriteTimestamp = false;
    protected $field = [
        'id', 'name', 'provider', 'model', 'endpoint', 'is_enabled', 'created_at', 'updated_at',
    ];
}
