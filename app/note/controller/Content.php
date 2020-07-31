<?php

namespace app\note\controller;

use app\note\service\CategoryService;
use app\note\service\ContentService;
use think\admin\Controller;
use think\admin\Storage;
use think\admin\storage\LocalStorage;
use app\module\service\AttachmentService;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Think\Db;

/**
 * 笔记内容管理
 * Class Content
 * @package app\note\controller
 */
class Content extends Controller
{
    /**
     * 当前操作数据表
     * @var string
     */
    protected $table = 'noteContent';

    /**
     * 笔记内容列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '笔记列表';
        if ($this->app->request->isGet() && input('action') === 'getdata') {
            $query = $this->_query($this->table)->where(['is_deleted' => 0])->like('title');
            $categoryservice = CategoryService::instance();
            $cateDatas = $categoryservice->getAllCategoryFromCache();
            $catid = input('catid');
            if (!empty($catid) && $catid > 0) {
                $category = $cateDatas[$catid];
                if ($category['haschild']) {
                    $query->whereIn('catid', $category['childids']);
                } else {
                    $query->equal('catid');
                }
            }
            $query->timeBetween('create_at');
            $data = $query->order('id desc')->page(true, false);
            return json(['code' => 0, 'catid' => $catid, 'msg' => '获取数据成功', 'count' => $data['page']['total'], 'data' => $data['list']]);
        } else {
            $this->categoryTree = json_encode(array_values(CategoryService::instance()->getAllCategoryTree(0)));
            $this->fetch();
        }
    }
    /**
     * 生成VuePress侧边栏导航文档
     * 运行vuepress命令 vuepress dev docs
     * @auth true
     */
    public function genVuepressMenu()
    {
        $allCategorys = CategoryService::instance()->getAllCategoryTree(0);
        $tabPrefix = "\t";
        $adapter = new Local(ROOT_PATH);
        $filesystem = new Filesystem($adapter);
        foreach ($allCategorys as $category){
            if(count($category['children']) > 0){
                //生成vuepress的主侧边导航
                foreach ($category['children'] as $cat){
                    $siderbarStr = 'module.exports = {'.PHP_EOL;
                    foreach ($cat['children'] as $category){
                        $categoryPath = '/'.CategoryService::instance()->getCategoryPathByCatid($category['id']);
                        $siderbarStr .= $tabPrefix.'"'.$categoryPath.'": require(\'..'.$categoryPath.'siderbar\'),'.PHP_EOL;
                        $this->genSonNav($category['id']);
                    }
                    $siderbarStr .= '}'.PHP_EOL;
                    $docPath = config('constant.Note.DocsRoot').'.vuepress'.DIRECTORY_SEPARATOR;
                    //生成主要的侧边栏导航配置
                    $fileName = $docPath . config('constant.Note.SiderbarJsName') . '.js';
                    $filesystem->put($fileName,$siderbarStr);
                }
            }
        }
        $this->genMainNav();
        $this->success('恭喜, VuePress导航生成成功！');
    }

    /**
     * 生成主要的nav.js文件导航(笔记头部导航)
     */
    private function genMainNav()
    {
        $allCategorys = CategoryService::instance()->getAllCategoryTree(0);
        $nav = [];
        foreach ($allCategorys as $key => $category){
            //一级栏目
            if(count($category['children'])>0){
                //二级栏目
                $secondNav = [];
                foreach ($category['children'] as $secondChild){
                    $thirdNav = [];
                    if(count($secondChild['children'])>0){
                        //三级栏目
                        foreach ($secondChild['children'] as $thirdChid){
                            if(count($thirdChid['children'])>0){
                                //如果还有四级栏目，则获取第一个4级栏目的第一篇文章
                                $firstUrl = ContentService::instance()->getFirstUrlByCatid($thirdChid['children'][0]['id']);
                            }else{
                                $firstUrl = ContentService::instance()->getFirstUrlByCatid($thirdChid['id']);
                            }
                            $thirdNav[] = [
                                'text' => $thirdChid['title'],
                                'link' => $firstUrl
                            ];
                        }
                    }else{
                        $thirdNav = [
                            'text' => $secondChild['title'],
                            'link' => ContentService::instance()->getFirstUrlByCatid($secondChild['id'])
                        ];
                    }
                    $secondNav[] = [
                        'text' => $secondChild['title'],
                        'items' => $thirdNav
                    ];
                }
                $nav[$key] = [
                    'text' => $category['title'],
                    'items' => $secondNav
                ];
            }else{
                $nav[$key] = [
                    'text' => $category['title'],
                    'link' => ContentService::instance()->getFirstUrlByCatid($category['id'])
                ];
            }
        }
        $adapter = new Local(ROOT_PATH);
        $filesystem = new Filesystem($adapter);
        $docPath = config('constant.Note.DocsRoot').'.vuepress'.DIRECTORY_SEPARATOR;
        $filename = $docPath.'nav.js';
        $filesystem->put($filename,'module.exports = '.json_encode($nav));
    }
    
