<?php

namespace app\cms\service;

use dir\Dir;
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
    protected $categorys;

    protected function initialize()
    {
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
        $categories = $this->model->order('sort ASC')->where(['status' => 1])->where(function (Query $query) use ($currentCid) {
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
     * 缓存所有的栏目信息
     */
    public function cacheAllCategory()
    {
        $fields = ['id', 'parent_id', 'name', 'sort', 'description', 'modelid', 'status', 'path', 'arrparentid', 'haschild', 'childids'];
        $categories = $this->app->db->name('CmsCategory')->field($fields)->order('sort ASC')->select();
        $newCategories = [];
        if (count($categories) > 0) {
            foreach ($categories as $cate) {
                $newCategories[$cate['id']] = $cate;
            }
        }
        cache(config('cache.usekey.category.key', 'AllCategory'), $newCategories);
        return $newCategories;
    }

    /**
     * 从缓存中得到所有的栏目
     * @return array|\Illuminate\Cache\CacheManager|mixed
     */
    public function getAllCategoryFromCache()
    {
        $data = cache(config('cache.usekey.category.key', 'AllCategory'));
        if (empty($data)) {
            $data = $this->cacheAllCategory();
        }
        return $data;
    }

    /**
     * 获取所有栏目，并构建为树形结构
     * @param int $selectId
     * @param bool $excludePage 是否排除单页模型
     * @return array
     */
    public function getAllCategoryTree($selectId = 0,$excludePage = false)
    {
        $map[] = ['status','=',1];
        //排除单页栏目
        if($excludePage){
            array_push($map,['modelid','<>',0]);
        }
        $categories = $this->model->field('id,name,parent_id')->order('sort ASC')->where($map)->select();
        $tree = new Tree();
        $newCategories = [];
        foreach ($categories as $item) {
            $item['selected'] = $selectId == $item['id'] ? "selected" : "";
            array_push($newCategories, $item);
        }
        $tree->init($newCategories);
        $treeStr = $tree->getTreeArray($selectId);
        return $treeStr;
    }

    /**
     * 更新栏目缓存
     */
    public function updateCategoryCache()
    {
        cache(config('cache.usekey.category.key', 'AllCategory'), null);
        $this->cacheAllCategory();
    }

    /**
     * 获取栏目的模版信息。根据前端模版页面的具体文件来获取
     */
    public function getCategoryTemplate()
    {
        $frontTplDir = $this->app->getBasePath() . 'index' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR;
        $dir = new Dir($frontTplDir);
        $allTpls = $dir->toArray();
        $templates = [];
        $catetory_prefix = 'category';
        $list_prefix = 'list';
        $show_prefix = 'show';
        $page_prefix = 'page';
        if (count($allTpls) > 0) {
            foreach ($allTpls as $tpl) {
                if (strncmp($tpl['filename'], $catetory_prefix, strlen($catetory_prefix)) === 0) {
                    $templates['category'][] = $tpl['filename'];
                } else if (strncmp($tpl['filename'], $list_prefix, strlen($list_prefix)) === 0) {
                    $templates['list'][] = $tpl['filename'];
                } else if (strncmp($tpl['filename'], $show_prefix, strlen($show_prefix)) === 0) {
                    $templates['show'][] = $tpl['filename'];
                } else if (strncmp($tpl['filename'], $page_prefix, strlen($page_prefix)) === 0) {
                    $templates['page'][] = $tpl['filename'];
                }
            }
        }
        return $templates;
    }


    /**
     * 更新栏目状态
     * @param $data
     * @param $map
     * @return mixed
     */
    public function updateCategoryStatus($data, $map)
    {
        return $this->model->where($map)->update($data);
    }

    /**
     * 修正栏目的某些特定字段值
     */
    public function repair()
    {
        //①、获取数据表中最新的数据
        $fields = ['id', 'parent_id', 'name', 'sort', 'description', 'modelid', 'status', 'arrparentid', 'haschild', 'childids'];
        $categories = $this->app->db->name('CmsCategory')->field($fields)->order('sort ASC')->select();
        foreach ($categories as $cate) {
            $this->categorys[$cate['id']] = $cate;
        }
        //②、逐条记录处理
        foreach ($this->categorys as $category) {
            $catid = $category['id'];
            $arrparentid = $this->get_arrparentid($catid);
            $arrchildid = $this->get_arrchildid($catid);
            $child = is_numeric($arrchildid) ? 0 : 1;
            if ($this->categorys[$catid]['arrparentid'] != $arrparentid || $this->categorys[$catid]['childids'] != $arrchildid || $this->categorys[$catid]['haschild'] != $child) {
                $updata = ['arrparentid' => $arrparentid, 'childids' => $arrchildid, 'haschild' => $child];
                $this->app->db->name('CmsCategory')->where(['id' => $catid])->update($updata);
            }
        }
        //③、重新更新栏目缓存
        $this->updateCategoryCache();
    }

    /**
     *
     * 获取父栏目ID列表
     * @param integer $catid 栏目ID
     * @param array $arrparentid 父目录ID
     * @param integer $n 查找的层次
     */
    private function get_arrparentid($catid, $arrparentid = '', $n = 1)
    {
        if ($n > 5 || !is_array($this->categorys) || !isset($this->categorys[$catid])) return false;
        $parentid = $this->categorys[$catid]['parent_id'];
        $arrparentid = $arrparentid ? $parentid . ',' . $arrparentid : $parentid;
        if ($parentid) {
            $arrparentid = $this->get_arrparentid($parentid, $arrparentid, ++$n);
        } else {
            $this->categorys[$catid]['arrparentid'] = $arrparentid;
        }
        $parentid = $this->categorys[$catid]['parent_id'];
        return $arrparentid;
    }

    /**
     * 获取子栏目ID列表
     * @param $catid
     * @return string
     */
    private function get_arrchildid($catid)
    {
        $arrchildid = $catid;
        if (is_array($this->categorys)) {
            foreach ($this->categorys as $id => $cat) {
                if ($cat['parent_id'] && $id != $catid && $cat['parent_id'] == $catid) {
                    $arrchildid .= ',' . $this->get_arrchildid($id);
                }
            }
        }
        return $arrchildid;
    }
}