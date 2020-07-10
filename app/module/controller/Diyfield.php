<?php
namespace app\module\controller;

use app\module\service\DiyfieldService;
use think\admin\Controller;
use database\TableOpt;
use think\facade\Log;
use app\cms\service\FieldService;
use app\module\service\DiyformService;

/**
 * 自定义表单字段管理
 * Class Diyfield
 * @package app\module\controller
 */
class Diyfield extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'module_diy_field';
    /**
     *
     * @auth true
     */
    public function index()
    {
        $this->title = '表单字段列表';
        $this->_query('module_diy_field')->equal('typeid')->order('id desc')->page();
    }

    /**
     * 添加字段
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->title = '添加表单字段';
        $typeid = input('typeid',0,'intval');
        $data = [];
        if($typeid>0){
            $data['typeid'] = $typeid;
        }
        $this->diyformlist = DiyformService::instance()->getAllDiyForm();
        $this->_form($this->table, 'form','',[],$data);
    }

    /**
     * 编辑字段
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->title = '编辑表单字段';
        $this->diyformlist = DiyformService::instance()->getAllDiyForm();
        $this->_form($this->table, 'form');
    }

    /**
     * 提交表单数据进行过滤处理
     * @param $vo
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            //①、判断字段名是否重复
            $fieldService = DiyfieldService::instance();
            if ($fieldService->checkFieldEixts($vo['field'])) {
                $this->error('字段名称重复，请更换新字段名！');
            }
            //②、过滤空值
            $settings = array_filter($vo['setting'], function ($val) {
                return ($val === '' || $val === null) ? false : true;
            });
            $vo['field'] = strtolower($vo['field']);
            if (!empty($vo['id'])) {
                //更新操作。要获取原来的setting配置信息，保留里面的一些必要配置信息
                $oldSettings = $this->app->db->name('module_diy_field')->field('setting')->where(['id' => $vo['id']])->find();
                $oldSettingsData = json_decode($oldSettings['setting'], true);
                $settings['length'] = $oldSettingsData['length'];
                $settings['chartype'] = $oldSettingsData['chartype'];
                if (isset($oldSettingsData['defaultvalue'])) {
                    $settings['defaultvalue'] = $oldSettingsData['defaultvalue'];
                }
            }
            $vo['setting'] = json_encode($settings);
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
            $flag = true;
            //如果新创建的字段，就需要在主表或是附表上面创建相应的字段
            if (!isset($data['id'])) {
                //在进行表单提交的时候进行了encode处理，所以这里需要重新decode一下
                $setting = json_decode($data['setting'], true);
                $fieldInfo = [
                    'name' => $data['field'],
                    'type' => $setting['chartype'],
                    'commment' => $data['title'],
                ];
                if (isset($setting['length']) && $setting['length'] != '') {
                    $fieldInfo['length'] = $setting['length'];
                }
                if (isset($setting['defaultvalue']) && $setting['defaultvalue'] != '') {
                    $fieldInfo['defaultvalue'] = $setting['defaultvalue'];
                }
                if ($data['formtype'] == 'number') {
                    if (isset($setting['precision'])) {
                        $fieldInfo['precision'] = $setting['precision'];
                    } else {
                        $fieldInfo['precision'] = 2;
                    }
                }
                $tableOpt = new TableOpt();
                $diyfromdata = DiyformService::instance()->getDiyFormByID($data['typeid']);
                if($diyfromdata){
                    $tablename = $diyfromdata['tablename'];
                    if (!$tableOpt->addField($tablename, $fieldInfo)) {
                        $flag = false;
                        //①、如果字段创建失败，记录日志
                        Log::error(sprintf("为 %s 新建 %s 字段失败，字段格式为：%s", $tablename, $data['field'], json_encode($fieldInfo)));
                        //②、将已经写入的这条记录给删掉
                        $this->app->db->name('module_diy_field')->where(['id' => $result])->delete();
                    }
                }else{
                    $flag = false;
                }
            }
            if ($flag) {
                $location = 'javascript:history.back()';
                $this->success('恭喜, 字段创建成功！', $location);
            } else {
                $this->error('字段创建失败，请重试！');
            }
        } else {
            $this->error('字段创建失败, 请稍候再试！');
        }
    }

    /**
     * 删除表单字段
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }

    /**
     * 预览表单
     * @auth true
     */
    public function preview()
    {
        $typeid = input('typeid',0,'intval');
        if($typeid){
            $allFiels = $this->app->db->name('module_diy_field')->where(['typeid' => $typeid,'isshow'=>1])->order('sort asc')->select();
            $form = new \form\Form(0, 0, $allFiels);
            $this->formFields = $form->get();
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
            $this->fetch();
        }else{
            $this->error('参数错误');
        }
    }
}