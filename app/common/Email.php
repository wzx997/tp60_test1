<?php


namespace app\common;


use PHPMailer\PHPMailer\PHPMailer;
/**
 * 公共类：邮件发送，固定使用stmp协议发送邮件。非单例设计模式
 * 该类提供两种方式发送邮件：
 * 方式一：调用静态方法sendEmail()，该方法不支持附件，调用需要做异常捕获
 * 方式二：链式方式调用，最后调用send()方法，调用需要做异常捕获
 * Class Email
 * @package app\common
 */
class Email
{
    /**
     * 培训选择，仅支持调试模式与字符集配置
     * @var array
     */
    public $options = [
        'debug'    => 0,
        'char_set' => 'utf-8'
    ];

    /**
     * 邮件实例对象
     * @var array
     */
    private $mail;

    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options); // 扩展配置

        $mail = new PHPMailer(true);
        $mail->isSMTP(); // 使用smtp鉴权方式发送邮件
        $mail->SMTPAuth   = true; // smtp需要鉴权 这个必须是true
        $mail->SMTPSecure = 'ssl';  // 设置使用ssl加密方式登录鉴权

        // 需要配置.env文件，里面配置例如邮箱名、密码等相关的变量
        $mail->SMTPDebug  = $this->options['debug']; // 是否开启调试模式，0不是，其他参考源码
        $mail->CharSet    = $this->options['char_set']; // 设置发送的邮件的编码
        $mail->Host       = env('email.host', ''); // 服务器地址
        $mail->Port       = env('email.port', ''); // 端口
        $mail->Username   = env('email.emailusername', ''); // 用户名
        $mail->Password   = env('email.emailpwd', ''); // 密码，授权码而不是邮箱密码
        $mail->From       = env('email.emailusername', ''); // 发送者

        $this->mail       = $mail; // 对象赋值
    }

    /**
     * 邮件收件人：单人 --> 'xxx@xx.com'；多人 --> ['xxx@xx.com', 'xxx@xx.com']
     * @param string|array $to 收件人，必传。单个收件人为字符串邮箱，多个收件人为一个索引数组，元素为字符串邮箱
     * @throws \PHPMailer\PHPMailer\Exception
     * @return $this 链式操作当前类实例对象
     */
    public function to($to) {
        if (is_array($to)) { // 添加收件人
            foreach ($to as $value) { // 多个收件人，数组形式
                $this->mail->addAddress($value);
            }
        } else { // 单个收件人
            $this->mail->addAddress($to);
        }
        return $this;
    }

    /**
     * 邮件主题，必传
     * @param string $subject
     * @return $this 链式操作当前类实例对象
     */
    public function subject(string $subject)
    {
        $this->mail->Subject = $subject;
        return $this;
    }

    /**
     * 邮件正文
     * @param string $body 邮件正文内容，支持HTML标签
     * @param bool $is_html 是否是HTML内容，为真可以解析html内容，为假无法解析
     * @return $this 链式操作当前类实例对象
     */
    public function message($body, $is_html = false) {
        $this->mail->isHTML($is_html); // 是否是html邮件
        $this->mail->Body = $body;
        return $this;
    }

    /**
     * 发送者名
     * @param string $from_name 发送者名
     * @return $this 链式操作当前类实例对象
     */
    public function fromName($from_name = '')
    {
        $this->mail->FromName = $from_name;
        return $this;
    }

    /**
     * 添加邮件抄送人：单人 --> 'xxx@xx.com'；多人 --> ['xxx@xx.com', 'xxx@xx.com']
     * @param string|array $cc 默认空数组。单个抄送人为字符串邮箱，多个抄送人为一个索引数组，元素为字符串邮箱
     * @return $this 链式操作当前类实例对象
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function addCC($cc = [])
    {
        if (!empty($cc)) { // 添加抄送人
            if (is_array($cc)) { //多个收件人
                foreach ($cc as $value) {
                    $this->mail->addCC($value);
                }
            } else {
                $this->mail->addCC($cc);
            }
        }
        return $this;
    }

    /**
     * 添加邮件附件，多个附件请多次调用
     * @param string $path 附件路径
     * @param string $name 附件文件名
     * @return $this 链式操作当前类实例对象
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function addAttachment($path, $name = '') {
        $this->mail->addAttachment($path, $name);
        return $this;
    }

    /**
     * 发送邮件：凡是采用链式方法调用的方式，必须做异常捕获，否则异常发送将会导致程序终止
     * @return bool 发送结果。为真发送成功，为加发送失败
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send()
    {
        return $this->mail->send();
    }

    /**
     *     简易邮件发送：该方法为简易邮件发送方法，不允许添加附件，
     * 采用静态方法调用，添加附件请使用实例的链式方式调用，调用该方法需要做异常捕获
     * @param string|array $to 收件人，必传。单个收件人为字符串邮箱，多个收件人为一个索引数组，元素为字符串邮箱
     * @param string $subject  邮件主题，必传
     * @param string $body  邮件内容，必传。支持HTML字符串
     * @param string|array $cc 抄送人，选传，默认空数组。单个抄送人为字符串邮箱，多个抄送人为一个索引数组，元素为字符串邮箱
     * @param string $from_name 发件人名字，选传，默认空串
     * @param bool $is_html 是否是html内容，选传，默认为假。为假不是，为真是
     * @return bool 发送结果，为真表示发送成功，为假发送失败
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function sendEmail($to, string $subject, string $body, $cc = [], $from_name = '', $is_html = false)
    {
        $mail = new Email();

        return $mail->to($to)
            ->subject($subject)
            ->message($body, $is_html)
            ->fromName($from_name)
            ->addCC($cc)
            ->send();
    }
}
