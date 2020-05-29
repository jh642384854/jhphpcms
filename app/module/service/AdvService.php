<?php
namespace app\module\service;

use think\admin\Service;

class AdvService extends Service
{
    /**
     * 获取所有广告位
     * @return array|false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAllAdvCat()
    {
        return $this->app->db->name('moduleAdvcat')->field(['id','name'])->select();
    }
}