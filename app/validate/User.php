<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'name'  => 'require|max:25',
        'age'   => 'require|number|between:1,120',
        'email' => 'require|email',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'  => '姓名不能为空',
        'age.require'   => '年龄不能为空',
        'email.require' => '邮箱不能为空',
        'name.max'      => '名称最多不能超过25个字符',
        'age.number'    => '年龄必须是数字',
        'age.between'   => '年龄只能在1-120之间',
        'email'         => '邮箱格式错误',
    ];
}
