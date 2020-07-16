<?php

namespace app\admin\controller;

use think\admin\Controller;

/**
 * 会员通知管理
 * Class MemberNotice
 * @package app\admin\controller
 */
class MemberNotice extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    public $table = 'SystemMemberNotice';
    public $noticeType = ['' => '-- 全部状态 --', '0' => '指定用户', '1' => '指定用户组', '2' => '所有用户'];

    /**
     * 会员通知列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $this->title = '会员通知列表';
        $this->_query($this->table)->like('users')->equal('send_type')->timeBetween('create_at')->where(['is_deleted' => 0])->order('id desc')->page();
    }

    /**
     * 添加会员通知
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->_applyFormToken();
        $this->userGroups = $this->app->db->name('SystemMemberGroup')->column('id,title');
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑会员通知
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->_applyFormToken();
        $this->userGroups = $this->app->db->name('SystemMemberGroup')->column('id,title');
        $this->_form($this->table, 'form');
    }

    /**
     * 表单数据处理
     * @param array $vo
     * @throws \ReflectionException
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            $sendType = $vo['send_type'];
            if ($sendType == 1) {
                $vo['users'] = $vo['user_groupid'];
            } else if ($sendType == 2) {
                $vo['users'] = 'all';
            }
            unset($vo['user_groupid']);
            $vo['create_at'] = time();
        }
    }

    /**
     * 删除会员通知
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}