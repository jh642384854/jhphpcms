<?php
namespace app\cms\service;
use think\admin\Service;

/**
 * 模型字段服务类
 * Class FieldService
 */
class FieldService extends Service
{
    /**
     * 检查字段是否已经存在
     * @param $modeid
     * @param $filed
     * @return bool
     */
    public function checkFieldEixts($modeid,$filed)
    {
        $fields = $this->app->db->name('cms_model_field')->field('field')->whereIn('modelid','1,'.$modeid)->select()->toArray();
        $modelFields = [];
        foreach ($fields as $field){
            $modelFields[] = $field['field'];
        }
        $flag = false;
        if(in_array(strtolower($filed),$modelFields)){
            $flag = true;
        }
        return $flag;
    }
}