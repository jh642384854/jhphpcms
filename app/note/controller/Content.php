<?php
namespace app\note\controller;

use app\note\service\CategoryService;
use think\admin\Controller;

/**
 * 笔记内容管理
 * Class Content
 * @package app\note\controller
 */
class Content extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'noteContent';

    /**
     * 笔记内容列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '笔记列表';
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
            $this->categoryTree = json_encode(array_values(CategoryService::instance()->getAllCategoryTree(0)));
            $this->fetch();
        }
    }

    /**
     * 对列表数据进行二次处理
     * @param $data
     */
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
        }
    }

    /**
     * 添加笔记内容
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {

        $catid = input('catid','0','intval');
        if($catid > 0){
            $categoryService = CategoryService::instance();
            $cagetorys = $categoryService->getAllCategoryFromCache();
            $catpath = str_replace('-', ',', substr($cagetorys[$catid]['path'], 2));
            $this->vo = ['catpath'=>$catpath];
        }
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑笔记内容
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * @param $data
     * @throws \DfaFilter\Exceptions\PdsBusinessException
     * @throws \DfaFilter\Exceptions\PdsSystemException
     */
    protected function _form_filter(&$data)
    {
        if($this->request->isGet()){
            $this->categorys = json_encode(CategoryService::instance()->getAllCategoryTree(0));
        }else if ($this->request->isPost()){
            if (strrpos($data['catpath'], ',') > 0) {
                $catid = substr($data['catpath'], strrpos($data['catpath'], ',') + 1);
                $data['catid'] = $catid;
            }
            $data['tags'] = str_replace('，', ',', $data['tags']);
            $data['content'] = dealBadwords($data['content']);
            $data['create_at'] = time();
        }
    }

    /**
     * 删除笔记内容
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}