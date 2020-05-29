<?php
namespace app\cms\controller;

use think\admin\Controller;

/**
 * 基础模型管理
 * Class BaseModel
 * @package app\cms\controller
 */
class BaseModel extends Controller
{
    /**
     * 基础模型字段管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        echo '基础模型字段管理';
    }
}