<?php

namespace app\cms\controller;

use think\admin\Controller;

/**
 * 碎片管理
 * Class Block
 * @package app\cms\controller
 */
class Block extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'cms_block';

    /**
     * 碎片列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '碎片列表';
        $this->_query($this->table)->where(['is_deleted' => 0])->order('id desc')->page();
    }

    /**
     * 添加碎片
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->title = '添加碎片';
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑碎片
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->title = '编辑碎片';
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            //判断别名是否存在  改进方案 可以用redis的布隆过滤器
            $query = $this->app->db->name($this->table)->where(['alias' => $data['alias'],'is_deleted'=>0]);
            if (isset($data['id'])) {
                $query->where('id', '<>', $data['id']);
            }
            $exits = $query->find();
            if ($exits) {
                $this->error('当前别名已经存在，请更换新的别名');
            } else {
                $data['content'] = dealBadwords($data['content_' . $data['type']]);
                $data['code'] = '{jhphpcms:block name="' . $data['alias'] . '" /}';
                unset($data['content_richtext'], $data['content_text'], $data['content_image']);
            }
        } else {
            if (isset($data['id'])) {
                $data['content_' . $data['type']] = $data['content'];
            }
        }
    }

    protected function _form_result($result, $data)
    {
        if ($result !== false) {
            $location = 'javascript:history.back()';
            $this->success('恭喜, 碎片保存成功！', $location);
        }
    }

    /**
     * 删除碎片
     * @auth true
     */
    public function delete()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}