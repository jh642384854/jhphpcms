<?php
namespace app\module\controller;

use think\admin\Controller;
use database\TableOpt;

/**
 * 自定义表单管理
 * Class Diyform
 * @package app\module\controller
 */
class Diyform extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'module_diy_form';

    /**
     * 自定义表单列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '自定义表单列表';
        //查询自定义表单字段的菜单信息
        $field_url_info = $this->app->db->name('system_menu')->where(['url'=>'module/diyfield/index'])->column('id,pid');
        $spm = '';
        if($field_url_info){
            $spm = 'm-'.$field_url_info[0]['pid'].'-'.$field_url_info[0]['id'];
        }
        $this->spm = $spm;
        $this->_query($this->table)->order('id desc')->page();
    }

    /**
     * 添加自定义表单
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
     * 编辑自定义表单
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

    protected function _form_filter(&$data)
    {
        if($this->request->isPost()){
            if(!isset($data['id'])){
                $data['create_at'] = time();
                $data['tablename'] = 'module_'.$data['tablename'];
            }else{
                unset($data['tablename']);
            }
        }
    }

    /**
     * 表单提交后执行的操作(已经完成了数据新增或更新操作)
     * @param $result
     * @param $data
     */
    protected function _form_result($result, $data)
    {
        if ($result !== false) {
            if (!isset($data['id'])) {
                $tableOpt = new TableOpt();
                if (!$tableOpt->checkTabeExits($data['tablename'])) {
                    $tableOpt->createTable($data['tablename'], 'module ' . $data['name']);
                }
            }
        }
    }
    
    /**
     * 删除自定义表单
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }

    protected function _delete_filter()
    {
        $id = input('id', 0, 'intval');
        $formData = $this->app->db->name('module_diy_form')->where(['id'=>$id])->find();
        if($formData){
            $tableOpt = new TableOpt();
            $tableOpt->deleteTable($formData['tablename']);
        }else{
            $this->error('参数错误');
        }
    }
}