<?php
namespace app\cms\controller;


use think\admin\Controller;

/**
 * 评论管理
 * Class Comment
 * @package app\cms\controller
 */
class Comment extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'cms_comment';

    /**
     * 评论列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '评论列表';
        $this->_query($this->table)->where(['is_deleted' => 0])->order('id desc')->page();
    }


    /**
     * 编辑评论
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->title = '编辑评论';
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 删除评论
     * @auth true
     */
    public function delete()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}