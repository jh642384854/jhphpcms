<?php

namespace app\index\controller;

use app\index\service\ArticleService;
use app\cms\service\CategoryService;
use think\facade\Db;

class Content extends Base
{
    private $allCategories;
    protected $table = 'cms_article';

    protected function initialize()
    {
        $this->allCategories = CategoryService::instance()->getAllCategoryFromCache();
    }

    /**
     * 列表页
     */
    public function index($name,$page=1)
    {
        $allCategoriesWithPath = CategoryService::instance()->getAllCategoryWithPathFromCache();
        if (in_array($name, array_keys($allCategoriesWithPath))) {
            /*//构建查询对象
            $query = $this->_query($this->table)->where(['is_deleted' => 0]);
            //获取栏目ID
            $catid = $allCategoriesWithPath[$name]['id'];
            //栏目的模板页
            $tpl = basename($this->allCategories[$catid]['list_template'], '.html');
            if (!empty($catid) && $catid > 0) {
                $category = $this->allCategories[$catid];
                if ($category['haschild']) {
                    $query->whereIn('catid', $category['childids']);
                } else {
                    $query->where(['catid'=>$catid]);
                }
            }
            $query->order('id desc')->page(true, true, false, 10, $tpl);*/
            $list = Db::name($this->table)->where(['is_deleted' => 0])->order('id','desc')->paginate(10);
            // 获取分页显示
            $page = $list->render();
            $this->fetch('list',['list'=>$list,'page'=>$page]);
        } else {
            abort(404, '栏目不存在');
        }
    }

    /**
     * 首页列表渲染的时候格式化其他数据
     * @param $data
     */
    protected function _index_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            $vo['url'] = get_url_show($vo);
        }
    }

    /**
     * 内容页
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show($id)
    {
        $articleService = ArticleService::instance();
        $data = $articleService->getAricleById($id);
        if (count($data) > 0) {
            if ($articleService->updateArticleByid($id)) {
                $data['views'] = $data['views'] + 1;
            }
            $data['tags'] = getNewsTagsLink($data['tags']);
            $tpl = 'show';
            if (in_array($data['catid'], array_keys($this->allCategories))) {
                $tpl = basename($this->allCategories[$data['catid']]['show_template'], '.html');
            }
            $this->fetch($tpl, ['data' => $data]);
        } else {
            abort(404, '数据不存在');
        }
    }

    /**
     * 单页
     */
    public function page()
    {
        $this->fetch();
    }

    /**
     * 标签页
     */
    public function tag()
    {
        $this->fetch();
    }
}