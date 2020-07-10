<?php

namespace app\module\controller;

use think\admin\Controller;
/**
 * 在线留言管理
 * Class Message
 * @package app\module\controller
 */
class Message extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'module_message';

    /**
     * 留言列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '留言列表';
        $this->_query($this->table)->where(['is_deleted' => 0])->order('id desc')->page();
    }


    /**
     * 编辑留言
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->title = '编辑留言';
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 删除留言
     * @auth true
     */
    public function delete()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }


}