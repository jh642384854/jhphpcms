<?php
namespace app\module\service;

use think\admin\Service;

class DiyfieldService extends Service
{
    /**
     * 检查字段是否已经存在
     * @param $modeid
     * @param $filed
     * @return bool
     */
    public function checkFieldEixts($filed)
    {
        return $this->app->db->name('module_diy_field')->where(['field'=>strtolower($filed)])->find();
    }
}