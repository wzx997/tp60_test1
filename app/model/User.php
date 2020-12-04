<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;


class User extends Model
{
    protected $table = 'tp_user';

    protected $pk = 'id';

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }
}
