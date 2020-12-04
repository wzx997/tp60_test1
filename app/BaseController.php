<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\facade\Log;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 响应数据的通用方法，响应json时调用
     * @param $code int 返回状态码，必填
     * @param mixed $data 返回数据，默认是一个数组，也可以是一个对象
     * @param string $msg 返回的消息文本
     * @return \think\response\Json
     */
    protected function response(int $code, $data = [], $msg = '请求成功')
    {
        $res = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
        return json($res);
    }

    /**
     *  正确处理响应的返回形式，响应json时调用
     * @param mixed $data 成功的数据。数组或者对象，也可以是空
     * @param string $msg 返回的消息文本
     * @param int $code 返回状态码，成功默认是0
     * @return \think\response\Json
     */
    protected function resSuccess($data = [], $msg = '请求成功', $code = 0)
    {
        return $this->response($code, $data, $msg);
    }

    /**
     * 错误处理响应的返回形式，响应json时调用
     * @param string $msg 返回的消息文本
     * @param mixed $data 失败的数据，一般为空
     * @param int $code 返回状态码，失败默认是1
     * @return \think\response\Json
     */
    protected function resFail($msg = '请求失败', $code = 1, $data = [])
    {
        return $this->response($code, $data, $msg);
    }

    /**
     * 自定义日志记录
     * @param $e mixed 错误对象。尽量传入\Throwable对象，否则无意义
     * @param string $type 日志记录级别，默认是error
     */
    protected function writeLog($e, $type = 'error')
    {
        $msg = '';
        if ($e instanceof \Throwable) {
            $error_msg = $e->getMessage();
            $trace = $e->getTraceAsString();
            $date = date("Y-m-d H:i:s",time());
            $msg = "【异常发生 $date 】 异常信息为：".$error_msg.', 请检查：'.$trace."\n";
        }
        Log::record($msg, $type);
    }
}
