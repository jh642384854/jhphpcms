<?php

namespace app\module\controller;

use app\cms\service\CategoryService;
use app\module\service\AttachmentService;
use ql\DownloadImage;
use QL\Ext\AbsoluteUrl;
use QL\QueryList;
use think\admin\Controller;
use think\admin\storage\LocalStorage;

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
            if ($data['is_auto_description']) {
                $auto_length = intval($data['auto_description_length']);
                //设置自动摘要的最大长度
                if($auto_length<0 || $auto_length > 255){
                    $data['auto_description_length'] = 255;
                }
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
                        unset($rules['seo_keywords']);
                        unset($rules['seo_description']);
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
            'seo_keywords' => [$config['seo_keywords_selector'], $config['seo_keywords_attr'], $config['seo_keywords_filter']],
            'seo_description' => [$config['seo_description_selector'], $config['seo_description_attr'], $config['seo_description_filter']]
        ];
        if ($config['thumb_selector'] != '') {
            $rules['thumb'] = [$config['thumb_selector'], $config['thumb_attr'], $config['thumb_filter']];
        }
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
                $updateContent = $this->collectContent($url, $collectConfig);
                $cjdata = $this->app->db->table('module_collect_data')->where(['collect_id' => $id, 'url' => $url])->column('thumb,description');
                //自动提取摘要
                if($collectConfig['is_auto_description'] && $cjdata[0]['description'] == ''){
                    $first_sentence = str_replace(PHP_EOL,'',explode('。',strip_tags($updateContent['content']))[0]);
                    //这里做一个控制，保证自动摘要内容不会少于50个字符
                    if(mb_strlen($first_sentence) < 50){
                        $updateContent['description'] = msubstr(strip_tags($updateContent['content']),$collectConfig['auto_description_length'],0,false);
                    }else{
                        $updateContent['description'] = msubstr($first_sentence,$collectConfig['auto_description_length'],0,false);
                    }
                }
                //自动提取内容页的首张图片作为缩略图
                if($collectConfig['is_first_img'] && $cjdata[0]['thumb'] == ''){
                    $ql = QueryList::html($updateContent['content']);
                    $thumb = $ql->find('img:eq(0)')->src;
                    if($thumb != ''){
                        $updateContent['thumb'] = $thumb;
                    }
                }else{
                    //将缩略图片下载到本地(采集获取的缩略图并没有下载到本地)
                    if($collectConfig['is_download_img']) {
                        $local = LocalStorage::instance();
                        $newFileInfo = $local::down($cjdata[0]['thumb']);
                        $imgurl = ltrim($newFileInfo['url'],'/');
                        //给图片加水印
                        if($collectConfig['is_watermark_img']) {
                            try {
                                $imgInfo = pathinfo($imgurl);
                                $waterFilePath = $imgInfo['dirname'] . DIRECTORY_SEPARATOR . $imgInfo['basename'];
                                $imgRes = \think\Image::open($imgurl);
                                $imgRes->water(config('constant.WaterImg'))->save($waterFilePath);
                            } catch (\think\image\Exception $e) {
                                sysoplog('图片水印', '图片水印添加失败'.$e->getMessage());
                            }
                        }
                        $updateContent['thumb'] = $imgurl;
                    }
                }
                $this->app->db->table('module_collect_data')->where(['collect_id' => $id, 'url' => $url])->save($updateContent);
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
        $articles = $ql->get($url)->rules($rules)->range($collectConfig['range_list'])->query()->getData(function ($item) use ($ql, $parseUrlInfo, $collect_id,$collectConfig) {
            $item['collect_id'] = $collect_id;
            //关键字替换
            $replaces = $this->getReplaceKeywords($collectConfig['replace_words']);
            if(count($replaces)>0 && isset($item['description']) && $item['description'] != ''){
                $item['description'] = str_ireplace(array_keys($replaces),array_values($replaces),$item['description']);
            }
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
            $conditionRules = [
                'seo_keywords' => $rules['seo_keywords'],
                'seo_description' => $rules['seo_description'],
                'content' => $rules['content'],
            ];
            $contentData = $ql->get($url)->rules($conditionRules)->query()->getData(function ($item) use ($ql, $parseUrlInfo, $url,$collectConfig) {
                $content = $ql->downloadImage($item['content'], $parseUrlInfo['host'], $url,$collectConfig);
                if($collectConfig['replace_words'] != ''){
                    $replaces = $this->getReplaceKeywords($collectConfig['replace_words']);
                    if(count($replaces)>0){
                        $item['seo_keywords'] = str_ireplace(array_keys($replaces),array_values($replaces),$item['seo_keywords']);
                        $item['seo_description'] = str_ireplace(array_keys($replaces),array_values($replaces),$item['seo_description']);
                        $item['content'] = str_ireplace(array_keys($replaces),array_values($replaces),$content);
                    }
                }else{
                    $item['content'] = $content;
                }
                return $item;
            });
            $ql->destruct();
            return $contentData->all();
        } else {
            return '';
        }
    }

    /**
     * 得到关键字替换的配置信息
     * @param $replace_words
     * @return array
     */
    private function getReplaceKeywords($replace_words)
    {
        $replaceArr = explode("\r\n", $replace_words);
        $replaces = [];
        foreach ($replaceArr as $replace){
            $tpm = explode('|',$replace);
            if(count($tpm) == 2){
                $replaces[$tpm[0]] = $tpm[1];
            }
        }
        return $replaces;
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
            $hasExec = $this->app->db->name('module_collect_job')->where(['catid' => $data['catid'], 'collect_id' => $data['collect_id'],'status'=>0])->find();
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
                $collect_map = ['collect_id' => $collect_id];
                $count = $this->app->db->name('module_collect_data')->where($collect_map)->count();
                if($count>0){
                    if ($jobid > 0) {
                        $limit = 100;//每次导入100条数据
                        $hasFinish = false; //是否完成了导入操作
                        $map = [
                            ['id', '=', $jobid]
                        ];
                        $jobdata = $this->app->db->name('module_collect_job')->where($map)->find();
                        if ($jobdata) {
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
                                $collectData = $this->app->db->name('module_collect_data')->field(['title','seo_keywords','seo_description', 'description', 'thumb', 'author', 'comefrom', 'content', 'UNIX_TIMESTAMP(`time`)' => 'create_at'])
                                    ->fieldRaw("'" . $jobdata["catid"] . "' as 'catid','" . $modelid . "' as 'modelid'")->where($collect_map)->limit($jobdata['records'], $limit)->select()->all();
                                $imports = count($collectData);
                                if ($limit > $count) {
                                    $hasFinish = true;
                                }
                                $this->app->db->name('cms_article')->insertAll($collectData);
                                //将采集的图片信息也写入到附件表中
                                $this->addAttach($collectData);
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
                }else{
                    $this->error('没有采集数据，无法导入');
                }
            } else {
                $this->error('数据错误');
            }
        }
    }

    /**
     * 完成数据导入执行的操作
     * @login true
     */
    public function finshimport()
    {
        if($this->request->isAjax()){
            $job_id = input('jobid',0,'intval');
            if($job_id>0){
                $map = ['id'=>$job_id];
                $result = $this->app->db->name('module_collect_job')->where($map)->update(['status'=>1]);
                if($result){
                    $this->success('导入完成');
                }else{
                    $this->success('数据操作失败');
                }
            }
        }
    }

    /**
     * 将图片信息写入到附件表中
     * @param $articles
     */
    private function addAttach($articles)
    {
        if(count($articles)>0){
            foreach ($articles as $article){
                $thumb = $article['thumb'];
                $contentImags = QueryList::html($article['content'])->find('img')->attrs('src')->all();
                array_push($contentImags,$thumb);
                if(count($contentImags)>0){
                    foreach ($contentImags as $imag){
                        $attachData = getFileInfoByFilepath($imag);
                        if(count($attachData)>0){
                            $attachData['modelid'] = $article['modelid'];
                            $attachData['catid'] = $article['catid'];
                            $attachData['userid'] = session('user.id');
                            $attachData['uploadtime'] = time();
                            $attachData['uploadip'] = $this->request->ip();
                            //将附件数据写入到附件表中
                            AttachmentService::instance()->addAttachment($attachData);
                        }
                    }
                }
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
        $collect_id = input('collect_id',0,'intval');
        if(input('id','','trim') == 'all' && input('collect_id',0,'intval') > 0){
            $this->app->db->name('module_collect_data')->where(['collect_id'=>$collect_id])->delete();
            $this->success('采集数据已经清空');
        }else{
            $this->_delete('module_collect_data');
        }
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

    public function jhtest()
    {
        $cjdata = $this->app->db->table('module_collect_data')->where(['collect_id' => 1, 'url' => 'https://www.php.cn/toutiao-453216.html'])->column('thumb,description');
        dump($cjdata);
    }
}