    /**
     * 生成VuePress子页面的侧边栏导航和具体的文档页面
     * @auth true
     */
    public function genSonNav($curcatid)
    {
        //获取当前栏目下面的所有子栏目
        $category_arr = CategoryService::instance()->getAllCategoryFromCache();
        $currentCat = $category_arr[$curcatid];
        if($currentCat['haschild']){
            $sonCategories = explode(',', $currentCat['childids']);
            array_shift($sonCategories);
        }else{
            $sonCategories = [$curcatid];
        }
        $noteNav = [];
        //父栏目
        $categoryPath = CategoryService::instance()->getCategoryPathByCatid($curcatid);
        $parent_path = config('constant.Note.DocsRoot') . $categoryPath;
        $adapter = new Local(ROOT_PATH);
        $filesystem = new Filesystem($adapter);
        if (!is_dir($parent_path)) {
            $filesystem->createDir($parent_path);
        }
        //子栏目数据
        foreach ($sonCategories as $catid) {
            $notes = $this->app->db->name($this->table)->where(['catid' => $catid, 'is_deleted' => 0])->order('sort ASC,id ASC')->select()->toArray();
            $children = [];
            if(count($notes)>0){
                $categoryPath = CategoryService::instance()->getCategoryPathByCatid($catid);
                $path = config('constant.Note.DocsRoot') . $categoryPath;
                if (!is_dir($path)) {
                    $filesystem->createDir($path);
                }
                foreach ($notes as $note){
                    $children[] = '/'. $categoryPath.$note['filename'];
                    /*//生成md文件内容
                    $markdownFileName = $path . $note['filename'] . '.md';
                    $content = $note['content'];
                    //Front Matter内容
                    if (config('constant.Note.AutoFrontMatter')) {
                        $content = $this->doFrontMatter($note, $catid) . PHP_EOL . $note['content'];
                    }
                    $filesystem->put($markdownFileName,$content);*/
                }
                $noteNav[] = [
                    'title' => $category_arr[$catid]['name'],
                    'collapsable' => true,
                    'children' => $children
                ];
            }
        }
        $filename = $parent_path.config('constant.Note.SiderbarJsName').'.js';
        $filesystem->put($filename,'module.exports = '.json_encode($noteNav));
    }

    /**
     * 得到笔记的Front Matter内容
     * @param $note 笔记内容
     * @param $catid 笔记栏目
     * @return bool|mixed|string
     */
    private function doFrontMatter($note, $catid)
    {
        $tpl = app()->getAppPath() . 'stubs' . DIRECTORY_SEPARATOR . 'front_matter.html';
        $fp = fopen($tpl, "r");
        $str = fread($fp, filesize($tpl));
        $str = str_replace("{title}", $note['title'], $str);
        $str = str_replace("{lang}", 'zh-cn', $str);
        $str = str_replace("{description}", $note['seo_description'], $str);
        $str = str_replace("{seo_description}", $note['seo_description'], $str);
        $str = str_replace("{seo_keywords}", $note['seo_keywords'], $str);
        if(isset($note['create_at'])){
            $str = str_replace("{date}", date('Y-m-d H:i:s', $note['create_at']), $str);
        }
        $str = str_replace("{comments}", $note['allow_comment'] ? true : false, $str);
        $str = str_replace("{search}", true, $str);
        $str = str_replace("{permalink}", CategoryService::instance()->getCategoryPathByCatid($catid) . $note['filename'] . config('constant.Note.FileSuffix'), $str);
        $tagStr = '';
        if ($note['tags'] != '') {
            foreach (explode(',', $note['tags']) as $tag) {
                $tagStr .= ' - ' . trim($tag) . PHP_EOL;
            }
        }
        $str = str_replace("{tags}", $tagStr, $str);
        $categoriesStr = '';
        $CategoryNames = CategoryService::instance()->getCategoryLevelNameByCatid($catid);
        foreach ($CategoryNames as $cat) {
            $categoriesStr .= ' - ' . trim($cat) . PHP_EOL;
        }
        $str = str_replace("{categories}", $categoriesStr, $str);
        fclose($fp);
        return $str;
    }

