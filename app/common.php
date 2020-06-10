<?php

use think\facade\View;

use cryption\Cryption;
use app\admin\service\ConfigService;
use DfaFilter\SensitiveHelper;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
/**
 * 错误信息反馈共页面
 * @param $msg
 * @return string
 */
//TODO 这个方法暂时没有用
function errorHandler($msg)
{
    return View::fetch(app()->getBasePath() . 'admin/view/error/error.html');
}

/**
 * 利用phpmailer进行邮件发送
 * @param $toUser
 * @param $content
 */
function sendEmail($toUser, $content = '', $subject = 'test')
{
    //获取邮件配置信息
    $smtpConfig = ConfigService::instance()->getTypeConfigFromCache('smtp');
    //加密解密处理
    $cryption = new Cryption(config('constant.CryptionKey'));
    $mail = new \PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = $smtpConfig['server'];
    $mail->Port = $smtpConfig['port'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtpConfig['senduser'];
    $mail->Password = $cryption->decrypt($smtpConfig['passwd']);
    // 设置发件人信息，如邮件格式说明中的发件人，这里会显示为Mailer(xxxx@163.com），Mailer是当做名字显示
    $mail->setFrom($smtpConfig['senduser'], sysconf('base.site_name'));
    // 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
    //$mail->addReplyTo('replyto@example.com', 'First Last');
    // 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)
    $mail->addAddress($toUser);
    // 邮件标题
    $mail->Subject = $subject;
    $mail->Body = $content;
    //$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
    // 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用
    //$mail->AltBody = 'AltBody';
    // 添加附件
    //$mail->addAttachment('images/phpmailer_mini.png');

    if (!$mail->send()) {
        sysoplog(config('log.typeText.email'), '向' . $toUser . '发送邮件失败,邮件内容：' . $content . ',失败原因：' . $mail->ErrorInfo);
        return false;
    } else {
        sysoplog(config('log.typeText.email'), '向' . $toUser . '发送邮件成功,邮件内容：' . $content);
        return true;
    }
}

/**
 * 敏感词过滤处理
 * @param $content
 * @return mixed
 * @throws \DfaFilter\Exceptions\PdsBusinessException
 * @throws \DfaFilter\Exceptions\PdsSystemException
 */
function dealBadwords($content){
    if(!empty($content)){
        $badwordConfig = ConfigService::instance()->getTypeConfigFromCache('badword');
        //系统默认的敏感词库
        $badwordFile = trim(app()->getConfigPath(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'badword.txt';
        $replaceStr = '';
        if(count($badwordConfig)>0){
            $configFile = explode('|',$badwordConfig['file']);
            if(count($configFile)>0){
                //判断文件是否存在
                $adapter = new Local(ROOT_PATH);
                $filesystem = new Filesystem($adapter);
                if($filesystem->has($configFile[2])){
                    $badwordFile = $configFile[2];
                    $replaceStr = $badwordConfig['replace'];
                }
            }
        }
        $wordPool = file_get_contents($badwordFile);
        $wordData = explode(',', $wordPool);
        $handle = SensitiveHelper::init()->setTree($wordData);//setTreeByFile()这个方法没有生效
        return $handle->replace($content,$replaceStr);
    }
}

/**
 * 自定义字段里面渲染字段设置的额外配置信息
 * @param $type 什么字段类型
 * @param string $default 默认的值
 * @return string
 */
function rendFieldOptions($type, $default = '')
{
    $data = config('constant.modelFieldType')[$type]['field_list'];
    if ($default == '') {
        $default = $data[0];
    }
    if (count($data) > 2) {
        $str = '<select name="setting[chartype]" class="layui-select" lay-filter="fieldtype_filter"  lay-search required>';
    } else {
        $str = '';
    }
    //如果模型字段可选的只要一个数据库字段可选，就直接返回一个隐藏域
    if (count($data) == 1) {
        $str .= '<input type="hidden" name="setting[chartype]" value="' . $data[0] . '" />';
    } else {
        foreach ($data as $val) {
            //如果模型字段可选的数据字段超过2种选择，使用select元素来渲染，如果等于2种选择，则使用radio来进行渲染。
            if (count($data) > 2) {
                if ($default == $val) {
                    $str .= '<option selected value="' . $val . '">' . $val . '</option>';
                } else {
                    $str .= '<option value="' . $val . '">' . $val . '</option>';
                }
            } else if (count($data) == 2) {
                if ($default == $val) {
                    $str .= '<input type="radio" name="setting[chartype]" checked value="' . $val . '" title="' . $val . '">';
                } else {
                    $str .= '<input type="radio" name="setting[chartype]" value="' . $val . '" title="' . $val . '">';
                }
            }
        }
    }
    if (count($data) > 2) {
        $str .= '</select>';
    }
    return $str;
}

