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
        $query->order('id desc')->page();
    }

    public function add()
    {
        $this->title = '添加文章';
        $this->_applyFormToken();
        $this->_form('cms_article', 'form');
    }

    public function jhtest()
    {
        $modelid = 6;
        $modelFields = $this->app->db->name('cms_model_field')->where(['modelid'=>$modelid])->select();
        $form = new \form\Form($modelFields);
        $defaultData = [
            'news_title' => 465,
            'keywords' => '关键字1,关键字2'
        ];
        $this->formData = $form->get($defaultData);
        $this->formValidator = $form->formValidator;
        $this->dependJS = $form->dependJS;
        $this->fetch();

/*        $form = new \form\Form([]);
        $setting = '{"tips":"\u8bf7\u9009\u62e9\u65e5\u671f\u8303\u56f4","width":"100","css":"layui-input","datetime_min_max_isopen":"1","datetime_min":"2020-06-01","datetime_max":"2020-06-30","chartype":"varchar","length":"50"}';
        $res = $form->daterange('select_field','',$setting);
        //echo $res;
        echo $form->dependJS;*/

    }

    protected function _form_filter()
    {

    }

}