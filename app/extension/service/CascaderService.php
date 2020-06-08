<?php

namespace app\extension\service;

use think\admin\Service;
use think\db\Query;
use tree\Tree;

/**
 * 级联菜单操作服务类
 * Class CascaderService
 * @package app\extension\service
 */
class CascaderService extends Service
{
    protected $model;
    protected $cascaders;

    protected function initialize()
    {
        $this->model = $this->app->db->name('extension_cascader_menu_data');
    }

    /**
     * 获取所有栏目，并构建成为树形结构
     * @param int $selectId
     * @param int $currentCid
     * @return string
     */
    public function getCascaderTree($selectId = 0, $currentCid = 0, $typeid = 1)
    {
        $categories = $this->model->order('sort ASC')->where(['status' => 1, 'typeid' => $typeid])->where(function (Query $query) use ($currentCid) {
            if (!empty($currentCid)) {
                $query->where('id', '<>', $currentCid);
            }
        })->select();

        $tree = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        $newCategories = [];
        foreach ($categories as $item) {
            $item['selected'] = $selectId == $item['id'] ? "selected" : "";
            array_push($newCategories, $item);
        }
        $tree->init($newCategories);
        $str = '<option value=\"{$id}\" {$selected}>{$spacer}{$name}</option>';
        $treeStr = $tree->getTree(0, $str);
        return $treeStr;
    }

    /**
     * 缓存指定的级联菜单数据(成树状结构)
     * @param $id 级联菜单类别ID
     */
    public function cacheCascaderTree($id)
    {
        //清除原有数据
        cache(config('cache.usekey.cascader.key' . '_' . $id), null);
        $selectId = 0;
        $categories = $this->model->order('sort ASC')->where(['status' => 1, 'typeid' => $id])->select();
        $tree = new Tree();
        $newCategories = [];
        foreach ($categories as $item) {
            $item['selected'] = $selectId == $item['id'] ? "selected" : "";
            array_push($newCategories, $item);
        }
        $tree->init($newCategories);
        $treeData = $tree->getTreeArray($selectId);
        //重新设置缓存数据
        cache(config('cache.usekey.cascader.key' . '_' . $id), $treeData);
    }
}