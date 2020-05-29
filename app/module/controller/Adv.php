<?php

namespace app\module\controller;


use app\module\service\AdvService;
use think\admin\Controller;

/**
 * 广告管理
 * Class Adv
 * @package app\module\controller
 */
class Adv extends Controller
{

    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'moduleAdv';

    /**
     * 广告列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '广告管理';
        $this->advCates = AdvService::instance()->getAllAdvCat();
        $query = $this->_query($this->table)->like('name,url');
        $query->equal('cid')->dateBetween('create_at');
        // 加载对应数据列表
        $this->type = input('type', 'all');
        if ($this->type === 'all') {
            $query->where(['is_deleted' => '0']);
        } elseif ($this->type = 'recycle') {
            $query->where(['is_deleted' => '1']);
        }
        // 列表排序并显示
        $query->order('sort desc,id desc')->page();
    }

    /**
     * 列表数据额外处理
     * @param $data
     */
    protected function _index_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            $catname = '未知';
            foreach ($this->advCates as $advCate) {
                if($vo['cid'] == $advCate['id']){
                    $catname = $advCate['name'];
                }
            }
            $vo['catname'] = $catname;
        }
    }

    /**
     * 添加广告
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
     * 编辑广告
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
     * 表单数据处理
     * @param array $vo
     * @throws \ReflectionException
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isGet()) {
            $this->advCates = AdvService::instance()->getAllAdvCat();
        }
    }

    /**
     * 恢复广告
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function recover()
    {
        $this->_applyFormToken();
        $this->_save($this->table, ['is_deleted' => intval(input('is_deleted'))]);
    }

    /**
     * 删除广告(软删除)
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }

    /**
     * 永久删除数据
     * @auth true
     */
    public function delete()
    {
        $this->_applyFormToken();
        $result = $this->app->db->name($this->table)->whereIn('id', explode(',',  $this->app->request->post('id', null)))->delete();
        if ($result !== false) {
            $this->success(lang('think_library_delete_success'), '');
        } else {
            $this->error(lang('think_library_delete_error'));
        }
    }
}