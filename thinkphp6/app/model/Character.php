<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class Character extends Model
{
    protected $name = 'characters';
    protected $autoWriteTimestamp = false;
}
