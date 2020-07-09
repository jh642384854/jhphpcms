<?php
namespace app\module\service;

use think\admin\Service;

class AttachmentService extends Service
{
    /**
     * 添加附件
     * @param $data
     * @return false|int|string
     */
    public function addAttachment($data)
    {
        $exits = $this->app->db->name('module_attachment')->where(['filemd5'=>$data['filemd5']])->find();
        if(!$exits){
            return $this->app->db->name('module_attachment')->insert($data);
        }
    }

}