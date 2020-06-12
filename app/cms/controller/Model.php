<?php

namespace app\cms\controller;


use app\cms\service\ModelService;
use database\TableOpt;
use think\admin\Controller;
use app\cms\service\FieldService;
use app\cms\service\CategoryService;

/**
 * 模型管理
 * Class Model
 * @package app\cms\controller
 */
class Model extends Controller

{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'CmsModel';
    protected $table_prefix = 'cms_';
    protected $table_suffix = '_model';

    /**
     * 模型管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '模型管理';
        $this->_query($this->table)->order('id desc')->page();
    }

    /**
     * 添加模型
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->_applyFormToken();
        $tpls = CategoryService::instance()->getCategoryTemplate();
        $this->categoryTpls = isset($tpls['category']) ? $tpls['category'] : [];
        $this->listTpls = isset($tpls['list']) ? $tpls['list'] : [];
        $this->showTpls = isset($tpls['show']) ? $tpls['show'] : [];
        $this->_form($this->table, 'form');
    }

    protected function _form_filter($data)
    {
        if ($this->request->isPost()) {
            //判断表名是否存在  改进方案 可以用redis的布隆过滤器
            $query = $this->app->db->name($this->table)->where(['tablename' => $data['tablename']]);
            if (isset($data['id'])) {
                $query->where('id', '<>', $data['id']);
            }
            $exits = $query->find();
            if ($exits) {
                $this->error('当前模型表已经存在，请更换新的模型表名称');
            }
        }
    }

    /**
     * 编辑模型
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->_applyFormToken();
        $tpls = CategoryService::instance()->getCategoryTemplate();
        $this->categoryTpls = isset($tpls['category']) ? $tpls['category'] : [];
        $this->listTpls = isset($tpls['list']) ? $tpls['list'] : [];
        $this->showTpls = isset($tpls['show']) ? $tpls['show'] : [];
        $this->_form($this->table, 'form');
    }

    /**
     * 模型创建后执行的操作
     * @param boolean $result
     */
    protected function _form_result($result, $data)
    {
        if ($result !== false) {
            //如果新创建的模型，就要初始化模型的数据表
            if (!isset($data['id'])) {
                $tableOpt = new TableOpt();
                $tableOpt->createTable(config('database.modeltable.prefix') . $data['tablename'] . config('database.modeltable.suffix'), 'cms:' . $data['name']);
            }
            ModelService::instance()->cacheAllModels();
        } else {
            $this->error('模型保存失败, 请稍候再试！');
        }
    }

    /**
     * 更新模型缓存
     * @auth true
     */
    public function upcache()
    {
        ModelService::instance()->cacheAllModels();
        $this->success('模型缓存更新成功');
    }

    /**
     * 删除模型
     * @auth true
     */
    public function delete()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }

    /**
     * 删除的前置回调函数
     * 判断是否为基础模型(基础模型是不允许删除的)，判断模型是否存在，判断模型表中是否有数据
     */
    protected function _delete_filter()
    {
        $modelid = $this->request->post('id', 0, 'intval');
        $this->checkModel($modelid);
    }

    //删除后的回调
    protected function _delete_result($result)
    {
        if ($result) {
            $modelid = $this->request->post('id', 0, 'intval');
            if ($this->checkModel($modelid)) {
                $modelService = ModelService::instance();
                //①、删除模型的字段
                $modelService->deleteModelField($modelid);
                //②、删除模型表
                $models = $modelService->getAllModelsFromCache();
                $tableOpt = new TableOpt();
                $tableOpt->deleteTable(config('database.modeltable.prefix') . $models[$modelid]['tablename'] . config('database.modeltable.suffix'));
                //③、重新更新模型缓存数据
                $modelService->cacheAllModels();
            }
        }
    }

    /**
     * 校验模型
     * @param $modelid
     * @return bool
     */
    private function checkModel($modelid)
    {
        if ($modelid > 1) {
            $models = ModelService::instance()->getAllModelsFromCache();
            if (isset($models[$modelid])) {
                //判断模型表中是否有数据
                $hasData = $this->app->db->name(config('database.modeltable.prefix') . $models[$modelid]['tablename'] . config('database.modeltable.suffix'))->count();
                if ($hasData > 0) {
                    $this->error('该模型有数据，请删除模型数据后在删除该模型！');
                } else {
                    return true;
                }
            } else {
                $this->error('模型不存在，删除失败, 请稍候再试！');
            }
        } else {
            $this->error('删除失败, 请稍候再试！');
        }
    }
}