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
        if($this->app->request->isGet() && input('action') === 'getdata'){
            $query = $this->_query($this->table)->like('title');
            $categoryservice = CategoryService::instance();
            $cateDatas = $categoryservice->getAllCategoryFromCache();
            $catid = input('catid');
            if(!empty($catid) && $catid > 0){
                $category = $cateDatas[$catid];
                if($category['haschild']){
                    $query->whereIn('catid',$category['childids']);
                }else{
                    $query->equal('catid');
                }
            }
            $query->dateBetween('create_at');
            $data = $query->order('id desc')->page(true,false);
            return json(['code'=>0,'msg'=>'获取数据成功','count'=>$data['page']['total'],'data'=>$data['list']]);
        }else{
            $this->categoryTree = json_encode(array_values(CategoryService::instance()->getAllCategoryTree()));
            $this->fetch();
        }
    }

    //对列表数据进行二次处理
    protected function _index_page_filter(&$data)
    {
        $categorys = CategoryService::instance()->getAllCategoryFromCache();
        foreach ($data as &$vo) {
            $catname = '未知';
            if(isset($categorys[$vo['catid']])){
                $catname = $categorys[$vo['catid']]['name'];
            }
            $vo['catname'] = $catname;
            $vo['create_at'] = format_datetime($vo['create_at']);
            if($vo['posids'] != ''){
                $vo['title'] = $vo['title'].' <span class="layui-badge">推荐</span>';
            }
        }
    }

    /**
     * 添加文章
     * @auth true
     */
    public function add()
    {
        $categoryService = CategoryService::instance();
        $cagetorys = $categoryService->getAllCategoryFromCache();
        if ($this->request->isGet()) {
            $this->title = '添加文章';
            $catid = $this->request->get('catid',0,'intval');
            if($catid>0){
                if(isset($cagetorys[$catid])){
                    $this->_applyFormToken();
                    $catdata = $cagetorys[$catid];
                    $this->allFields($catid,$catdata['modelid'],['catid'=>str_replace('-',',',substr($catdata['path'],2))]);

                }else{
                    $this->redirect(url('index'));
                }
            }else{
                $this->redirect(url('index'));
            }
        }
        $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$data)
    {
        if($this->request->isPost()){
            $categoryService = CategoryService::instance();
            $cagetorys = $categoryService->getAllCategoryFromCache();
            $catid = $data['catid'];
            if(strrpos($data['catid'],',')>0){
                $catid = substr($data['catid'],strrpos($data['catid'],',')+1);
                $data['catid'] = $catid;
            }
            $catdata = $cagetorys[$catid];
            $data['modelid'] = $catdata['modelid'];
            $data['tags'] = str_replace('，',',',$data['tags']);
            $data['content'] = dealBadwords($data['content']);
            $data['create_at'] = strtotime($data['create_at']);
        }
    }

    protected function _form_result($result,$data)
    {
        dump($result);
        dump($data);
        exit;
        if($result){
            //内容敏感词过滤处理
            if ($this->app->db->name($this->table)->insert($data) !== false) {
                $this->success('文章保存成功！', 'javascript:history.back()');
            } else {
                $this->error('文章保存失败，请稍候再试！');
            }
        }
    }

    /**
     * 处理标签
     */
    private function doTags($tag)
    {

    }

    /**
     * 处理推荐位
     */
    private function doPosid($posids)
    {

    }

    /**
     * 处理附件
     * @param $imgs
     */
    private function doAttach($imgs)
    {

    }

    /**
     * 根据指定的模型来获取模型的所有字段信息
     * @param $modelid 模型ID
     */
    private function allFields($catid,$modelid,$defaultData=[])
    {
        //基础模型的字段
        $baseFields = $this->app->db->name('cms_model_field')->where(['modelid'=>1,'isshow'=>1])->order('sort asc')->select();
        //当前模型的自定义字段
        $modelFields = [];
        if($modelid != 1){
            $modelFields = $this->app->db->name('cms_model_field')->where(['modelid'=>$modelid,'isshow'=>1])->order('sort asc')->select();
        }
        $allFiels = $baseFields->merge($modelFields);
        $form = new \form\Form($catid,$modelid,$allFiels);
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
        //$this->fetch();
        dump(array_values(CategoryService::instance()->getAllCategoryTree()));
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

}