<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

class ImageGeneration extends Model
{
    protected $name = 'image_generations';
    protected $autoWriteTimestamp = false;
}
