<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class Episode extends Model
{
    protected $name = 'episodes';
    protected $autoWriteTimestamp = false;
}
