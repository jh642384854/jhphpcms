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
     * 得到所有的模型
     * @return mixed
     */
    public function getAllModels()
    {
        return $this->model->where(['status'=>1])->select();
    }
}