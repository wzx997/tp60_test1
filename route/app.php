<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('hello/:name', 'index/hello');

Route::get('test', function () {
    $data = [
        'code' => 0,
        'msg' => 'tp6路由配置成功',
        'data' => []
    ];
    return json($data);
});

Route::post('test2', 'test.Test/test2');
Route::get('test3', 'test.Test/test3');

// 开始
// 用户模块
Route::post('reg', 'user.User/reg'); //注册
Route::post('login', 'user.User/login'); //登录
Route::post('getUserList', 'user.User/getUserList'); //查询用户列表
Route::post('updateUser', 'user.User/updateUser'); // 更新用户

// 测试模块
Route::post('testSendEmail', 'common.TestAPI/testSendEmail'); // 测试发送邮件
Route::post('testSendEmail2', 'common.TestAPI/testSendEmail2'); // 测试发送邮件