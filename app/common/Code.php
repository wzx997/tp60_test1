<?php


namespace app\common;


/**
 * 获取验证码相关的类
 * Class Code
 * @package app\common
 */
class Code
{
    /**
     * 获取验证码，默认是6位验证码，包含大写字母、小写字母、数字
     * @param int $num 掩码码长度，默认是6位，不能小于1，不能大于62
     * @return string 验证码
     */
    public static function getCode($num = 6)
    {
        // 初始化数组a-z A-Z 0-9
        $arr = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
            'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u',
            'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F',
            'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q',
            'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1',
            '2', '3', '4', '5', '6', '7', '8', '9',];

        // 验证码最多62位，最少1位，否则设置为6位
        if ($num < 0 or $num > 62) {
            $num = 6;
        }

        $res_str = '';
        $key_arr = array_rand($arr, $num); // 该方法只能获取键，还需要进一步转化

        if (!is_array($key_arr)) { // 如果不是数组，则转为数组，当num=1的时候返回值就不是一个数组
            $key_arr = array($key_arr);
        }

        foreach ($key_arr as $value) { // 根据随机键从数组中取出随机值
            $res_str .= $arr[$value];
        }
        return $res_str;
    }
}