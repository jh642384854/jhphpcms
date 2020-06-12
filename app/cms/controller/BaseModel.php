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
     * 绑定数据表
     * @var string
     */
    protected $table = 'cms_model_field';

    /**
     * 基础模型字段管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '基础模型字段管理';
        $this->list = $this->app->db->name($this->table)->where(['modelid' => 1])->select();
        $this->fetch();
    }
}