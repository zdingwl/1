<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class Prop extends Model
{
    protected $name = 'props';
    protected $autoWriteTimestamp = false;
}
