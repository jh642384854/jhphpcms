<?php
namespace app\cms\service;


use think\admin\Service;

/**
 * 模型服务类
 * Class ModelService
 * @package app\cms\service
 */
class ModelService extends Service
{
    protected $model;
    protected function initialize(){
        $this->model = $this->app->db->name('CmsModel');
    }
    /**
     * 根据模型ID来获取模型信息
     * @param int $modelid
     * @return array|false|mixed|null|\PDOStatement|string|\think\Model
     */
    public function getModelByModelID($modelid = 1)
    {
        return $this->model->where(['id'=>$modelid])->find();
    }

    /**
     * 缓存所有的模型数据
     */
    public function cacheAllModels()
    {
        cache(config('cache.usekey.model.key', 'AllModel'), null);
        $models = $this->model->order('id ASC')->select();
        $newModels = [];
        if (count($models) > 0) {
            foreach ($models as $model) {
                $newModels[$model['id']] = $model;
            }
        }
        cache(config('cache.usekey.model.key', 'AllModel'), $newModels);
        return $newModels;
    }

    /**
     * 从缓存中得到所有的模型
     * @return array|\Illuminate\Cache\CacheManager|mixed
     */
    public function getAllModelsFromCache()
    {
        $data = cache(config('cache.usekey.model.key', 'AllModel'));
        if(empty($data)){
            $data = $this->cacheAllModels();
        }
        return $data;
    }

    /**
     * 删除指定模型的字段
     * @param $modelid
     * @return false|int
     */
    public function deleteModelField($modelid)
    {
        if($modelid>1){
            return $this->app->db->name('cms_model_field')->where(['modelid'=>$modelid])->delete();
        }
    }
}