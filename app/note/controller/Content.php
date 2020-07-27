<?php

namespace app\note\controller;

use app\note\service\CategoryService;
use think\admin\Controller;
use think\admin\Storage;
use think\admin\storage\LocalStorage;
use app\module\service\AttachmentService;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

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
            $query->dateBetween('create_at');
            $data = $query->order('id desc')->page(true, false);
            return json(['code' => 0, 'catid' => $catid, 'msg' => '获取数据成功', 'count' => $data['page']['total'], 'data' => $data['list']]);
        } else {
            $this->categoryTree = json_encode(array_values(CategoryService::instance()->getAllCategoryTree(0)));
            $this->fetch();
        }
    }
    /**
     * 生成VuePress侧边栏导航文档
     * @auth true
     */
    public function jhtest()
    {
        $allCategorys = CategoryService::instance()->getAllCategoryTree(0);
        foreach ($allCategorys as $category){
            if($category['_level'] != 3 || count($category['children']) > 0){
                foreach ($category['children'] as $cat){
                    foreach ($cat['children'] as $category){
                        $this->generatevuepressnav($category['id']);
                    }
                }
            }
        }
    }
    
    /**
     * 生成VuePress侧边栏导航文档
     * @auth true
     */
    public function generatevuepressnav($curcatid)
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
        if (!isLinuxEnv()) {
            $parent_path = '.' . $parent_path;
        }
        if (!is_dir($parent_path)) {
            $adapter = new Local(ROOT_PATH);
            $filesystem = new Filesystem($adapter);
            $filesystem->createDir($parent_path);
        }
        //子栏目数据
        foreach ($sonCategories as $catid) {
            $notes = $this->app->db->name($this->table)->where(['catid' => $catid, 'is_deleted' => 0])->order('sort ASC,id DESC')->select()->toArray();
            $children = [];
            if(count($notes)>0){
                $categoryPath = CategoryService::instance()->getCategoryPathByCatid($catid);
                $path = config('constant.Note.DocsRoot') . $categoryPath;
                if (!isLinuxEnv()) {
                    $path = '.' . $path;
                }
                if (!is_dir($path)) {
                    $adapter = new Local(ROOT_PATH);
                    $filesystem = new Filesystem($adapter);
                    $filesystem->createDir($path);
                }

                foreach ($notes as $note){
                    $children[] = '/'. $categoryPath.$note['filename'];

                    //生成md文件内容
                    $fileName = $path . $note['filename'] . '.md';
                    $handle = fopen($fileName, "w");
                    $content = $note['content'];
                    //Front Matter内容
                    if (config('constant.Note.AutoFrontMatter')) {
                        $content = $this->doFrontMatter($note, $catid) . PHP_EOL . $note['content'];
                    }
                    fwrite($handle, $content);
                    fclose($handle);

                }
                $noteNav[] = [
                    'title' => $category_arr[$catid]['name'],
                    'collapsable' => true,
                    'children' => $children
                ];
            }
        }
        $filename = $parent_path.config('constant.Note.SiderbarJsName').'.js';
        $handle = fopen($filename, "w");
        fwrite($handle, 'module.exports = '.json_encode($noteNav));
        fclose($handle);
    }

    /**
     * 将指定栏目的文档生成为.md文件
     * @auth true
     */
    public function generatemd()
    {
        $catid = 34;
        //获取当前栏目下面的所有子栏目
        $category_arr = CategoryService::instance()->getAllCategoryFromCache();
        $currentCat = $category_arr[$catid];
        if ($currentCat['haschild']) {
            $sonCategories = explode(',', $currentCat['childids']);
            array_shift($sonCategories);
            foreach ($sonCategories as $catid) {
                $this->dogeneratemd($catid);
            }
        } else {
            $this->dogeneratemd($catid);
        }
    }

    private function dogeneratemd($catid)
    {
        //查询栏目下面的所有文档，并生成为.md文件
        $notes = $this->app->db->name($this->table)->where(['catid' => $catid, 'is_deleted' => 0])->select()->toArray();
        if (count($notes) > 0) {
            //文档保存路径
            $categoryPath = CategoryService::instance()->getCategoryPathByCatid($catid);
            $path = config('constant.Note.DocsRoot') . $categoryPath;
            if (!isLinuxEnv()) {
                $path = '.' . $path;
            }
            if (!is_dir($path)) {
                $adapter = new Local(ROOT_PATH);
                $filesystem = new Filesystem($adapter);
                $filesystem->createDir($path);
            }

            foreach ($notes as $note) {
                $fileName = $path . $note['filename'] . '.md';
                $handle = fopen($fileName, "w");
                $content = $note['content'];
                //Front Matter内容
                if (config('constant.Note.AutoFrontMatter')) {
                    $content = $this->doFrontMatter($note, $catid) . PHP_EOL . $note['content'];
                }
                fwrite($handle, $content);
                fclose($handle);
            }
        }
        return true;
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
        $str = str_replace("{description}", $note['description'], $str);
        $str = str_replace("{seo_description}", $note['seo_description'], $str);
        $str = str_replace("{seo_keywords}", $note['seo_keywords'], $str);
        $str = str_replace("{date}", date('Y-m-d H:i:s', $note['create_at']), $str);
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
            unset($data['test-editormd-html-code']);
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


    protected function _form_result($result, $data)
    {
        if ($result !== false) {
            if ($data['filename'] == '' && !isset($data['id'])) {
                $updateData = [
                    'filename' => $this->app->pinyin->abbr($data['title']) . '_' . $result
                ];
                $this->app->db->name($this->table)->where(['id' => $result])->update($updateData);
            }
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