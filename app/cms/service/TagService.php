<?php
namespace app\cms\service;

use think\admin\Service;

/**
 * 标签处理服务
 * Class TagService
 * @package app\cms\service
 */
class TagService extends Service
{
    /**
     * 添加标签
     * @param $tags
     * @param $articleID
     * @return int|string
     */
    public function addTags($tags,$articleID,$isUpdate)
    {
        $tagArrs = explode(',',$tags);
        $tagData = [];
        //如果是更新操作，先移除之前的标签关联关系
        if($isUpdate){
            $this->app->db->name('cms_tag_data')->where(['aid'=>$articleID])->delete();
        }
        foreach ($tagArrs as $tagval){
            $tag = strtolower($tagval);
            $exits = $this->app->db->name('cms_tag')->where(['name'=>$tag])->find();
            if($exits){
                $this->app->db->name('cms_tag')->where(['name'=>$tag])->inc('nums')->update();
                $tagid = $exits['id'];
            }else{
                $data = [
                    'name'=>$tag,
                    'nums' => 1,
                    'sort' => 1,
                ];
                $tagid = $this->app->db->name('cms_tag')->insertGetId($data);
            }
            $tagData[] = [
                'tagid'=>$tagid,
                'aid' =>$articleID
            ];

        }
        $this->updateTagCache();
        return $this->app->db->name('cms_tag_data')->insertAll($tagData);
    }

    /**
     * 更新tag缓存数据
     */
    public function updateTagCache()
    {
        $allTags = $this->app->db->name('cms_tag_data')->select();
        $results = [];
        if($allTags){
            foreach ($allTags as $tag){
                $results[$tag['name']] = $tag;
            }
            cache(config('cache.usekey.tags.key', 'AllTags'), $results);
        }
        return $results;
    }

    /**
     * 从缓存中获取所有的标签数据
     * @return array|\Illuminate\Cache\CacheManager|mixed
     */
    public function getAllTagsFromCache()
    {
        $data = cache(config('cache.usekey.tags.key', 'AllTags'));
        if (empty($data)) {
            $data = $this->updateTagCache();
        }
        return $data;
    }
}