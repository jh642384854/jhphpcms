<?php
namespace app\cms\controller;

use app\cms\service\RecommendService;
use think\admin\Controller;
use app\cms\service\CategoryService;
use app\cms\service\ModelService;

/**
 * 推荐位管理
 * Class Recommend
 * @package app\cms\controller
 */
class Recommend extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'cms_recommend';

    /**
     * 推荐位列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '推荐位列表';
        $this->_query($this->table)->order('id desc')->page();
    }

    protected function _index_page_filter(&$data)
    {
        $categorys = CategoryService::instance()->getAllCategoryFromCache();
        $models = ModelService::instance()->getAllModelsFromCache();
        foreach ($data as &$vo) {
            $catname = '未知';
            if (isset($categorys[$vo['catid']])) {
                $catname = $categorys[$vo['catid']]['name'];
            }
            $vo['catname'] = $catname;
            $modelname = '未知';
            if (isset($models[$vo['modelid']])) {
                $modelname = $models[$vo['modelid']]['name'];
            }
            $vo['modelname'] = $modelname;
        }
    }

    /**
     * 添加推荐位
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->_applyFormToken();
        $this->models = ModelService::instance()->getAllModelsFromCache();
        $this->categoriesTree = CategoryService::instance()->getCategoryTree(0);
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑推荐位
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->_applyFormToken();
        $catid = $this->request->get('catid', 0, 'intval');
        $this->models = ModelService::instance()->getAllModelsFromCache();
        $this->categoriesTree = CategoryService::instance()->getCategoryTree($catid);
        $this->_form($this->table, 'form');
    }

    /**
     * 删除推荐位
     * @auth true
     */
    public function delete()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }

    protected function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            //判断别名是否存在  改进方案 可以用redis的布隆过滤器
            $query = $this->app->db->name($this->table)->where(['name' => $data['name']]);
            if (isset($data['id'])) {
                $query->where('id', '<>', $data['id']);
            }
            $exits = $query->find();
            if ($exits) {
                $this->error('当前推荐位已经存在，请更换新的名称');
            }
        }
    }

    /**
     * 更新栏目缓存
     * @auth true
     */
    public function upcache()
    {
        RecommendService::instance()->cacheAllRecommends();
        $this->success('恭喜, 栏目缓存更新成功！');
    }
}