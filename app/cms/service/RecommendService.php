<?php
namespace app\cms\service;

use think\admin\Service;

/**
 * 推荐位服务类
 * Class RecommendService
 * @package app\cms\service
 */
class RecommendService extends Service
{
    protected $recommend;
    protected function initialize(){
        $this->recommend = $this->app->db->name('CmsRecommend');
    }
    /**
     * 根据模型ID来获取模型信息
     * @param int $recommendid
     * @return array|false|mixed|null|\PDOStatement|string|\think\Recommend
     */
    public function getRecommendByRecommendID($recommendid = 1)
    {
        return $this->recommend->where(['id'=>$recommendid])->find();
    }

    /**
     * 缓存所有的模型数据
     */
    public function cacheAllRecommends()
    {
        $recommends = $this->recommend->order('sort desc,id ASC')->select();
        $newRecommends = [];
        if (count($recommends) > 0) {
            foreach ($recommends as $recommend) {
                $newRecommends[$recommend['id']] = $recommend;
            }
        }
        cache(config('cache.usekey.recommend.key', 'AllRecommend'), $newRecommends);
        return $newRecommends;
    }

    /**
     * 从缓存中得到所有的模型
     * @return array|\Illuminate\Cache\CacheManager|mixed
     */
    public function getAllRecommendsFromCache()
    {
        $data = cache(config('cache.usekey.recommend.key', 'AllRecommend'));
        if(empty($data)){
            $data = $this->cacheAllRecommends();
        }
        return $data;
    }
}