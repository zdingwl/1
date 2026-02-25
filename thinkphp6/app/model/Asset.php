<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class Asset extends Model
{
    protected $name = 'assets';
    protected $autoWriteTimestamp = false;
}
