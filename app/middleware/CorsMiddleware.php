<?php
declare (strict_types = 1);

namespace app\middleware;

use think\Request;

/**
 * 跨域请求中间件
 * Class CorsMiddleware
 * @package app\middleware
 */
class CorsMiddleware
{
    /**
     * 处理跨域请求，与6.0不同，只需要设置中间件即可解决5.1的options请求问题
     * @param Request $request
     * @param \Closure $next
     * @return mixed|\think\Response
     */
    public function handle(Request $request, \Closure $next)
    {
        header('Access-Control-Allow-Origin: *'); // 后期具体设置允许源
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, auth-token');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, PUT, DELETE');
        if ($request->isOptions()) {
            // options探路请求，先返回200 OK，然后跨域会发起一个新的请求，
            //这时候是post请求，进入到控制器中
            return response();
        }

        return $next($request);
    }
}