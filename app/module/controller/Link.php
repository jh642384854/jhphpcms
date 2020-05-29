<?php

namespace app\module\controller;

use think\admin\Controller;

/**
 * 友情链接管理
 * Class Link
 * @package app\content\controller
 */
class Link extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'moduleLink';

    /**
     * 友情链接列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '友情链接管理';
        $query = $this->_query($this->table)->like('name,url');
        $query->dateBetween('create_at');
        // 加载对应数据列表
        $this->type = input('type', 'all');
        if ($this->type === 'all') {
            $query->where(['is_deleted' => '0']);
        } elseif ($this->type = 'recycle') {
            $query->where(['is_deleted' => '1']);
        }
        // 列表排序并显示
        $query->order('sort desc,id desc')->page();
    }

    /**
     * 添加友情链接
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
     * 编辑友情链接
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
     * 恢复友情链接
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function recover()
    {
        $this->_applyFormToken();
        $this->_save($this->table, ['is_deleted' => intval(input('is_deleted'))]);
    }

    /**
     * 删除友情链接
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}