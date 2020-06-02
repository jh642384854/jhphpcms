<?php
namespace app\cms\controller;


use app\cms\service\CategoryService;
use app\cms\service\ModelService;
use think\admin\Controller;
use tree\Tree;

/**
 * 内容管理
 * Class Content
 * @package app\cms\controller
 */
class Content extends Controller
{
    /**
     * 文章列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title='文章列表';
        if($this->app->request->isGet() && $this->app->request->getInput('tree')){
            $tree = new Tree();
            $cagetorys = CategoryService::instance()->getAllCategoryFromCache();
            $tree->init($cagetorys);
            return $tree->getTreeArray(0);
        }
        $mid= $this->request->param('mid');  //父栏目ID
        $this->models = ModelService::instance()->getAllModelsFromCache();
        if(empty($mid)){
            $this->defaultModel = current($this->models);
        }else{
            $this->defaultModel = $this->models[$mid];
        }
        $query = $this->_query($this->defaultModel['tablename'])->like('title');
        $query->equal('catid')->dateBetween('create_at');
        // 列表排序并显示
        $query->order('sort desc,id desc')->page();
    }

    public function add()
    {
        $this->title = '添加文章';
        $this->_applyFormToken();
        $this->_form('cms_article', 'form');
    }

    public function jhtest()
    {
        $form = new \form\Form();
        //text类型
        /*$id = 123;
        $field = $this->app->db->name('cms_model_field')->where(['id'=>$id])->find();
        $textStr = $form->text($field['field'],'',$field['setting']);
        echo $textStr;*/

        //textarea类型
        /*
        $id = 125;
        $field = $this->app->db->name('cms_model_field')->where(['id'=>$id])->find();

        $textStr = $form->textarea($field['field'],'465',$field['setting']);
        echo $textStr;
        */
        //数值类型  暂时就沿用text类型

        //select类型(简单版，支持多选)
        /*
        $id = 128;
        $field = $this->app->db->name('cms_model_field')->where(['id'=>$id])->find();

        $textStr = $form->select($field['field'],3,$field['setting']);
        echo $textStr;
        */
        //checkbox类型
/*
        $id = 129;
        $field = $this->app->db->name('cms_model_field')->where(['id'=>$id])->find();

        $textStr = $form->checkbox($field['field'],3,$field['setting']);
        echo $textStr;*/


        $this->fetch();
    }

    protected function _form_filter()
    {

    }

}