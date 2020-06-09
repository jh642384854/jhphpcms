<?php

namespace app\extension\controller;

use app\extension\service\KeylinkService;
use think\admin\Controller;

/**
 * 关键字链接管理
 * Class Keylink
 * @package app\extension\controller
 */
class Keylink extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'extension_keylink';

    /**
     * 关键字链接列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '关键字链接管理';
        $this->_query($this->table)->where(['is_deleted' => 0])->order('id desc')->page();
    }

    /**
     * 添加关键字链接
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
     * 编辑关键字链接
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

    protected function _form_result()
    {
        if ($this->request->isPost()) {
            KeylinkService::instance()->cacheKeylinkData();
        }
    }

    /**
     * 删除关键字链接
     * @auth true
     */
    public function delete()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }

    /**
     * 缓存关键字链接
     * @auth true
     */
    public function cache()
    {
        KeylinkService::instance()->cacheKeylinkData();
        $this->success('缓存成功');
    }
}