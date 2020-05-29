<?php

namespace app\cms\controller;

use app\cms\service\CategoryService;
use app\cms\service\ModelService;
use dir\Dir;
use think\admin\Controller;
use think\facade\Log;

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
        $this->title = '栏目列表';
        $this->data = json_encode($this->app->db->name($this->table)->field(['id', 'parent_id as pid', 'name', 'sort', 'description', 'modelid as modelname', 'status'])->select()->toArray());
        $this->fetch();
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
        $this->categoryTpls = [
            'category.html' => '文章栏目页',
            'category_picture.html' => '图片栏目页',
        ];
        $this->listTpls = [
            'list.html' => '文章列表页',
            'list_picture.html' => '图片列表页',
        ];
        $this->showTpls = [
            'show.html' => '文章内容页',
            'show_picture.html' => '图片内容页',
        ];
        $this->models = ModelService::instance()->getAllModels();
        $this->parentid = $this->request->param('pid', 0, 'intval');
        $this->categoriesTree = CategoryService::instance()->getCategoryTree($this->parentid);
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
        $this->_applyFormToken();
        $this->categoryTpls = [
            'category.html' => '文章栏目页',
            'category_picture.html' => '图片栏目页',
        ];
        $this->listTpls = [
            'list.html' => '文章列表页',
            'list_picture.html' => '图片列表页',
        ];
        $this->showTpls = [
            'show.html' => '文章内容页',
            'show_picture.html' => '图片内容页',
        ];
        $this->models = ModelService::instance()->getAllModels();
        $this->parentid = $this->request->param('pid', 0, 'intval');
        $this->id = $this->request->param('id', 0, 'intval');
        $this->categoriesTree = CategoryService::instance()->getCategoryTree($this->parentid, $this->id);
        $this->_form($this->table, 'form');
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
            if($newRes){
                $location = 'javascript:history.back()';
                $this->success('恭喜, 栏目保存成功！', $location);
            }else{
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


}