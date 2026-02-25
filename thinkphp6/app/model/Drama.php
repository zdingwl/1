<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class Drama extends Model
{
    protected $name = 'dramas';
    protected $autoWriteTimestamp = false;
    protected $field = [
        'id', 'title', 'genre', 'synopsis', 'progress', 'status', 'created_at', 'updated_at',
    ];
}
