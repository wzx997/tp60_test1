<?php


namespace app\controller\test;


use app\BaseController;
use think\facade\Filesystem;


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

    /**
     * 单文件上传测试
     * @return \think\response\Json
     */
    public function uploadFile()
    {
        $file = $this->request->file('file');

        $original_name = $file->getOriginalName(); // 获取原始文件名
        $file_ext = $file->getOriginalExtension(); // 获取原始后缀名

        //$save_name = Filesystem::putFile('', $file); // 不指定磁盘，存在runtime下面
        $save_name = Filesystem::disk('upload')->putFile('', $file); // 指定upload磁盘
        $save_name = str_replace('\\', '/', $save_name); // 反斜杠换成左斜杠，windows下存在这个问题
        //$path = Filesystem::getDiskConfig('upload', 'root'). '/'; // 获取文件存储路径

        if ($save_name) {
            return $this->resSuccess([
                'original_name' => $original_name, // 文件原始名字
                'file_ext' => $file_ext, // 文件原始后缀名
                //'path' => $path, // 文件路径
                'save_name' => $save_name, // 存储的文件名，与路径拼接即可得到文件的位置
            ], '上传成功');
        }

        return $this->resFail('上传失败');
    }
}