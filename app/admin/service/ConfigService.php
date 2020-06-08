<?php
namespace app\admin\service;
use think\admin\Service;

/**
 * 配置服务操作类
 * Class ConfigService
 * @package app\admin\service
 */
class ConfigService extends Service
{
    /**
     * 将指定的类别配置进行缓存处理
     * @param string $type
     * @return array
     */
    public function cacheConfig($type = '')
    {
        cache(config('cache.usekey.config.key').'_'.$type,null);
        $data = [];
        if($type != ''){
            $res = $this->app->db->name('system_config')->where(['type'=>$type])->select();
            if(count($res) > 0){
                $data[] = '';
                foreach ($res->toArray() as $val){
                    $data[$val['name']] = $val['value'];
                }
                cache(config('cache.usekey.config.key').'_'.$type,$data);
            }
        }
        return $data;
    }

    /**
     * 从缓存中获取指定类别的数据
     * @param $type
     * @return array|\Illuminate\Cache\CacheManager|mixed
     */
    public function getTypeConfigFromCache($type)
    {
        $data = cache(config('cache.usekey.config.key').'_'.$type);
        if(empty($data)){
            $data = $this->cacheConfig($type);
        }
        return $data;
    }
}