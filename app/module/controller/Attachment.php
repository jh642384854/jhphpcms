<?php
namespace app\module\controller;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use think\admin\Controller;

/**
 * 附件管理
 * Class Attachment
 * @package app\module\controller
 */
class Attachment extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'moduleAttachment';

    /**
     * 附件列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '广告管理';
        $query = $this->_query($this->table)->like('filename')->equal('status');
        // 加载对应数据列表
        $this->type = input('type','all');
        if($this->type != 'all'){
            foreach (config('constant.attachmentType') as $key=>$val){
                if($this->type == $key){
                    $query->whereIn('fileext',$val['suffix']);
                }
            }
        }
        // 列表排序并显示
        $query->order('id desc')->page();
    }
    /**
     * 列表数据额外处理
     * @param $data
     */
    protected function _index_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            $catname = '未知';
            foreach (config('constant.attachmentType') as $key=>$val) {
                if(in_array($vo['fileext'],explode(',',$val['suffix']))){
                    if($key == 'doc'){
                        $vo['icon'] = $val['icon'][$vo['fileext']];
                    }else{
                        $vo['icon'] = $val['icon'];
                    }
                    $vo['type'] = $key;
                }
            }
            $vo['catname'] = $catname;
        }
    }

    /**
     * 文件下载
     * @auth true
     */
    public function down()
    {
        $id = input('id');
        $op = input('op','');
        if($id){
            $data = $this->app->db->name($this->table)->where(['id'=>$id])->find();
            if($data){
                if($op == 'down'){
                    $this->app->db->name($this->table)->where(['id'=>$id])->inc('downloads')->update();
                    return download($data['filepath'],$data['filename']);
                }else{
                    $adapter = new Local(ROOT_PATH);
                    $filesystem = new Filesystem($adapter);
                    if(!$filesystem->has($data['filepath'])){
                        //将当前附件资源更新为异常状态
                        $this->app->db->name($this->table)->where(['id'=>$id])->save(['status'=>-1]);
                        $this->error(lang('downfile_not_exits'),'',404);
                    }else{
                        $this->success('资源存在');
                    }
                }
            }else{
                $this->error(lang('no_record'));
            }
        }
    }
    /**
     * 永久删除数据
     * @auth true
     */
    public function delete()
    {
        $this->_applyFormToken();
        $ids = $this->app->request->post('id', null);
        if($ids){
            //1.删除物理资源
            $idArr = explode(',',$ids);
            foreach ($idArr as $item) {
                $data = $this->app->db->name($this->table)->where(['id'=>$item])->find();
                if($data){
                    $adapter = new Local(ROOT_PATH);
                    $filesystem = new Filesystem($adapter);
                    if($filesystem->has($data['filepath'])){
                        $filesystem->delete($data['filepath']);
                    }
                }
            }
            //2.删除数据库记录
            $result = $this->app->db->name($this->table)->whereIn('id', explode(',',  $ids))->delete();
            if ($result !== false) {
                $this->success(lang('think_library_delete_success'), '');
            } else {
                $this->error(lang('think_library_delete_error'));
            }
        }
    }

}