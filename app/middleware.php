<?php
// 全局中间件定义文件

use app\middleware\CorsMiddleware;
use app\middleware\ApiAuthMiddleware;

return [
    // 全局请求缓存
    CorsMiddleware::class,
//    ApiAuthMiddleware::class
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    // \think\middleware\SessionInit::class
];
