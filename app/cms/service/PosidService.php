<?php
namespace app\cms\service;

use think\admin\Service;

/**
 * 推荐位服务类
 * Class PosidService
 * @package app\cms\service
 */
class PosidService extends  Service
{
    /**
     * 推荐位和文章绑定关系
     * @param $catid
     * @param $posids
     * @param $articleID
     * @return int|string
     */
    public function bindPosidWithArticle($catid,$posids,$articleID,$isUpdate)
    {
        $posidArrs = explode(',',$posids);
        $posidData = [];
        //如果是更新操作，先移除之前的标签关联关系
        if($isUpdate){
            $this->app->db->name('cms_recommend_data')->where(['aid'=>$articleID])->delete();
        }
        foreach ($posidArrs as $posid){
            $posidData[] = [
                'catid' => $catid,
                'posid'=>$posid,
                'aid' =>$articleID,
                'sort' => 1,
            ];
        }
        return $this->app->db->name('cms_recommend_data')->insertAll($posidData);
    }
}