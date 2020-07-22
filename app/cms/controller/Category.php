<?php

namespace app\cms\controller;

use app\cms\service\CategoryService;
use app\cms\service\ModelService;
use think\admin\Controller;

/**
 * 栏目管理
 * Class Category
 * @package app\cms\controller
 */
class Category extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'CmsCategory';

    /**
     * 文章栏目管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        if ($this->app->request->isPost() && $this->app->request->post('action') === 'sort') {
            $pk = 'id';
            $map = [$pk => $this->app->request->post($pk, 0)];
            $data = ['sort' => intval($this->app->request->post('sort', 0))];
            if ($this->app->db->name($this->table)->where($map)->update($data) !== false) {
                $this->success(lang('think_library_sort_success'), '');
            }
        } else {
            $action = input('action','','trim');
            if($action != '' && $action = 'getdata'){
                $categories = CategoryService::instance()->getAllCategoryFromCache();
                $models = ModelService::instance()->getAllModelsFromCache();
                $categoryData = [];
                if (count($categories) > 0) {
                    foreach ($categories as $key => $cate) {
                        $categoryData[$key] = $cate;
                        if (in_array($cate['modelid'], array_keys($models))) {
                            $categoryData[$key]['modelname'] = $models[$cate['modelid']]['name'];
                        } else if ($cate['modelid'] == 0) {
                            $categoryData[$key]['modelname'] = '单页模型';
                        } else {
                            $categoryData[$key]['modelname'] = '未知模型';
                        }
                    }
                }
                return json(['code'=>0,'msg'=>'','count'=>1,'data'=>array_values($categoryData)]);
            }else{
                $this->title = '栏目列表';
                $this->fetch();
            }
        }
    }

    /**
     * 添加栏目
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->title = '添加栏目';
        $this->_applyFormToken();
        $type = $this->request->request('type', 'category', 'trim');
        $this->type = $type;
        $categoryService = CategoryService::instance();
        $tpls = $categoryService->getCategoryTemplate();
        if ($type === 'page') {
            $this->categoryTpls = isset($tpls['page']) ? $tpls['page'] : [];
        } else {
            //①、获取模版列表
            $this->categoryTpls = isset($tpls['category']) ? $tpls['category'] : [];
            $this->listTpls = isset($tpls['list']) ? $tpls['list'] : [];
            $this->showTpls = isset($tpls['show']) ? $tpls['show'] : [];
            //②、获取模型列表
            $this->models = ModelService::instance()->getAllModelsFromCache();
        }
        $this->parentid = $this->request->param('pid', 0, 'intval');
        //③、获取栏目列表树形结构
        $this->categoriesTree = $categoryService->getCategoryTree($this->parentid);
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑栏目
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->title = '编辑栏目';
        $this->parentid = $this->request->param('pid', 0, 'intval');  //父栏目ID
        $this->id = $this->request->param('id', 0, 'intval');         //当前栏目ID
        $this->mid = $this->request->param('mid', 0, 'intval');       //当前栏目模型ID
        $this->_applyFormToken();
        $categoryService = CategoryService::instance();
        $tpls = $categoryService->getCategoryTemplate();
        if ($this->mid > 0) {
            $this->type = 'category';
            //①、获取模版列表
            $this->categoryTpls = isset($tpls['category']) ? $tpls['category'] : [];
            $this->listTpls = isset($tpls['list']) ? $tpls['list'] : [];
            $this->showTpls = isset($tpls['show']) ? $tpls['show'] : [];
            //②、获取模型列表
            $this->models = ModelService::instance()->getAllModelsFromCache();
        } else {
            $this->type = 'page';
            $this->categoryTpls = isset($tpls['page']) ? $tpls['page'] : [];
        }
        //③、获取栏目列表树形结构
        $this->categoriesTree = $categoryService->getCategoryTree($this->parentid, $this->id);
        $this->_form($this->table, 'form');
    }

    /**
     * 提交表单数据进行过滤处理
     * @param $vo
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            //如果是进行更新操作，就把模型修改的属性给删除掉
            $url_path = !empty($vo['url_path']) ? strtolower($vo['url_path']) : '';
            if (!empty($vo['usepy']) && strtolower($vo['usepy']) == 'on') {
                $url_path = $this->app->pinyin->abbr($vo['name']);
                $vo['url_path'] = $url_path;
            }
            $checkUrlpath = false;
            if ($url_path != '') {
                $checkUrlpath = true;
            }
            $map = [];
            if (!empty($vo['id'])) {
                unset($vo['modelid']);
                $map[] = ['id', '<>', $vo['id']];
            }
            if ($checkUrlpath) {
                $map[] = ['url_path', '=', $url_path];
                $exits = $this->app->db->name($this->table)->field('id')->where($map)->find();
                if ($exits) {
                    $this->error('路径地址重复，请更换路径地址');
                }
            }
        }
    }

    /**
     * 表单提交保存数据后执行的操作
     * @param boolean $result
     */
    protected function _form_result($result, $data)
    {
        if ($result !== false) {
            $newRes = true;
            //①、如果新创建的栏目
            $model = $this->app->db->name($this->table);
            if (!isset($data['id'])) {
                if (empty($data['parent_id'])) {
                    $updateData = ['path' => '0-' . $result];
                } else {
                    //这里不能写成$model的方式来调用，不然在执行下面的更新操作的时候，会带上这里的where()查询条件，那还导致下面的更新语句出问题
                    $parentPath = $this->app->db->name($this->table)->where(['id' => intval($data['parent_id'])])->value('path');
                    $updateData = ['path' => $parentPath . '-' . $result];
                }
                $model->where('id', '=', $result)->update($updateData);
            } else {
                //②、对已有的栏目进行修改
                $id = $data['id'];
                $parentId = intval($data['parent_id']);
                $oldCategory = $model->where(['id' => $id])->find();
                if (empty($parentId)) {
                    $newPath = '0-' . $id;
                } else {
                    $parentPath = $this->app->db->name($this->table)->where(['id' => intval($data['parent_id'])])->value('path');
                    if ($parentPath === false) {
                        $newPath = false;
                    } else {
                        $newPath = "$parentPath-$id";
                    }
                }
                if (empty($oldCategory) || empty($newPath)) {
                    $newRes = false;
                } else {
                    //1.更新本栏目的层级关系
                    $updateData['path'] = $newPath;
                    $model->where(['id' => $id])->update($updateData);
                    //2.处理该栏目的子栏目信息
                    $children = $this->app->db->name($this->table)->field('id,path')->where('path', 'like', $oldCategory['path'] . "-%")->select();
                    if (!$children->isEmpty()) {
                        foreach ($children as $child) {
                            $childPath = str_replace($oldCategory['path'] . '-', $newPath . '-', $child['path']);
                            $this->app->db->name($this->table)->where('id', $child['id'])->update(['path' => $childPath], ['id' => $child['id']]);
                        }
                    }
                }
            }
            //修正栏目某些字段的一些属性，并且更新栏目缓存
            CategoryService::instance()->repair();
            if ($newRes) {
                $location = 'javascript:history.back()';
                $this->success('恭喜, 栏目保存成功！', $location);
            } else {
                $this->error('栏目保存失败, 请稍候再试！');
            }
        } else {
            $this->error('栏目保存失败, 请稍候再试！');
        }
    }

    /**
     * 删除栏目
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }

    /**
     * 在执行真正删除前的操作
     * @param $query
     * @param $where
     */
    protected function _delete_filter($query, $where)
    {
        $id = $this->app->request->post('id', 0, 'intval');
        //①、判断记录是否存在
        $category = $this->app->db->name($this->table)->where(['id' => $id])->find();
        if ($category) {
            //②、判断是否有子栏目
            $hasSon = $this->app->db->name($this->table)->where(['parent_id' => $id])->count();
            if ($hasSon > 0) {
                $this->error('该分类有子栏目无法删除！');
            } else {
                //③、判断当前栏目下面是否有数据
                $modelData = ModelService::instance()->getAllModelsFromCache();
                $modelTable = $modelData[$category['modelid']]['tablename'];
                $articleCount = $this->app->db->table($modelTable)->where(['catid' => $id])->count();
                if ($articleCount > 0) {
                    $this->error('此栏目有文章无法删除!');
                }
            }
        } else {
            $this->error('数据错误，请重试！');
        }
    }

    /**
     * 在执行完删除操作之后的操作
     * @param $result
     */
    protected function _delete_result($result)
    {
        //更新栏目缓存
        CategoryService::instance()->repair();
        $this->success('恭喜, 栏目删除成功！');
    }

    /**
     * 设置栏目的状态(显示或隐藏)
     */
    public function status()
    {
        $this->_applyFormToken();
        $this->id = $this->request->param('id', 0, 'intval');         //当前栏目ID
        if ($this->id > 0) {
            $categoryService = CategoryService::instance();
            $categories = $categoryService->getAllCategoryFromCache();
            $categoryData = $categories[$this->id];
            if ($categoryData['status'] == 1) {
                $updateData = ['status' => 0];
            } else {
                $updateData = ['status' => 1];
            }
            if ($categoryService->updateCategoryStatus($updateData, ['id' => $this->id])) {
                $categoryService->updateCategoryCache();
                $this->success('恭喜, 栏目状态更新成功！');
            } else {
                $this->error('操作失败，请重试！');
            }
        } else {
            $this->error('参数有误，请重试！');
        }
    }

    /**
     * 更新栏目缓存
     * @auth true
     */
    public function upcache()
    {
        CategoryService::instance()->repair();
        $this->success('恭喜, 栏目缓存更新成功！');
    }
}