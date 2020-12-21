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

    /**
     * 用户列表查询接口
     * @return \think\response\Json
     */
    public function getUserList()
    {
        $username = $this->request->post('username', ''); // 用户名模糊匹配
        $start_time = $this->request->post('start_time', ''); // 注册开始时间
        $end_time = $this->request->post('end_time', ''); // 注册结束字节
        $page_num = $this->request->post('page_num', 1); // 分页第几页，默认第一页
        $page_size = $this->request->post('page_size', 10); // 每页数据量，默认10条
        $sort_key = $this->request->post('sort_key', 'id'); // 排序字段，默认id
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

        return $this->resSuccess($users);
    }
}
