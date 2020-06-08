<?php
namespace app\extension\controller;

use think\admin\Controller;

/**
 * 数据库管理
 * Class Database
 * @package app\extension\controller
 */
class Database extends Controller
{
    public function index()
    {
        echo '数据库管理';
    }

    public function jhtest()
    {
        sendEmail("642384854@qq.com",'PHPMailer测试邮件发送');
        /*$smtpConfig = \app\admin\service\ConfigService::instance()->getTypeConfigFromCache('smtp');
        dump($smtpConfig);*/
    }
}