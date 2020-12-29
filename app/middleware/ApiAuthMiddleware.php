<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Db;
use think\facade\Log;
use think\facade\Request;

class ApiAuthMiddleware
{
    public function handle($request, \Closure $next)
    {

        $url = Request::baseUrl(); // 获取请求的url
        $notNeedAuth = [
            '/think',
            '/hello',
            '/test',
            '/reg',
            '/login',
            '/getCodeByEmail', // 重置密码时发送验证码接口
            '/resetPassword', // 重置密码接口
        ]; // 白名单列表，

        if (in_array($url, $notNeedAuth)) {// 白名单的接口不用认证
            return $next($request);
        }

        $token = Request::header('auth-token');

        try {
            $login_log = Db::table('tp_login_log')
                ->where('token', '=', $token)
                ->field('id,create_time')
                ->find();
            if (is_null($login_log)) {// 如果未传token，提示无权访问
                return $this->response(10001, '您无权访问');
            }
            // 一天免登录
            $time = strtotime($login_log['create_time']) + 24 * 60 * 60;
            if (time() > $time) { //登录过期
                return $this->response(10002, '登录已过期，请重新登录');
            }
        } catch (\Throwable $e) {
            $date = date("Y-m-d H:i:s", time());
            Log::record("【接口鉴权异常 $date 】" . $e->getMessage(), 'error');
            return $this->response(10000, '数据库连接异常');
        }

        // 验证成功，跳转到控制器
        return $next($request);
    }

    /**
     * @param $code
     * @param $msg
     * @return \think\Response
     */
    private function response($code, $msg)
    {
        $data = [
            'code' => $code,
            'msg' => $msg
        ];
        return json($data);
    }
}
