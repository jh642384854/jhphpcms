<?php

namespace app\cms\controller;

use app\cms\service\FieldService;
use app\cms\service\ModelService;
use database\TableOpt;
use think\admin\Controller;
use think\facade\Log;

/**
 * 模型字段管理
 * Class Field
 * @package app\cms\controller
 */
class Field extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'cms_model_field';

    /**
     * 字段管理
     * @auth true
     */
    public function index()
    {
        $this->title = '模型字段管理';
        $this->modelid = !empty(input('mid')) ? intval(input('mid')) : 1;
        $this->list = $this->app->db->name($this->table)->where(['modelid' => $this->modelid])->select();
        $this->fetch();
    }

    /**
     * 验证模型是否存在
     * @param $modelid
     * @return bool
     */
    private function checkModel($modelid)
    {
        $flag = false;
        if (!empty($modelid) && intval($modelid) > 0) {
            $exits = $this->app->db->name('CmsModel')->where(['id' => $modelid])->find();
            if ($exits) {
                $flag = true;
            }
        }
        return $flag;
    }

    /**
     * 添加模型字段
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->title = '添加模型字段';
        if ($this->checkModel(input('mid'))) {
            $this->_applyFormToken();
            $this->modelid = intval(input('mid'));
            $this->_form($this->table, 'form');
        } else {
            $this->error('模型错误');
        }
    }

    /**
     * 修改模型字段
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->title = '修改模型字段';
        if ($this->checkModel(input('mid'))) {
            $this->_applyFormToken();
            $this->modelid = intval(input('mid'));
            $this->_form($this->table, 'form');
        } else {
            $this->error('模型错误');
        }
    }

    /**
     * 提交表单数据进行过滤处理
     * @param $vo
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            //①、判断字段名是否重复,只针对新增操作，修改不会提交字段名，也就不用检查字段名称了
            if(empty($vo['id'])) {
                $fieldService = FieldService::instance();
                if ($fieldService->checkFieldEixts($vo['modelid'], $vo['field'])) {
                    $this->error('字段名称重复，请更换新字段名！');
                }
                $vo['field'] = strtolower($vo['field']);
            }
            //②、过滤空值
            $settings = array_filter($vo['setting'], function ($val) {
                return ($val === '' || $val === null) ? false : true;
            });
            if (!empty($vo['id'])) {
                //更新操作。要获取原来的setting配置信息，保留里面的一些必要配置信息
                $oldSettings = $this->app->db->name($this->table)->field('setting')->where(['id' => $vo['id']])->find();
                $oldSettingsData = json_decode($oldSettings['setting'], true);
                $settings['length'] = isset($oldSettingsData['length'])?$oldSettingsData['length']:'';
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
                $modelData = ModelService::instance()->getModelByModelID($data['modelid']);
                if ($modelData) {
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
                    $tablename = $modelData['tablename'];
                    if (!$data['issystem']) {
                        $tablename = config('database.modeltable.prefix') . $modelData['tablename'] . config('database.modeltable.suffix');
                    }
                    if (!$tableOpt->addField($tablename, $fieldInfo)) {
                        $flag = false;
                        //①、如果字段创建失败，记录日志
                        Log::error(sprintf("为 %s 模型(%s)新建 %s 字段失败，字段格式为：%s", $modelData['name'], $tablename, $data['field'], json_encode($fieldInfo)));
                        //②、将已经写入的这条记录给删掉
                        $this->app->db->name($this->table)->where(['id' => $result])->delete();
                    }
                }
            }
            if ($flag) {
                $location = 'javascript:history.back()';
                $this->success('恭喜, 字段创建成功！', $location);
            } else {
                $this->error('模型保存失败, 字段创建失败，请重试！');
            }
        } else {
            $this->error('模型保存失败, 请稍候再试！');
        }
    }

    /**
     * 模型字段更多设置
     * @auth true
     */
    public function field_settings()
    {
        $type = input('type', 'text');
        $id = input('id');
        $tpl = app()->getAppPath() . 'stubs' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . 'form.html';
        $isupdate = false;
        if (is_file($tpl)) {
            $data = [];
            if (!is_null($id)) {
                $result = $this->app->db->name($this->table)->where(['id' => $id, 'disabled' => 0])->find();
                if ($result) {
                    $data = json_decode($result['setting'], true);
                }
                $isupdate = true;
            }
            //如果字段类型是下面的类型，就可以快捷从通用配置里面获取数据
            $loadLocalConfig = ['select', 'selects', 'radio', 'checkbox'];
            if (in_array($type, $loadLocalConfig)) {
                $this->localConfig = $this->app->db->name('module_diy_config')->where(['is_deleted' => 0, 'value_type' => 'many'])->select();
            }
            $this->type = $type;
            $this->isupdate = $isupdate;
            $this->fetch($tpl, $data);
        } else {
            echo '';
        }
    }
}