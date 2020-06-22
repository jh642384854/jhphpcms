<?php

namespace app\cms\controller;

use app\cms\service\CategoryService;
use app\cms\service\PosidService;
use app\cms\service\TagService;
use think\admin\Controller;

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
        $this->title = '文章列表';
        if ($this->app->request->isGet() && input('action') === 'getdata') {
            $query = $this->_query($this->table)->where(['is_deleted' => 0])->like('title');
            $categoryservice = CategoryService::instance();
            $cateDatas = $categoryservice->getAllCategoryFromCache();
            $catid = input('catid');
            if (!empty($catid) && $catid > 0) {
                $category = $cateDatas[$catid];
                if ($category['haschild']) {
                    $query->whereIn('catid', $category['childids']);
                } else {
                    $query->equal('catid');
                }
            }
            $query->dateBetween('create_at');
            $data = $query->order('id desc')->page(true, false);
            return json(['code' => 0,'catid'=>$catid, 'msg' => '获取数据成功', 'count' => $data['page']['total'], 'data' => $data['list']]);
        } else {
            $this->categoryTree = json_encode(array_values(CategoryService::instance()->getAllCategoryTree(0,true)));
            $this->fetch();
        }
    }

    //对列表数据进行二次处理
    protected function _index_page_filter(&$data)
    {
        $categorys = CategoryService::instance()->getAllCategoryFromCache();
        foreach ($data as &$vo) {
            $catname = '未知';
            if (isset($categorys[$vo['catid']])) {
                $catname = $categorys[$vo['catid']]['name'];
            }
            $vo['catname'] = $catname;
            $vo['create_at'] = format_datetime($vo['create_at']);
            if ($vo['posids'] != '') {
                $vo['title'] = $vo['title'] . ' <span class="layui-badge">推荐</span>';
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
            $catid = $this->request->get('catid', 0, 'intval');
            if ($catid > 0) {
                if (isset($cagetorys[$catid])) {
                    $this->_applyFormToken();
                    $catdata = $cagetorys[$catid];
                    $this->allFields($catid, $catdata['modelid'], ['catid' => str_replace('-', ',', substr($catdata['path'], 2))]);

                } else {
                    $this->redirect(url('index'));
                }
            } else {
                $this->redirect(url('index'));
            }
        }
        $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            $categoryService = CategoryService::instance();
            $cagetorys = $categoryService->getAllCategoryFromCache();
            $catid = $data['catid'];
            if (strrpos($data['catid'], ',') > 0) {
                $catid = substr($data['catid'], strrpos($data['catid'], ',') + 1);
                $data['catid'] = $catid;
            }
            $catdata = $cagetorys[$catid];
            $data['modelid'] = $catdata['modelid'];
            $data['tags'] = str_replace('，', ',', $data['tags']);
            $data['content'] = dealBadwords($data['content']);
            $data['create_at'] = strtotime($data['create_at']);
        }
    }

    /**
     * 保存表单后处理的逻辑
     * @param $result
     * @param $data
     */
    protected function _form_result($result, $data)
    {
        if ($result) {
            $isUpdate = false;
            $aid = $result;
            if (isset($data['id']) && intval($data['id']) > 0) {
                $isUpdate = true;
                $aid = $data['id'];
            }
            //处理tag标签
            if ($data['tags'] != '') {
                $this->doTags($data['tags'], $aid, $isUpdate);
            }
            //处理推荐位
            if ($data['posids'] != '') {
                $this->doPosid($data['catid'], $data['posids'], $aid, $isUpdate);
            }
        }
    }

    /**
     * 处理标签
     */
    private function doTags($tags, $articleid, $isUpdate = false)
    {
        return TagService::instance()->addTags($tags, $articleid, $isUpdate);
    }

    /**
     * 处理推荐位
     */
    private function doPosid($catid, $posids, $articleid, $isUpdate = false)
    {
        return PosidService::instance()->bindPosidWithArticle($catid, $posids, $articleid, $isUpdate);
    }

    /**
     * 根据指定的模型来获取模型的所有字段信息
     * @param $catid  栏目ID
     * @param $modelid 模型ID
     * @param array $defaultData
     */
    private function allFields($catid, $modelid, $defaultData = [])
    {
        //基础模型的字段
        $baseFields = $this->app->db->name('cms_model_field')->where(['modelid' => 1, 'isshow' => 1])->order('sort asc')->select();
        //当前模型的自定义字段
        $modelFields = [];
        if ($modelid != 1) {
            $modelFields = $this->app->db->name('cms_model_field')->where(['modelid' => $modelid, 'isshow' => 1])->order('sort asc')->select();
        }
        $allFiels = $baseFields->merge($modelFields);
        $form = new \form\Form($catid, $modelid, $allFiels);
        $this->formFields = $form->get($defaultData);
        $this->formValidator = $form->formValidator;
        $this->dependJS = $form->dependJS;
        $this->formData = $form->formData;
        $layuiJsModel = array_unique($form->layuiJsModule);
        $layuiJSinit = '';
        foreach ($layuiJsModel as $value) {
            $layuiJSinit .= $value . ' = layui.' . $value . ',';
        }
        $this->layuiJsModels = implode('","', $layuiJsModel);
        $this->layuiJSinit = $layuiJSinit;
    }

    /**
     * 修改文章
     * @auth true
     */
    public function edit()
    {
        $id = $this->request->request('id', 0, 'intval');
        if ($id > 0) {
            if ($this->app->request->isGet()) {
                $data = $this->app->db->name($this->table)->where(['is_deleted' => 0, 'id' => $id])->find();
                if ($data) {
                    $categoryService = CategoryService::instance();
                    $cagetorys = $categoryService->getAllCategoryFromCache();
                    if ($this->request->isGet()) {
                        $this->title = '修改文章';
                        $catid = $data['catid'];
                        if ($catid > 0) {
                            if (isset($cagetorys[$catid])) {
                                $this->_applyFormToken();
                                $catdata = $cagetorys[$catid];
                                $data['catid'] = str_replace('-', ',', substr($catdata['path'], 2));
                                $this->allFields($catid, $data['modelid'], $data);
                            } else {
                                $this->redirect(url('index'));
                            }
                        } else {
                            $this->redirect(url('index'));
                        }
                    }
                    $this->id = $id;
                }
            }
            $this->_form($this->table, 'form');
        }
    }

    /**
     * 删除文章
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}