<?php
namespace app\note\service;

use think\admin\Service;

/**
 * 内容服务类
 * Class ContentService
 * @package app\note\service
 */
class ContentService extends Service
{
    /**
     * 获取指定栏目的第一条文档记录
     * @param $catid
     * @return string
     */
    public function getFirstUrlByCatid($catid)
    {
        $url = '';
        if($catid != '' && $catid >0){
            $mdcontent = $this->app->db->name('noteContent')->where(['catid'=>$catid])->order('id ASC')->limit(1)->column('filename');
            if($mdcontent){
                $catpath = CategoryService::instance()->getCategoryPathByCatid($catid);
                return '/'.$catpath.$mdcontent[0];
            }
        }
        return $url;
    }
}