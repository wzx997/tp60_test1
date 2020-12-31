<?php


namespace app\controller\user;


use app\BaseController;
use app\common\Code;
use app\common\Email;
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
            'email|邮箱' => 'require|email',
            'mobile|手机号' => 'require|mobile'
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
                ->whereOr('mobile', '=', $data['mobile'])
                ->whereOr('email', '=', $data['email'])
                ->field('username,mobile,email')
                ->find();
            if (!is_null($user)) {
                if ($user['username'] == $data['username']) {
                    return $this->resFail('该用户名已被注册');
                }
                if ($user['mobile'] == $data['mobile']) {
                    return $this->resFail('该手机号已被注册');
                }
                if ($user['email'] == $data['email']) {
                    return $this->resFail('该邮箱已被注册');
                }
            }

            Db::table('tp_user')->insert($data);
        } catch (\Throwable $e) {
            $this->writeLog($e);
            return $this->resFail('注册失败'.$e->getMessage());
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

    /**
     * 用户列表查询接口
     * @return \think\response\Json
     */
    public function getUserList()
    {
        $username   = $this->request->post('username', ''); // 用户名模糊匹配
        $start_time = $this->request->post('start_time', ''); // 注册开始时间
        $end_time   = $this->request->post('end_time', ''); // 注册结束字节
        $page_num   = $this->request->post('page_num', 1); // 分页第几页，默认第一页
        $page_size  = $this->request->post('page_size', 10); // 每页数据量，默认10条
        $sort_key   = $this->request->post('sort_key', 'id'); // 排序字段，默认id
        $sort_value = $this->request->post('sort_value', 'asc'); // 排序贵州，默认asc

        // 构造查询条件
        $condition = [];
        // 处理搜索字段
        if (!empty($username)) {
            $condition[] = ['username', 'like', "%{$username}%"];
        }
        if (!empty($start_time) and !empty($end_time)) {
            $condition[] = ['create_time', 'between', [$start_time, $end_time]];
        }
        // 处理排序字段
        if (empty($page_num) or !is_numeric($page_num)) {
            $page_num = 1;
        }
        if (empty($page_size) or !is_numeric($page_size)) {
            $page_size = 10;
        }
        // 处理排序字段
        if (empty($sort_key)) {
            $sort_key = 'id';
        }
        if (empty($sort_value)) {
            $sort_value = 'asc';
        }

        try {
            $total = Db::table('tp_user')
                ->field('id,username,mobile,email,create_time')
                ->where($condition)
                ->where('is_del', '=', 0)
                ->count();

            $users = Db::table('tp_user')
                ->field('id,username,mobile,email,create_time')
                ->where($condition)
                ->where('is_del', '=', 0)
                ->page($page_num, $page_size)
                ->order($sort_key, $sort_value)
                ->select();
        } catch (\Throwable $e) {
            $this->writeLog($e);
            return $this->resFail('获取用户列表失败');
        }

        return $this->resSuccess(['total' => $total, 'list' => $users]);
    }

    /**
     * 更新用户信息。仅更新手机号码和邮箱
     * @return \think\response\Json
     */
    public function updateUser()
    {
        $data = $this->request->post();

        $rule = [
            'id|用户ID' => 'require',
            'email|邮箱' => 'email',
            'mobile|手机号' => 'mobile'
        ];
        $validate = Validate::rule($rule); //参数校验

        if (!$validate->check($data)) {
            return $this->resFail($validate->getError());
        }

        try {
            $user = Db::table('tp_user')
                ->field('mobile,email')
                ->where('id', '<>', $data['id'])
                ->where(function ($query) use ($data) {
                    $query->where('email', '=', $data['email'])
                        ->whereOr('mobile', '=', $data['mobile']);
                })
                ->find();
            if (!is_null($user)) {// 手机号与邮箱地址被注册
                if ($user['mobile'] == $data['mobile']) {
                    return $this->resFail('该手机号已被注册');
                }
                if ($user['email'] == $data['email']) {
                    return $this->resFail('该邮箱已被注册');
                }
            }

            Db::table('tp_user')
                ->where('id', '=', $data['id'])
                ->update(['email' => $data['email'], 'mobile' => $data['mobile']]);
            return $this->resSuccess([], '更新成功');
        } catch (\Throwable $e) {
            $this->writeLog($e);
            return $this->resFail('更新失败');
        }
    }

    /**
     * 找回密码操作 发送邮件验证码接口
     * @return \think\response\Json
     */
    public function getCodeByEmail()
    {
        $data = $this->request->post();

        $rule = [
            'username|用户名' => 'require',
            'email|邮箱' => 'require|email'
        ];
        $validate = Validate::rule($rule); //参数校验

        if (!$validate->check($data)) {
            return $this->resFail($validate->getError());
        }

        $code = Code::getCode();  // 获取验证码
        $mail = new Email(); // 获取邮箱对象实例
        $body = "尊敬的<b> {$data['username']}</b> 用户，你好！你本次找回密码操作验证码为
            ：<span style='font-size: 20px'>{$code}</span>，请勿泄露，请尽快操作，十分钟内有效。";

        try {
            $user = Db::table('tp_user')
                ->field('id')
                ->where('username', '=', $data['username'])
                ->where('email', '=', $data['email'])
                ->find();
            if (is_null($user)) {
                return $this->resFail('用户名与邮箱不匹配');
            }

            $email_res = $mail->to($data['email'])
                ->subject('找回密码')
                ->fromName('超级平台 邮件')
                ->message($body, true)
                ->send();
            if ($email_res) { // 邮件发送成功
                $insert_data = [
                    'user_id'     => $user['id'],
                    'code'        => $code,
                    'expire_time' => time() + 10 * 60
                ];
                Db::table('tp_user_code')->insert($insert_data);
                return $this->resSuccess([], '验证码发送成功，请到邮箱查询');
            } else {
                return $this->resFail([], '验证码发送失败');
            }
        } catch (\Throwable $e) {
            $this->writeLog($e);
            return $this->resFail('发送验证码失败'.$e->getMessage());
        }
    }

    /**
     * 重置密码
     * @return \think\response\Json
     */
    public function resetPassword()
    {
        $data = $this->request->post();

        $rule = [
            'username|用户名' => 'require',
            'email|邮箱' => 'require|email',
            'code|验证码' => 'require',
            'password|密码' => 'require|length:6,20',
        ];
        $validate = Validate::rule($rule); //参数校验

        if (!$validate->check($data)) {
            return $this->resFail($validate->getError());
        }

        Db::startTrans(); // 开启事务
        try {
            $user = Db::table('tp_user u')
                ->leftJoin('tp_user_code c', 'c.user_id=u.id')
                ->field('u.id')
                ->where('u.username', '=', $data['username'])
                ->where('u.email', '=', $data['email'])
                ->where('c.code', '=', $data['code'])
                ->where('c.expire_time', '>', time())
                ->where('c.is_del', '=', 0)
                ->find();

            if (!is_null($user)) {
                $password = password_hash($data['password'], PASSWORD_DEFAULT);
                // 更新密码
                Db::table('tp_user')
                    ->where('username', '=', $data['username'])
                    ->where('email', '=', $data['email'])
                    ->update(['password' => $password]);
                // 将使用后的验证码标记删除，防止有限期内再次使用
                Db::table('tp_user_code')
                    ->where('user_id', '=', $user['id'])
                    ->where('code', '=', $data['code'])
                    ->update(['is_del' => 1]);
                // 提交事务
                Db::commit();

                return $this->resSuccess([], '重置密码成功');
            } else {
                return $this->resFail('验证码错误或已过期，请重试');
            }
        } catch (\Throwable $e) {
            $this->writeLog($e);
            Db::rollback(); // 异常回滚事务
            return $this->resFail('重置密码异常'.$e->getMessage());
        }
    }
}
