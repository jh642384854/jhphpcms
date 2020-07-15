<?php

namespace app\admin\controller;

use think\admin\Controller;

/**
 * 会员等级管理
 * Class MemberLevel
 * @package app\admin\controller
 */
class MemberLevel extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    public $table = 'SystemMemberLevel';

    /**
     * 会员等级列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $this->title = '会员等级列表';
        $this->_query($this->table)->where(['is_deleted' => 0])->order('id desc')->page();
    }

    /**
     * 添加会员等级
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
     * 编辑会员等级
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
     * 提交表单数据进行过滤处理
     * @param $vo
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            $map = [];
            if (!empty($vo['id'])) {
                $map[] = ['id', '<>', $vo['id']];
            }
            $map[] = ['name', '=', $vo['name']];
            $exits = $this->app->db->name($this->table)->field('id')->where($map)->find();
            if ($exits) {
                $this->error('级别名称重复，请更换级别名称');
            }
        }
    }

    /**
     * 删除会员等级
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}