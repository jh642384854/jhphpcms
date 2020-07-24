<?php
namespace app\note\controller;

use app\note\service\CategoryService;
use think\admin\Controller;
use think\admin\Storage;
use think\admin\storage\LocalStorage;
use app\module\service\AttachmentService;

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
            return json(['code' => 0,'catid'=>$catid, 'msg' => '获取数据成功', 'count' => $data['page']['total'], 'data' => $data['list']]);
        } else {
            $this->categoryTree = json_encode(array_values(CategoryService::instance()->getAllCategoryTree(0)));
            $this->fetch();
        }
    }

    public function jhtest()
    {
        $id = 4;
        $data = $this->app->db->name($this->table)->where(['id'=>$id])->find();
        if($data){
            $category_arr = CategoryService::instance()->getAllCategoryFromCache();
            $catid = $data['catid'];
            if(!isset($category_arr[$catid])) return '';
            $dirArr = [];
            $arrparentid = array_filter(explode(',', $category_arr[$catid]['arrparentid'].','.$catid));
            foreach($arrparentid as $catid) {
                array_push($dirArr,$category_arr[$catid]['url_path']);
            }
            echo implode('/',$dirArr).'<br />';

        }

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
        $this->name = 'notes/'.Storage::name($file->getPathname(), $this->extension, '', 'md5_file');
        $local = LocalStorage::instance();
        $realpath = dirname($realname = $local->path($this->name, $this->safe));
        file_exists($realpath) && is_dir($realpath) || mkdir($realpath, 0755, true);
        rename($file->getPathname(), $realname);
        $info = $local->info($this->name, $this->safe, $file->getOriginalName());
        //写上传文件到附件表中
        $attachData = [
            'module'=> 'notes',
            'catid' => input('catid',0,'intval'),
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
        return json(['success' => 1, 'message' => '文件上传成功','url'=>$info['url'],'alt'=>pathinfo($file->getOriginalName())['filename']]);
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

        $catid = input('catid','0','intval');
        if($catid > 0){
            $categoryService = CategoryService::instance();
            $cagetorys = $categoryService->getAllCategoryFromCache();
            $catpath = str_replace('-', ',', substr($cagetorys[$catid]['path'], 2));
            $this->vo = ['catpath'=>$catpath,'catid'=>$catid];
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
        if($this->request->isGet()){
            $this->categorys = json_encode(CategoryService::instance()->getAllCategoryTree(0));
        }else if ($this->request->isPost()){
            unset($data['test-editormd-html-code']);
            if (strrpos($data['catpath'], ',') > 0) {
                $catid = substr($data['catpath'], strrpos($data['catpath'], ',') + 1);
                $data['catid'] = $catid;
            }else{
                $data['catid'] = $data['catpath'];
            }
            //判断文件名是否重复
            if($data['filename'] != ''){
                $map = [
                    ['catid', '=', $data['catid']],
                    ['filename', '=', $data['filename']]
                ];
                if(isset($data['id'])){
                    $map[] = ['id', '<>', $data['id']];
                }
                $exit = $this->app->db->name($this->table)->where($map)->find();
                if($exit){
                    $this->error('文件名重复，请更换文件名');
                }
            }
            $data['tags'] = str_replace('，', ',', $data['tags']);
            $data['content'] = dealBadwords($data['content']);
            if(!isset($data['id'])){
                $data['create_at'] = time();
            }
        }
    }


    protected function _form_result($result,$data)
    {
        if ($result !== false) {
            if($data['filename'] == '' && !isset($data['id'])){
                $updateData = [
                    'filename' => $this->app->pinyin->abbr($data['title']).'_'.$result
                ];
                $this->app->db->name($this->table)->where(['id'=>$result])->update($updateData);
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