<?php
namespace app\module\service;

use think\admin\Service;

class DiyformService extends Service
{
    /**
     * 获取所有自定义表单类型
     * @return array|false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAllDiyForm()
    {
        return $this->app->db->name('module_diy_form')->field(['id','name'])->select();
    }

    /**
     * 根据自定义表单ID来获取详情
     * @param $id
     * @return array|false|mixed|null|\PDOStatement|string|\think\Model
     */
    public function getDiyFormByID($id)
    {
        return $this->app->db->name('module_diy_form')->where(['id'=>$id])->find();
    }
}