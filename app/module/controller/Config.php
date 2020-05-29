<?php
namespace app\module\controller;

use think\admin\Controller;

/**
 * 通用全局配置
 * Class Config
 * @package app\module\controller
 */
class Config extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'moduleDiyConfig';
    /**
     * 通用配置(自定义配置)
     * @auth true
     */
    public function index()
    {
        $this->title = '通用配置';
        $this->_query($this->table)->where(['is_deleted' => '0'])->order('id desc')->page();
    }

    /**
     * 通用配置添加
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

    protected function _form_filter(&$data)
    {
        if ($this->request->isGet()) {
            $data['single_value'] = '';
            $data['many_value'] = '';
            $type = !empty($data['value_type'])?$data['value_type']:'single';
            $defaultValue = !empty($data['value'])?$data['value']:'';
            if($type == 'single'){
                $data['single_value'] = $defaultValue;
            }elseif ($type == 'many'){
                $data['many_value'] = $defaultValue;
            }
        }else{
            //验证key的唯一性
            if(!empty($data['id'])){
                $hasExits = $this->app->db->name($this->table)->where(['is_deleted' => '0'])->where('key',$data['key'])->where('id','<>',$data['id'])->find();
            }else {
                $hasExits = $this->app->db->name($this->table)->where(['is_deleted' => '0'])->where('key', $data['key'])->find();
            }
            if ($hasExits){
                $this->error($data['key'].'：key重复,请更换key名');
            }
            //数据处理
            $data['key'] = strtoupper(trim($data['key']));
            if($data['value_type'] == 'single'){
                $data['value'] = $data['single_value'];
            }elseif ($data['value_type'] == 'many'){
                $data['value'] = $data['many_value'];
            }
        }
    }
    /**
     * 通用配置编辑
     * @auth true
     */
    public function edit()
    {
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 通用配置编辑
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}