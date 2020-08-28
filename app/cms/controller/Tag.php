<?php

namespace app\cms\controller;

use app\cms\service\TagService;
use think\admin\Controller;

/**
 * 标签管理
 * Class Tag
 * @package app\cms\controller
 */
class Tag extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'cms_tag';
    protected $tableData = 'cms_tag_data';

    /**
     * 标签列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '标签列表';
        $this->_query($this->table)->order('id desc')->page();
    }

    /**
     * 添加标签
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 添加标签
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
     * 更新tag数据
     * @param $result
     * @param $data
     */
    protected function _form_result($result, $data){
        if($result){
            TagService::instance()->updateTagCache();
        }
    }

    /**
     * 删除标签
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function delete()
    {
        $this->_applyFormToken();
        //删除映射表的数据
        $id = $this->app->request->post('id', 0, 'intval');
        $this->app->db->name($this->tableData)->where(['tagid' => $id])->delete();
        $this->_delete($this->table);
    }

    /**
     * 在执行完删除操作之后的操作
     * @param $result
     */
    protected function _delete_result($result)
    {
        //更新tag缓存
        if($result){
            TagService::instance()->updateTagCache();
        }
    }

    /**
     * 标签文章列表
     * @auth true
     */
    public function lists()
    {
        $tid = $this->request->get('id', 0, 'intval');
        $list = [];
        if ($tid > 0) {
            $articles = $this->app->db->name($this->tableData)->field('aid')->where(['tagid' => $tid])->limit(10)->select()->toArray();
            $aidArr = [];
            foreach ($articles as $article) {
                $aidArr[] = $article['aid'];
            }
            $list = $this->app->db->name('cms_article')->field('id,title')->where(['is_deleted' => 0])->whereIn('id', implode(',', $aidArr))->select();
        }
        $this->list = $list;
        $this->fetch();
    }
}