    /**
     * 编辑器图片上传
     */
    public function upload()
    {
        $file = $this->request->file('editormd-image-file');
        $this->fielSize = $file->getSize();
        $this->extension = $file->getOriginalExtension();
        $this->filemd5 = $file->md5();
        $this->safe = 0;
        if (!in_array($this->extension, explode(',', sysconf('storage.allow_exts')))) {
            return json(['success' => 0, 'message' => '文件上传类型受限，请在后台配置']);
        }
        if (in_array($this->extension, ['php', 'sh'])) {
            return json(['success' => 0, 'message' => '可执行文件禁止上传到本地服务器']);
        }
        $this->name = 'notes/' . Storage::name($file->getPathname(), $this->extension, '', 'md5_file');
        $local = LocalStorage::instance();
        $realpath = dirname($realname = $local->path($this->name, $this->safe));
        file_exists($realpath) && is_dir($realpath) || mkdir($realpath, 0755, true);
        rename($file->getPathname(), $realname);
        $info = $local->info($this->name, $this->safe, $file->getOriginalName());
        //写上传文件到附件表中
        $attachData = [
            'module' => 'notes',
            'catid' => input('catid', 0, 'intval'),
            'filename' => $file->getOriginalName(),
            'filepath' => $info['url'],
            'filesize' => $this->fielSize,
            'fileext' => $this->extension,
            'uploadtime' => time(),
            'userid' => session('user.id'),
            'uploadip' => request()->ip(),
            'filemd5' => $this->filemd5
        ];
        AttachmentService::instance()->addAttachment($attachData);
        return json(['success' => 1, 'message' => '文件上传成功', 'url' => $info['url'], 'alt' => pathinfo($file->getOriginalName())['filename']]);
    }

    /**
     * 对列表数据进行二次处理
     * @param $data
     */
    protected function _index_page_filter(&$data)
    {
        $categorys = CategoryService::instance()->getAllCategoryFromCache();
        foreach ($data as &$vo) {
            $catname = '未知';
            if (isset($categorys[$vo['catid']])) {
                $catname = $categorys[$vo['catid']]['name'];
            }
            $vo['catname'] = $catname;
            $vo['create_at'] = format_datetime($vo['create_at']);
        }
    }

    /**
     * 添加笔记内容
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {

        $catid = input('catid', '0', 'intval');
        if ($catid > 0) {
            $categoryService = CategoryService::instance();
            $cagetorys = $categoryService->getAllCategoryFromCache();
            $catpath = str_replace('-', ',', substr($cagetorys[$catid]['path'], 2));
            $this->vo = ['catpath' => $catpath, 'catid' => $catid];
        }
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑笔记内容
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
     * @param $data
     * @throws \DfaFilter\Exceptions\PdsBusinessException
     * @throws \DfaFilter\Exceptions\PdsSystemException
     */
    protected function _form_filter(&$data)
    {
        if ($this->request->isGet()) {
            $this->categorys = json_encode(CategoryService::instance()->getAllCategoryTree(0));
        } else if ($this->request->isPost()) {
            unset($data['table-align']);//这个是editormd在创建表格的时候会携带的参数
            unset($data['test-editormd-html-code']);//这个是editormd生成的html文档内容
            if (strrpos($data['catpath'], ',') > 0) {
                $catid = substr($data['catpath'], strrpos($data['catpath'], ',') + 1);
                $data['catid'] = $catid;
            } else {
                $data['catid'] = $data['catpath'];
            }
            //判断文件名是否重复
            if ($data['filename'] != '') {
                $map = [
                    ['catid', '=', $data['catid']],
                    ['filename', '=', $data['filename']]
                ];
                if (isset($data['id'])) {
                    $map[] = ['id', '<>', $data['id']];
                }
                $exit = $this->app->db->name($this->table)->where($map)->find();
                if ($exit) {
                    $this->error('文件名重复，请更换文件名');
                }
            }
            $data['tags'] = str_replace('，', ',', $data['tags']);
            $data['content'] = dealBadwords($data['content']);
            if (!isset($data['id'])) {
                $data['create_at'] = time();
            }
        }
    }


/**
 * 添加或更新文档后自动生成md文件和自动设置文件名
 * @param $result
 * @param $data
 */
    protected function _form_result($result, $data)
    {
        if ($result !== false) {
            if ($data['filename'] == '' && !isset($data['id'])) {
                $filename = $this->app->pinyin->abbr($data['title']) . '_' . $result;
                $updateData = [
                    'filename' => $filename
                ];
                $this->app->db->name($this->table)->where(['id' => $result])->update($updateData);
            }else{
                $filename = $data['filename'];
            }
            //生成md文件内容
            $adapter = new Local(ROOT_PATH);
            $filesystem = new Filesystem($adapter);
            $categoryPath = CategoryService::instance()->getCategoryPathByCatid($data['catid']);
            $mdpath = config('constant.Note.DocsRoot') . $categoryPath;
            if (!is_dir(getPublicPath().$mdpath)) {
                $filesystem->createDir($mdpath);
            }
            $markdownFileName = $mdpath . $filename . '.md';
            $content = $data['content'];
            //Front Matter内容
            if (config('constant.Note.AutoFrontMatter')) {
                $content = $this->doFrontMatter($data, $data['catid']) . PHP_EOL . $data['content'];
            }
            $filesystem->put($markdownFileName,$content);
        }
    }

    /**
     * 删除笔记内容
     * @auth true
     * @throws \think\db\exception\DbException
     */
    public function remove()
    {
        $this->_applyFormToken();
        $this->_delete($this->table);
    }
}