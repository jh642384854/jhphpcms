<?php
namespace app\cms\service;

use think\admin\Service;

/**
 * 单页管理服务类
 * Class PageService
 * @package app\cms\service
 */
class PageService extends Service
{
    /**
     * 添加或新增单页数据
     * @param $data
     */
    public function updateOrAddData($data)
    {
        if(isset($data['id']) && $data['id'] >0){
            return $this->app->db->name('cms_page')->update($data);
        }else{
            return $this->app->db->name('cms_page')->save($data);
        }
    }
}