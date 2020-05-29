<?php
namespace app\cms\controller;


use think\admin\Controller;
use app\cms\service\FieldService;
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
        $this->_form($this->table, 'form');
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
        $this->_form($this->table, 'form');
    }

    /**
     * 模型创建后执行的操作
     * @param boolean $result
     */
    protected function _form_result($result,$data)
    {
        if ($result !== false) {
            //如果新创建的模型，就要初始化模型的数据表
            if(!isset($data['id'])){
                $tablepre = $this->table_prefix;
                $fieldService = FieldService::instance();
                $fieldService->CreateMainTable($tablepre.$data['tablename']);
                $fieldService->CreateSubTable($tablepre.$data['tablename'].'_data');
                $fieldService->InitModelField($result);
            }
        } else {
            $this->error('模型保存失败, 请稍候再试！');
        }
    }
}