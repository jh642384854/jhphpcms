<?php
namespace app\cms\controller;


use app\cms\service\CategoryService;
use app\cms\service\ModelService;
use app\cms\service\RecommendService;
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
        $this->formFields = $form->get($defaultData);
        $this->formValidator = $form->formValidator;
        $this->dependJS = $form->dependJS;
        $this->formData = $form->formData;
        $layuiJsModel = array_unique($form->layuiJsModule);
        $layuiJSinit = '';
        foreach ($layuiJsModel as $value){
            $layuiJSinit .= $value.' = layui.'.$value.',';
        }
        $this->layuiJsModels = implode('","',$layuiJsModel);
        $this->layuiJSinit = $layuiJSinit;
        $this->fetch();

/*        $form = new \form\Form([]);
        $setting = '{"tips":"\u8bf7\u9009\u62e9\u680f\u76eeID","width":"100","min":"1","is_multiple":"0","options_from":"moduledata","options_module":"category","length":"5","chartype":"tinyint"}';
        $res = $form->select('catid','',$setting);
        echo $res;
        echo $form->dependJS;*/

    }

    public function uploadtest()
    {
        /*$res = CategoryService::instance()->getAllCategoryTree();
        return json($res);*/
        $this->fetch();
    }


    public function attachs()
    {
        $rs['images'] = [
            ['thumb'=>'https://xzcustomer.cdn.bcebos.com./uploadfile/category/d9/48/18/58/5c49b26f4be9f.jpg','name'=>'ISgfKjpg','type'=>'jpg'],
            ['thumb'=>'https://xzcustomer.cdn.bcebos.com./uploadfile/category/d9/48/18/58/5c49b26f4be9f.jpg','name'=>'ISgfKjpg','type'=>'mp3'],
            ['thumb'=>'https://xzcustomer.cdn.bcebos.com./uploadfile/category/d9/48/18/58/5c49b26f4be9f.jpg','name'=>'ISgfKjpg','type'=>'doc'],
        ];
        $rs['count'] = 30;
        return json($rs);
    }

    protected function _form_filter()
    {

    }

}