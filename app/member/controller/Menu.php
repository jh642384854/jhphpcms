<?php
namespace app\member\controller;

use think\admin\Controller;
use think\admin\extend\DataExtend;
/**
 * 会员前台菜单管理
 * Class MemberMenu
 * @package app\admin\controller
 */
class Menu extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    public $table = 'SystemMemberMenu';
    /**
     * 会员菜单列表
     * @auth true
     * @menu true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $this->title = '系统菜单管理';
        $this->_page($this->table, false);
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_page_filter(&$data)
    {
        foreach ($data as &$vo) {
            if ($vo['url'] !== '#' && $vo['pid'] != 0) {
                $vo['url'] = trim(url($vo['url']) . (empty($vo['params']) ? '' : "?{$vo['params']}"), '/\\');
            }
            $vo['ids'] = join(',', DataExtend::getArrSubIds($data, $vo['id']));
        }
        $data = DataExtend::arr2table($data);
    }

    /**
     * 添加系统菜单
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
     * 编辑系统菜单
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
     * 表单数据处理
     * @param array $vo
     * @throws \ReflectionException
     */
    protected function _form_filter(&$vo)
    {
        if ($this->request->isGet()) {
            // 选择自己的上级菜单
            if (empty($vo['pid']) && $this->request->get('pid', '0')) $vo['pid'] = $this->request->get('pid', '0');
            // 列出可选上级菜单
            $menus = $this->app->db->name($this->table)->where(['status' => '1'])->order('sort desc,id asc')->column('id,pid,icon,url,title,params', 'id');
            $this->menus = DataExtend::arr2table(array_merge($menus, [['id' => '0', 'pid' => '-1', 'url' => '#', 'title' => '顶部菜单']]));
            if (isset($vo['id'])) foreach ($this->menus as $key => $menu) if ($menu['id'] === $vo['id']) $vo = $menu;
            foreach ($this->menus as $key => &$menu) {
                if ($menu['spt'] >= 3) unset($this->menus[$key]); // || $menu['url'] !== '#'
                if (isset($vo['spt']) && $vo['spt'] <= $menu['spt']) unset($this->menus[$key]);
            }
        }
    }

    /**
     * 修改菜单状态
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function state()
    {
        $this->_applyFormToken();
        $this->_save($this->table, ['status' => intval(input('status'))]);
    }

    /**
     * 删除系统菜单
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}