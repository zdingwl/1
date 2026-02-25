<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class AppSetting extends Model
{
    protected $name = 'app_settings';
    protected $autoWriteTimestamp = false;
}
