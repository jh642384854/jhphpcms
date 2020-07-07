<?php

namespace app\module\controller;

use app\cms\service\CategoryService;
use ql\DownloadImage;
use QL\Ext\AbsoluteUrl;
use QL\QueryList;
use think\admin\Controller;

/**
 * 采集管理
 * Class Collection
 * @package app\module\controller
 * http://querylist.cc/docs/guide/v4/installation
 */
class Collect extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'module_collect';
    protected $collectDefaultFilterTag = ['p', 'a', 'b', 'i', 'em', 'script', 'iframe', 'table', 'span', 'img', 'object', 'embed', 'param', 'div'];

    /**
     * 任务列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '采集任务列表';
        $this->_query($this->table)->where(['is_deleted' => 0])->order('id desc')->page();
    }

    /**
     * 添加任务
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        //采集测试网址 https://www.liqingbo.cn/news/list-1.html?cid=1&page=1
        $this->title = '创建采集任务';
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑任务
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $this->title = '编辑采集任务';
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    protected function _form_filter(&$data)
    {
        if ($this->request->isPost()) {
            if ($data['name'] == '' || $data['url'] == '' || $data['pagesize_start'] == '' || $data['pagesize_end'] == '' || $data['title_selector'] == '' || $data['title_attr'] == '') {
                $this->error('数据填写不完整,请填写完整数据后在提交');
            }
            if (isset($data['customconfig'])) {
                $ruleNames = $data['customconfig']['name'];
                $ruleSelectors = $data['customconfig']['selector'];
                $ruleAttrs = $data['customconfig']['attr'];
                $ruleFilters = $data['customconfig']['filter'];
                $newrule = [];
                foreach ($ruleNames as $key => $name) {
                    array_push($newrule, ['name' => $name, 'selector' => $ruleSelectors[$key], 'attr' => $ruleAttrs[$key], 'filter' => $ruleFilters[$key]]);
                }
                $data['custom_config'] = json_encode($newrule);
                unset($data['customconfig']);
            }
        }
    }

    /**
     * 测试采集任务
     * @auth true
     */
    public function testjob()
    {
        $arrlowType = ['list', 'content'];
        $type = $this->request->request('type', 'list', 'trim');
        if (in_array($type, $arrlowType)) {
            $id = $this->request->request('id', 0, 'intval');
            $collectConfig = $this->app->db->name($this->table)->where(['id' => $id, 'is_deleted' => 0])->find();
            $rules = $this->dorule($collectConfig);
            $this->type = $type;
            //1.测试采集列表页
            if ($type == 'list') {
                if ($id) {
                    if ($collectConfig) {
                        $url = str_replace('{page}', 1, $collectConfig['url']);
                        $parseUrlInfo = parse_url($url);
                        unset($rules['content']);
                        $ql = QueryList::getInstance();
                        $ql->use(AbsoluteUrl::class);
                        $this->id = $id;
                        $this->list = $ql->get($url)->rules($rules)->range($collectConfig['range_list'])->query()->getData(function ($item) use ($ql, $parseUrlInfo) {
                            //判断采集的url地址是相对还是绝对地址
                            if (!strpos($item['url'], 'http')) {
                                //使用帮助函数单独转换某个链接
                                $item['url'] = $ql->absoluteUrlHelper($parseUrlInfo['scheme'] . '://' . $parseUrlInfo['host'] . '/', $item['url']);
                                return $item;
                            }
                        });
                        $this->fetch();
                    } else {
                        $this->error('参数错误');
                    }
                } else {
                    $this->error('参数错误');
                }
            } else if ($type == 'content') {
                $url = input('url', '', 'trim');
                if ($url != '') {
                    $contentRules = [
                        'content' => $rules['content']
                    ];
                    $ql = QueryList::getInstance();
                    $this->data = $ql->get($url)->rules($contentRules)->query()->getData()->all();
                    $this->fetch();
                }
            }
        }
    }

    /**
     * 采集规则处理
     */
    private function dorule($config)
    {
        $rules = [
            'title' => [$config['title_selector'], $config['title_attr'], $config['title_filter']],
            'url' => [$config['page_url_selector'], $config['page_url_attr'], $config['page_url_filter']],
            'content' => [$config['content_selector'], $config['content_attr'], $config['content_filter']],
        ];
        if ($config['thumb_selector'] != '') {
            $rules['thumb'] = [$config['thumb_selector'], $config['thumb_attr'], $config['thumb_filter']];
        }
        if ($config['author_selector'] != '') {
            $rules['author'] = [$config['author_selector'], $config['author_attr'], $config['author_filter']];
        }
        if ($config['comefrom_selector'] != '') {
            $rules['comefrom'] = [$config['comefrom_selector'], $config['comefrom_attr'], $config['comefrom_filter']];
        }
        if ($config['time_selector'] != '') {
            $rules['time'] = [$config['time_selector'], $config['time_attr'], $config['time_filter']];
        }
        if ($config['description_selector'] != '') {
            $rules['description'] = [$config['description_selector'], $config['description_attr'], $config['description_filter']];
        }
        if ($config['custom_config'] != '') {
            array_push($rules, json_decode($config['custom_config'], true));
        }
        return $rules;
    }

    /**
     * 执行采集任务
     * @auth true
     */
    public function dojob()
    {
        $id = input('id', 0, 'intval');
        $collectConfig = $this->app->db->name($this->table)->where(['id' => $id, 'is_deleted' => 0])->find();
        $curr = input('curr', $collectConfig['pagesize_start'], 'intval');
        $action = input('action', '', 'trim');
        if ($action != '') {
            if ($action == 'list') {
                $articles = $this->collectList($curr, $collectConfig, $id);
                //将采集的结果入库
                $this->app->db->table('module_collect_data')->insertAll($articles);
                //返回采集的结果给前端渲染显示
                return json(['code' => 0, 'msg' => '数据采集成功', 'count' => count($articles), 'data' => $articles]);
            } else if ($action == 'content') {
                //详情页URL地址
                $url = input('url', '', 'trim');
                //采集内容
                $content = $this->collectContent($url, $collectConfig);
                $this->app->db->table('module_collect_data')->where(['collect_id' => $id, 'url' => $url])->save(['content' => $content]);
                return json(['code' => 0, 'msg' => '内容采集成功']);
            }
        } else {
            if ($collectConfig) {
                $this->title = '正在执行采集任务';
                $this->id = $id;
                $this->curr = $curr;
                $this->totalPage = $collectConfig['pagesize_end'];
                $this->fetch();
            } else {
                $this->error('数据错误');
            }
        }
    }

    /**
     * 采集列表
     * @param $page
     * @param $collectConfig
     * @return array
     */
    private function collectList($page, $collectConfig, $collect_id)
    {
        $url = str_replace('{page}', $page, $collectConfig['url']);
        $parseUrlInfo = parse_url($url);
        $rules = $this->dorule($collectConfig);
        $ql = QueryList::getInstance();
        $ql->use(AbsoluteUrl::class);
        unset($rules['content']);
        $articles = $ql->get($url)->rules($rules)->range($collectConfig['range_list'])->query()->getData(function ($item) use ($ql, $parseUrlInfo, $collect_id) {
            $item['collect_id'] = $collect_id;
            //判断采集的url地址是相对还是绝对地址
            if (!strpos($item['url'], 'http')) {
                //使用帮助函数单独转换某个链接
                $item['url'] = $ql->absoluteUrlHelper($parseUrlInfo['scheme'] . '://' . $parseUrlInfo['host'] . '/', $item['url']);
                return $item;
            }
        })->all();
        $ql->destruct();
        return $articles;
    }

    /**
     * 采集内容
     * @param $url
     * @param $collectConfig
     */
    private function collectContent($url, $collectConfig)
    {
        if ($url != '') {
            $rules = $this->dorule($collectConfig);
            $ql = QueryList::use(DownloadImage::class);
            $parseUrlInfo = parse_url($url);
            $contentData = $ql->get($url)->rules(['content' => $rules['content']])->query()->getData(function ($item) use ($ql, $parseUrlInfo, $url) {
                $item['content'] = $ql->downloadImage($item['content'], $parseUrlInfo['host'], $url);
                return $item;
            });
            $ql->destruct();
            return $contentData->all()['content'];
        } else {
            return '';
        }
    }

    /**
     * 查看采集数据
     * @auth true
     */
    public function showlist()
    {
        $this->title = '查看采集数据';
        $collect_id = input('id', 0, 'intval');
        if ($collect_id > 0) {
            $this->id = $collect_id;
            $this->_query('module_collect_data')->where(['collect_id' => $collect_id])->order('id desc')->page();
        } else {
            $this->error('数据错误');
        }
    }

    /**
     * 导入采集数据
     * @auth true
     */
    public function import()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (strpos($data['catid'], ',')) {
                $cat = explode(',', $data['catid']);
                $data['catid'] = $cat[count($cat) - 1];
            }
            $data['create_at'] = date('Y-m-d H:i:s');
            //保存采集任务
            $hasExec = $this->app->db->name('module_collect_job')->where(['catid' => $data['catid'], 'collect_id' => $data['collect_id']])->find();
            if (!$hasExec) {
                $jobid = $this->app->db->name('module_collect_job')->insertGetId($data);
            } else {
                $jobid = $hasExec['id'];
            }
            $this->success('任务创建成功', ['jobid' => $jobid]);
        } else {
            $collect_id = input('id', 0, 'intval');
            $jobid = input('jobid', 0, 'intval');
            $this->id = $collect_id;
            if ($collect_id > 0) {
                if ($jobid > 0) {
                    $limit = 5;//每次导入100条数据
                    $hasFinish = false; //是否完成了导入操作
                    $map = [
                        ['id', '=', $jobid]
                    ];
                    $jobdata = $this->app->db->name('module_collect_job')->where($map)->find();
                    if ($jobdata) {
                        $collect_map = ['collect_id' => $collect_id];
                        $count = $this->app->db->name('module_collect_data')->where($collect_map)->count();
                        if ($count == $jobdata['records']) {
                            $hasFinish = true;
                            $imports = $count;
                        } else {
                            //得到栏目信息
                            $category = CategoryService::instance()->getCategoryByCatid($jobdata["catid"]);
                            if (count($category) > 0) {
                                $modelid = $category['modelid'];
                            } else {
                                $modelid = 1;
                            }
                            $collectData = $this->app->db->name('module_collect_data')->field(['title', 'description', 'thumb', 'author', 'comefrom', 'content', 'UNIX_TIMESTAMP(`time`)' => 'create_at'])
                                ->fieldRaw("'" . $jobdata["catid"] . "' as 'catid','" . $modelid . "' as 'modelid'")->where($collect_map)->limit($jobdata['records'], $limit)->select()->all();
                            $imports = count($collectData);
                            if ($limit > $count) {
                                $hasFinish = true;
                            }
                            $this->app->db->name('cms_article')->insertAll($collectData);
                            //更新导入的进度
                            $this->app->db->name('module_collect_job')->where($map)->inc('records', $imports)->update();
                        }
                        $this->success('导入成功', ['hasFinish' => $hasFinish, 'page' => input('page', 1, 'intval'), 'total' => $count, 'imports' => $imports, 'jobid' => $jobid, 'collect_id' => $collect_id]);
                    } else {
                        $this->error('数据错误');
                    }
                } else {
                    $this->categorys = json_encode(CategoryService::instance()->getAllCategoryTree(0, true), true);
                    $this->fetch();
                }
            } else {
                $this->error('数据错误');
            }
        }
    }

    /**
     * 编辑采集内容
     * @auth true
     */
    public function edit_content()
    {
        $this->title = '编辑采集数据';
        $this->_applyFormToken();
        $this->_form('module_collect_data', 'edit_content');
    }

    /**
     * 删除采集内容
     * @auth true
     */
    public function del_content()
    {
        $this->_applyFormToken();
        $this->_delete('module_collect_data');
    }

    /**
     * 删除任务
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}