<?php

namespace app\extension\controller;

use app\extension\service\CascaderService;
use think\admin\Controller;

/**
 * 级联菜单管理
 * Class Linkagemenu
 * @package app\extension\controller
 */
class Cascader extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'extension_cascader_menu';
    protected $menuDataTable = 'extension_cascader_menu_data';

    /**
     * 级联菜单类别列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '级联菜单列表';
        $this->_query($this->table)->order('id desc')->page();
    }

    /**
     * 添加级联菜单类别
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
     * 编辑级联菜单类别
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
     * 删除级联菜单类别
     * @auth true
     */
    public function delete()
    {
        $this->_applyFormToken();
        $id = $this->request->param('id', 0, 'intval');         //当前菜单ID
        //判断菜单是否存在
        $exits = $this->app->db->name($this->menuDataTable)->where(['typeid' => $id])->count();
        if ($exits) {
            $this->error('当前菜单有子菜单，请删除子菜单后在删除该类别');
        } else {
            $this->_delete($this->table);
        }
    }


    /**
     * 管理级联菜单子菜单
     * @auth true
     */
    public function submenus()
    {
        $this->title = '子菜单列表';
        $typeid = $this->app->request->get('typeid', 1, 'intval');
        $parent_id = $this->app->request->get('pid', 0, 'intval');
        if ($typeid > 0) {
            $query = $this->_query($this->menuDataTable)->where(['typeid' => $typeid]);
            if ($parent_id > 0) {
                $query->where(['parent_id' => $parent_id]);
                //获取当前菜单的父菜单ID
                $current_parent_data = $this->app->db->name($this->menuDataTable)->field('parent_id')->where(['id' => $parent_id])->find();
                $this->current_parent_id = $current_parent_data['parent_id'];
            }
            $this->typeid = $typeid;
            $this->parent_id = $parent_id;
            $query->order('id asc')->page();
        }
    }

    /**
     * 添加级联菜单子菜单
     * @auth true
     */
    public function addSub()
    {
        $this->_applyFormToken();
        $cascaderService = CascaderService::instance();
        $this->parentid = $this->request->param('pid', 0, 'intval');  //父菜单ID
        $this->id = $this->request->param('id', 0, 'intval');         //当前菜单ID
        $this->typeid = $this->request->param('typeid', 0, 'intval'); //当前类别ID
        $this->cascaderTree = $cascaderService->getCascaderTree($this->parentid, $this->id, $this->typeid);
        $this->_form($this->menuDataTable, 'subform');
    }

    /**
     * 编辑级联菜单子菜单
     * @auth true
     */
    public function editSub()
    {
        $this->_applyFormToken();
        $cascaderService = CascaderService::instance();
        $this->parentid = $this->request->param('pid', 0, 'intval');  //父菜单ID
        $this->id = $this->request->param('id', 0, 'intval');         //当前菜单ID
        $this->typeid = $this->request->param('typeid', 0, 'intval'); //当前类别ID
        $this->cascaderTree = $cascaderService->getCascaderTree($this->parentid, $this->id, $this->typeid);
        $this->_form($this->menuDataTable, 'subform');
    }

    protected function _form_filter(&$data)
    {
        if ($this->app->request->isPost()) {
            $name = $data['name'];
            $names = explode("\r\n", trim($name));
            //如果是批量添加数据
            if (count($names) > 1) {
                $menus = [];
                foreach ($names as $val) {
                    $menus[] = ['typeid' => $data['typeid'], 'parent_id' => $data['parent_id'], 'name' => $val, 'status' => $data['status']];
                }
                $this->app->db->name($this->menuDataTable)->insertAll($menus);
                $this->success('添加成功');
            }
        }
    }

    /**
     * 删除级联菜单子菜单
     * @auth true
     */
    public function deleteSub()
    {
        $id = $this->request->param('id', 0, 'intval');         //当前菜单ID
        //判断菜单是否存在
        $exits = $this->app->db->name($this->menuDataTable)->where(['id' => $id])->find();
        if ($exits) {
            //判断是否有子菜单
            $count = $this->app->db->name($this->menuDataTable)->where(['parent_id' => $id])->count();
            if ($count > 0) {
                $this->error('当前菜单有子菜单，请删除子菜单后在删除该菜单');
            } else {
                $this->_delete($this->menuDataTable);
            }
        } else {
            $this->error('数据不存在，删除失败');
        }
    }

    /**
     * 更新级联菜单缓存
     * @auth true
     */
    public function cache()
    {
        $id = $this->request->param('id', 0, 'intval');         //当前菜单ID
        CascaderService::instance()->cacheCascaderTree($id);
        $this->success('缓存更新成功');
    }
}