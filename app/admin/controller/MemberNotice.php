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
    /**
     * 会员通知管理列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {

    }
}