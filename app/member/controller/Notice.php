<?php

namespace app\member\controller;

use think\admin\Controller;

/**
 * 会员通知管理
 * Class MemberNotice
 * @package app\admin\controller
 */
class Notice extends Controller
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

    protected function _index_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            if($vo['send_type'] == 1){
                $memberGroup = $this->app->db->name('SystemMemberGroup')->where(['id'=>$vo['users']])->column('title');
                if($memberGroup){
                    $vo['users'] = $memberGroup[0];
                }else{
                    $vo['users'] = '未知会员组';
                }
            }elseif ($vo['send_type'] == 2){
                $vo['users'] = '所有会员';
            }
        }
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
        $this->title = '添加通知';
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
        $this->title = '编辑通知';
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
            if(empty($vo['id'])){
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
    }

    protected function _form_result($result)
    {
        if ($result) {
            $location = 'javascript:history.back()';
            $this->success('恭喜, 会员通知保存成功！', $location);
        } else {
            $this->error('会员通知保存失败，请重试！');
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

    /**
     * 查看会员已读通知
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function viewread()
    {
        $this->title = '查看会员已读通知';
        $noticeid = input('id',0,'intval');
        if($noticeid>0){
            $this->_query('system_member_notice_read')->where(['notice_id' => $noticeid])->order('id desc')->page();
        }else{
            $this->error('参数错误');
        }
    }
}