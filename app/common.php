<?php

use think\facade\View;

use cryption\Cryption;
use app\admin\service\ConfigService;
use DfaFilter\SensitiveHelper;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use think\admin\storage\LocalStorage;

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
function dealBadwords($content)
{
    if (!empty($content)) {
        $badwordConfig = ConfigService::instance()->getTypeConfigFromCache('badword');
        //系统默认的敏感词库
        $badwordFile = trim(app()->getConfigPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'badword.txt';
        $replaceStr = '';
        if (count($badwordConfig) > 0) {
            $configFile = explode('|', $badwordConfig['file']);
            if (count($configFile) > 0) {
                //判断文件是否存在
                $adapter = new Local(ROOT_PATH);
                $filesystem = new Filesystem($adapter);
                if ($filesystem->has($configFile[2])) {
                    $badwordFile = $configFile[2];
                    $replaceStr = $badwordConfig['replace'];
                }
            }
        }
        $wordPool = file_get_contents($badwordFile);
        $wordData = explode(',', $wordPool);
        $handle = SensitiveHelper::init()->setTree($wordData);//setTreeByFile()这个方法没有生效
        return $handle->replace($content, $replaceStr);
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

/**
 * 根据文件路径获取文件相关信息
 * @param $filepath
 * @return array
 */
function getFileInfoByFilepath($filepath)
{
    $fileData = [];
    if (is_string($filepath)) {
        $fileRes = new \SplFileInfo($filepath);
    }
    if ($fileRes->isFile()) {
        $fileData = [
            'filename' => $fileRes->getFilename(),
            'filepath' => $filepath,
            'filesize' => $fileRes->getSize(),
            'fileext' => $fileRes->getExtension(),
            'filemd5' => md5_file($fileRes),
        ];
    }
    return $fileData;
}


/**
 * 字符串截取，支持中文和其他编码
 * static
 * access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * return string
 */
function msubstr($str, $length, $start = 0, $suffix = true, $charset = "utf-8")
{
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
}

function downRemoteImg($path){
    //判断是否是https协议的资源
    if(strpos($path,'https') === 0){
        $pathmd5 = md5($path);
        $prefix = './upload/down/'.substr($pathmd5,0,2).'/';
        $file = $prefix.substr($pathmd5,2).'.'.pathinfo($path,PATHINFO_EXTENSION);
        $dir = pathinfo($file,PATHINFO_DIRNAME);
        !is_dir($dir) && @mkdir($dir,0755,true);
        $url = str_replace(" ","%20",$path);

        if(function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $temp = curl_exec($ch);
            if (@file_put_contents($file, $temp) && !curl_error($ch)) {
                $newFileInfo = [
                    'url' => substr($file,1),
                    'key' => $file,
                    'file' => getPublicPath().$file
                ];
            }
        }
    }else{
        $local = LocalStorage::instance();
        $newFileInfo = $local::down($path);
    }
    return $newFileInfo;
}


/**
 * sys_download_file('web服务器中的文件地址', 'test.jpg');
 * sys_download_file('远程文件地址', 'test.jpg', true);
 *
 * @param $path  件地址：针对当前服务器环境的相对或绝对地址
 * @param null $name 下载后的文件名（包含扩展名）
 * @param bool $isRemote 是否是远程文件（通过 url 无法获取文件扩展名的必传参数 name）
 * @param bool $isSSL 是否是HTTPS协议
 * @param string $proxy
 * @return bool true|false
 */
function sys_download_file($path, $name = null, $isRemote = false, $isSSL = false, $proxy = '') {

    $fileRelativePath = $path;
    $savedFileName = $name;
    if (!$savedFileName) {
        $file = pathinfo($path);
        if (!empty($file['extension'])) {
            $savedFileName = $file['basename'];
        } else {
            echo 'Extension get failed, parameter \'name\' is required!';
            return false;
        }
    }

    // 如果是远程文件，先下载到本地
    if ($isRemote) {
        $pathmd5 = md5($path);
        $prefix = './upload/down/'.substr($pathmd5,0,2).'/';
        $file = $prefix.substr($pathmd5,2).'.'.pathinfo($path,PATHINFO_EXTENSION);
        $dir = pathinfo($file,PATHINFO_DIRNAME);
        !is_dir($dir) && @mkdir($dir,0755,true);
        $url = str_replace(" ","%20",$path);

        if(function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $temp = curl_exec($ch);
            if (@file_put_contents($file, $temp) && !curl_error($ch)) {
                $fileRelativePath = $file;
            } else {
                return false;
            }
        }
    }
    // 执行下载
    echo $fileRelativePath;
    /*if (is_file($fileRelativePath)) {
        //header('Content-Description: File Transfer');
        header('Content-type: application/octet-stream');
        header('Content-Length:' . filesize($fileRelativePath));
        if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { // for IE
            header('Content-Disposition: attachment; filename="' . rawurlencode($savedFileName) . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $savedFileName . '"');
        }
        readfile($fileRelativePath);
        if ($isRemote) {
            unlink($fileRelativePath); // 删除下载远程文件时对应的临时文件
        }
        return true;
    } else {
        echo 'Invalid file: ' . $fileRelativePath;
        return false;
    }*/
}

/**
 * 判断是否为Linux环境
 * @return bool
 */
function isLinuxEnv()
{
    return strtoupper(PHP_OS) === 'LINUX' ? true : false;
}

/**
 * 获取项目的public目录
 * @return string
 */
function getPublicPath($public='public'){
    return app()->getRootPath().$public.DIRECTORY_SEPARATOR;
}
