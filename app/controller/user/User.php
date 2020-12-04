<?php


namespace app\controller\user;


use app\BaseController;
use think\facade\Db;
use think\facade\Validate;

class User extends BaseController
{
    /**
     * 用户注册
     * @return \think\response\Json
     */
    public function reg()
    {
        $data = $this->request->post();

        $rule = [
            'username|用户名' => 'require|length:1,20',
            'password|密码' => 'require|length:6,20',
            'email|邮箱' => 'email',
            'mobile|手机号' => 'mobile'
        ];
        $validate = Validate::rule($rule); //参数校验

        if (!$validate->check($data)) {
            return $this->resFail($validate->getError());
        }

        // 对密码进行加密存储
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        try {
            $user = Db::table('tp_user')
                ->where('username', '=', $data['username'])
                ->field('id')
                ->find();
            if (!is_null($user)) {
                return $this->resFail('该用户名已被注册');
            }

            Db::table('tp_user')->insert($data);
        } catch (\Throwable $e) {
            $this->writeLog($e->getMessage());
            return $this->resFail('注册失败');
        }

        return $this->resSuccess([], '注册成功');
    }

    /**
     * 登录接口
     * @return \think\response\Json
     */
    public function login()
    {
        $data = $this->request->post();

        $rule = [
            'username|用户名' => 'require',
            'password|密码' => 'require',
        ];
        $validate = Validate::rule($rule); //参数校验

        if (!$validate->check($data)) {
            return $this->resFail($validate->getError());
        }

        try {
            $user = Db::table('tp_user')
                ->where(['username' => $data['username'], 'is_del' => 0])
                ->field('id,username,mobile,email,password')
                ->find();
            if (is_null($user)) {
                return $this->resFail('用户名不存在');
            }

            if (password_verify($data['password'], $user['password'])) { // 验证密码，通过则表示登录成功
                $token = md5($data['username'] . time());
                Db::table('tp_login_log')->insert(['user_id' => $user['id'], 'token' => $token]);
                $user['token'] = $token; // 添加token字段
                unset($user['password']); // 删除密码字段
                return $this->resSuccess($user, '登录成功');
            } else { // 密码不正确
                return $this->resFail('用户名或密码错误');
            }
        } catch (\Throwable $e) {
            $this->writeLog($e);
            return $this->resFail('登录异常');
        }
    }
}
