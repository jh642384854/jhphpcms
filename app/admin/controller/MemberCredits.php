<?php
namespace app\admin\controller;

use think\admin\Controller;

/**
 * 会员积分管理
 * Class MemberCredits
 * @package app\admin\controller
 */
class MemberCredits extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    public $table = 'SystemMemberCredits';
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

    }
}