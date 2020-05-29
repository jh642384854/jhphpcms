<?php
namespace app\cms\controller;

use think\admin\Controller;

/**
 * 自定义表单
 * Class Diyform
 * @package app\cms\controller
 */
class Diyform extends Controller
{
    public function index()
    {
        $this->title='自定义表单';
        $this->fetch();
    }
}