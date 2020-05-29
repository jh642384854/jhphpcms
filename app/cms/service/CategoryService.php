<?php
namespace app\cms\service;

use think\admin\Service;
use think\db\Query;
use tree\Tree;

/**
 * 栏目管理服务类
 * Class CategoryService
 * @package app\cms\service
 */
class CategoryService extends Service
{
    protected $model;
    protected function initialize(){
        $this->model = $this->app->db->name('CmsCategory');
    }

    /**
     * 获取所有栏目，并构建成为树形结构
     * @param int $selectId
     * @param int $currentCid
     * @return string
     */
    public function getCategoryTree($selectId = 0, $currentCid = 0)
    {
        $categories = $this->model->order('sort ASC')->where(['status'=>1])->where(function (Query $query) use ($currentCid){
            if(!empty($currentCid)){
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
        $str     = '<option value=\"{$id}\" {$selected}>{$spacer}{$name}</option>';
        $treeStr = $tree->getTree(0, $str);
        return $treeStr;
    }


    public function cacheAllCategory()
    {
        $categories = $this->model->order('sort ASC')->select();
        $newCategories = [];
        if (count($categories) > 0) {
            foreach ($categories as $cate){
                $newCategories[$cate['id']] = $cate;
            }
        }
        cache(config('cache.usekey.category.key','AllCategory'),$newCategories);
    }
}