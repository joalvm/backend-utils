<?php

namespace Joalvm\Utils;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Joalvm\Utils\Traits\Validatable;

class Model extends BaseModel
{
    use Validatable;
}
