<?php
namespace app\member\controller;

use think\admin\Controller;
use think\admin\extend\DataExtend;
/**
 * 会员分组管理
 * Class MemberGroup
 * @package app\admin\controller
 */
class Group extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    public $table = 'SystemMemberGroup';
    /**
     * 会员分组列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $this->title = '会员组权限管理';

        $query = $this->_query($this->table)->dateBetween('create_at');
        $query->like('title,desc')->equal('status')->order('sort desc,id desc')->page();
    }

    /**
     * 添加会员组权限
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑会员组权限
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 修改权限状态
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function state()
    {
        $this->_applyFormToken();
        $this->_save($this->table, ['status' => input('status')]);
    }

    /**
     * 权限配置节点
     * @auth true
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function apply()
    {
        $map = $this->_vali(['auth.require#id' => '权限ID不能为空！']);
        if (input('action') === 'get') {
            $checkeds = $this->app->db->name('systemMemberGroupNode')->where($map)->column('node');
            $menus = $this->app->db->name('SystemMemberMenu')->where(['status' => '1'])->order('sort desc,id asc')->column('id,pid,icon,url,title,params', 'id');
            foreach ($menus as &$menu){
                $menu['checked'] = in_array($menu['url'],$checkeds);
            }
            $menuData = DataExtend::arr2tree($menus);
            $this->success('获取权限节点成功！',$menuData);
        } elseif (input('action') === 'save') {
            list($post, $data) = [$this->request->post(), []];
            foreach (isset($post['nodes']) ? $post['nodes'] : [] as $node) {
                if($node != '#'){
                    $data[] = ['auth' => $map['auth'], 'node' => $node];
                }
            }
            $this->app->db->name('systemMemberGroupNode')->where($map)->delete();
            $this->app->db->name('systemMemberGroupNode')->insertAll($data);
            $this->success('权限授权修改成功！', 'javascript:history.back()');
        } else {
            $this->title = '权限配置节点';
            $this->_form($this->table, 'apply');
        }
    }

    /**
     * 删除会员组权限
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }

    /**
     * 删除结果处理
     * @param boolean $result
     * @throws \think\db\exception\DbException
     */
    protected function _remove_delete_result($result)
    {
        if ($result) {
            $map = $this->_vali(['auth.require#id' => '权限ID不能为空！']);
            $this->app->db->name('systemMemberGroupNode')->where($map)->delete();
            $this->success("权限删除成功！");
        } else {
            $this->error("权限删除失败，请稍候再试！");
        }
    }
}