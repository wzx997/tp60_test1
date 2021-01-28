<?php


namespace app\controller\test;


use app\BaseController;


class Test extends BaseController
{
    /**
     * 测试post路由
     */
    public function test2()
    {
        $data = $this->request->post();
        return $this->resSuccess($data, '测试post路由');
    }

    /**
     * 测试get路由
     */
    public function test3()
    {
        return $this->resSuccess([], '测试get路由');
    }

}