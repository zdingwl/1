<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class Scene extends Model
{
    protected $name = 'scenes';
    protected $autoWriteTimestamp = false;
}
