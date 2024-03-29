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
     * 根据栏目ID来获取栏目信息
     * @param $catid
     * @return array|mixed
     */
    public function getCategoryByCatid($catid)
    {
        $categoryData = [];
        if ($catid && $catid > 0) {
            $allCacheCategory = $this->getAllCategoryFromCache();
            if (isset($allCacheCategory[$catid])) {
                $categoryData = $allCacheCategory[$catid];
            } else {
                $categoryData = $this->model->where(['id' => $catid])->find();
            }
        }
        return $categoryData;
    }

    /**
     * 根据urlpath来获取栏目信息
     * @param string $urlpath
     * @return array
     */
    public function getCategoryByUrlPath($urlpath = '')
    {
        $category = [];
        if ($urlpath != '') {
            $data = $this->model->where('url_path', '=', $urlpath)->find();
            if ($data) {
                $category = $data;
            }
        }
        return $category;
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
     * 缓存所有的栏目信息。缓存的key是栏目的id，方便直接通过栏目id来定位是哪个栏目
     */
    public function cacheAllCategory()
    {
        $fields = ['id', 'parent_id', 'name', 'url_path', 'link_url', 'sort', 'description', 'modelid', 'status', 'path', 'arrparentid', 'haschild', 'childids','create_at','seo_title','seo_keywords','seo_description','category_template','list_template','show_template'];
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
     * 从缓存中获取所有栏目数据，只不过这个数组的key是用栏目的url_path来构建。这个方便在前台页面通过这个url来定位是哪个栏目
     * @return array|\Illuminate\Cache\CacheManager|mixed
     */
    public function getAllCategoryWithPathFromCache()
    {
        $data = cache('AllCategoryWithPth');
        if (empty($data)) {
            $allCategories = $this->getAllCategoryFromCache();
            $newCategoriesWithPath = [];
            if (count($allCategories) > 0) {
                foreach ($allCategories as $cate) {
                    $path = getCatPath($cate['id']);
                    $newCategoriesWithPath[$path] = $cate;
                }
            }
            cache('AllCategoryWithPth', $newCategoriesWithPath);
            $data = $newCategoriesWithPath;
        }
        return $data;
    }

    /**
     * 获取所有栏目，并构建为树形结构
     * @param int $selectId
     * @param bool $excludePage 是否排除单页模型
     * @return array
     */
    public function getAllCategoryTree($selectId = 0, $excludePage = false)
    {
        $map[] = ['status', '=', 1];
        //排除单页栏目
        if ($excludePage) {
            array_push($map, ['modelid', '<>', 0]);
        }
        $categories = $this->model->field('id,name,parent_id')->order('sort ASC,id ASC')->where($map)->select();
        $tree = new Tree();
        $newCategories = [];
        foreach ($categories as &$item) {
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
        if($categories->count()>0){
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