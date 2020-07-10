<?php
namespace app\module\controller;

use think\admin\Controller;

/**
 * 自定义表单管理
 * Class Diyform
 * @package app\module\controller
 */
class Diyform extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'module_diy_form';

    /**
     * 自定义表单列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '自定义表单列表';
        $this->_query($this->table)->order('id desc')->page();
    }

    /**
     * 添加自定义表单
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
     * 编辑自定义表单
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

    protected function _form_filter(&$data)
    {
        if($this->request->isPost()){
            if(!isset($data['id'])){
                $data['create_at'] = time();
            }
        }
    }
    
    /**
     * 删除自定义表单
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}