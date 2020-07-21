<?php

namespace app\member\controller;

use think\admin\Controller;

/**
 * 会员积分管理
 * Class MemberCredits
 * @package app\admin\controller
 */
class Credits extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    public $table = 'SystemMemberCredits';
    public $credits_type = ['' => '-- 全部状态 --', '0' => '减少', '1' => '增加'];
    public $credits_from = ['' => '-- 全部状态 --', 1 => '会员注册', 2 => '会员每天登录', 3 => '邀请注册', 4 => '写原创文章'];

    /**
     * 会员积分列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $this->title = '会员积分列表';
        $query = $this->_query($this->table)->like('username');
        $query->equal('credits_type')->equal('come_from')->timeBetween('create_at');
        // 列表排序并显示
        $query->order('id desc')->page();
    }

    /**
     * 会员积分配置
     * @auth true
     */
    public function config()
    {
        if ($this->request->isPost()) {
            foreach ($this->request->post() as $name => $value) sysconf($name, $value);
            $this->success('会员积分规则配置成功！');
        } else {
            return $this->fetch();
        }
    }
}