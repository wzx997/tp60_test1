<?php


namespace app\controller\common;


use app\BaseController;
use app\common\Email;
use think\facade\Log;

/**
 * 测试用的文件，主要是测试一些公共方法用
 * Class TestAPi
 * @package app\controller\common
 */
class TestAPI extends BaseController
{
    /**
     * 邮件发送测试接口1
     * @return \think\response\Json
     */
    public function testSendEmail()
    {
        $to        = $this->request->post('to', '');
        $subject   = $this->request->post('subject', '');
        $body      = $this->request->post('body', '');
        $cc        = $this->request->post('cc', '');
        $from_name = $this->request->post('from_name', '');

        try {
            $res = Email::sendEmail($to, $subject, $body, $cc, $from_name);
        } catch (\Throwable $e) {
            Log::error('【邮件发送异常】，异常信息为：'.$e->getMessage());
            return $this->resFail('邮件发送异常'.$e->getMessage());
        }

        if ($res) {
            return $this->resSuccess();
        }

        return $this->resFail('邮件发送失败');
    }

    /**
     * 邮件发送2，采用链式调用的方式
     * @return \think\response\Json
     */
    public function testSendEmail2()
    {
        $data      = $this->request->post();
        $to        = $data['to'];
        $subject   = $data['subject'];
        $body      = $data['body'];
        $cc        = $data['cc'];
        $from_name = $data['from_name'];

        try {
            $mail = new Email();
            $res = $mail->to($to)
                ->subject($subject)
                ->message($body, true)
                ->addCC($cc)
                ->fromName($from_name)
                ->send();
        } catch (\Throwable $e) {
            Log::error('【邮件发送异常】，异常信息为：'.$e->getMessage());
            return $this->resFail('邮件发送异常'.$e->getMessage());
        }

        if ($res) {
            return $this->resSuccess();
        }

        return $this->resFail('邮件发送失败');
    }
}
