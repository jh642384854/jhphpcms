<?php

use think\facade\Db;

/**
 * 根据会员组ID来获取会员组信息
 * @param $gid
 * @return array|null|\think\Model
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 */
function getUserGroupById($gid){
    $data = Db::name('system_member_group')->where(['id'=>$gid])->find();
    if($data){
        return $data;
    }else{
        return [];
    }
}