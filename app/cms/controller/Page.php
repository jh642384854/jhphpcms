<?php
namespace app\cms\controller;


use app\cms\service\CategoryService;
use think\admin\Controller;

/**
 * 单页管理
 * Class Page
 * @package app\cms\controller
 */
class Page extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'cms_page';

    /**
     * 单页列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '单页列表';
        $this->_query($this->table)->order('id desc')->page();
    }

    protected function _index_page_filter(&$data)
    {
        $categorys = CategoryService::instance()->getAllCategoryFromCache();
        foreach ($data as &$vo) {
            $catname = '未知';
            if(isset($categorys[$vo['catid']])){
                $catname = $categorys[$vo['catid']]['name'];
            }
            $vo['catname'] = $catname;
        }
    }
    /**
     * 添加/编辑单页
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->title = '编辑单页';
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    protected function _form_result($result, $data)
    {
        if ($result !== false) {
            $location = 'javascript:history.back()';
            $this->success('恭喜, 单页保存成功！', $location);
        }
    }
}