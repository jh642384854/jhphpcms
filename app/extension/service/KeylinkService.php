<?php

namespace app\extension\service;

use think\admin\Service;

/**
 * 关键字链接服务类
 * Class KeylinkService
 * @package app\extension\service
 */
class KeylinkService extends Service
{
    protected $model;

    protected function initialize()
    {
        $this->model = $this->app->db->name('extension_keylink');
    }

    /**
     * 生成缓存数据
     * @return mixed
     */
    public function cacheKeylinkData()
    {
        cache(config('cache.usekey.keylink.key', 'Keylink'), null);
        $data = $this->model->field('id,keyword,url')->where(['status' => 1, 'is_deleted' => 0])->select();
        cache(config('cache.usekey.keylink.key', 'Keylink'), $data);
        return $data;
    }

    /**
     * 从缓存中获取数据
     * @return \Illuminate\Cache\CacheManager|mixed
     */
    public function getKeyLinkFromCache()
    {
        $data = cache(config('cache.usekey.keylink.key', 'Keylink'));
        if (!$data) {
            $data = $this->cacheKeylinkData();
        }
        return $data;
    }
}