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
     * 当前操作数据表
     * @var string
     */
    protected $table = 'cms_article';
    /**
     * 文章列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title='文章列表';
        if($this->app->request->isGet() && input('action') === 'tree'){
            $this->success('获取权限节点成功！', array_values(CategoryService::instance()->getAllCategoryFromCache()));
        }
        $query = $this->_query($this->table)->like('title');
        $query->equal('catid')->dateBetween('create_at');
        // 列表排序并显示
        $query->order('id desc')->page();
    }

    public function add()
    {
        $this->title = '添加文章';
        $catid = $this->request->get('catid',0,'intval');
        if($catid>0){
            $categoryService = CategoryService::instance();
            $cagetorys = $categoryService->getAllCategoryFromCache();
            if(isset($cagetorys[$catid])){
                $this->_applyFormToken();
                $catdata = $cagetorys[$catid];
                $this->allFields($catdata['modelid']);
                $this->_form('cms_article', 'form');
            }else{
                $this->redirect(url('index'));
            }
        }else{
            $this->redirect(url('index'));
        }

    }

    /**
     * 根据指定的模型来获取模型的所有字段信息
     * @param $modelid 模型ID
     */
    private function allFields($modelid,$defaultData=[])
    {
        //基础模型的字段
        $baseFields = $this->app->db->name('cms_model_field')->where(['modelid'=>1,'isshow'=>1])->order('sort asc')->select();
        //当前模型的自定义字段
        $modelFields = $this->app->db->name('cms_model_field')->where(['modelid'=>$modelid,'isshow'=>1])->order('sort asc')->select();
        $allFiels = $baseFields->merge($modelFields);
        $form = new \form\Form($allFiels);
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
    }

    public function jhtest()
    {
        $cagetorys = CategoryService::instance()->getAllCategoryFromCache();


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