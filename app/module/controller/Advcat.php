<?php
namespace app\module\controller;


use think\admin\Controller;

/**
 * 广告位位管理
 * Class Advcat
 * @package app\module\controller
 */
class Advcat extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'moduleAdvcat';

    /**
     * 广告位列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '广告位管理';
        $this->_query($this->table)->order('id desc')->page();
    }
    
    /**
     * 添加广告位
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑广告位
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 永久删除数据
     * @auth true
     */
    public function delete()
    {
        $this->_applyFormToken();
        $result = $this->app->db->name($this->table)->whereIn('id', explode(',',  $this->app->request->post('id', null)))->delete();
        if ($result !== false) {
            $this->success(lang('think_library_delete_success'), '');
        } else {
            $this->error(lang('think_library_delete_error'));
        }
    }
}