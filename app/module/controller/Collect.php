<?php
namespace app\module\controller;

use ql\DownloadImage;
use ql\MyHttp;
use think\admin\Controller;
use QL\QueryList;
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
    protected $collectDefaultFilterTag = ['p','a','script','iframe','table','span','b','img','object','embed','param','div'];
    /**
     * 采集任务列表
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '采集任务列表';
        $this->_query($this->table)->where(['is_deleted'=>0])->order('id desc')->page();
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
        $this->title='创建采集任务';
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
        $this->title='编辑采集任务';
        $this->_applyFormToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 测试采集任务
     * @auth true
     */
    public function testjob()
    {
        $arrlowType = ['list','content'];
        $type = $this->request->request('type','list','trim');
        if(in_array($type,$arrlowType)){
            //1.测试采集列表页
            if($type == 'list'){
                $id = $this->request->request('id',0,'intval');
                if($id){
                    $collectConfig = $this->app->db->name($this->table)->where(['id'=>$id,'is_deleted'=>0])->find();
                    if($collectConfig){

                        //$this->fetch();
                    }else{
                        $this->error('参数错误');
                    }
                }else{
                    $this->error('参数错误');
                }
            }else{
                //测试采集内容页
            }
        }
    }

    /**
     * 执行采集任务
     * @auth true
     */
    public function dojob()
    {

    }

    public function content()
    {
        $ql = QueryList::getInstance();
        $ql->bind('downloadImage',function ($path){
            dump($this);exit;
            $data = $this->getData();
            $ql2 = QueryList::html($data['content']);
            $imgs = $ql2->find('img')->attrs('src');
            dump($imgs);
            exit;
            $data->map(function ($item) use($path){
                //获取图片
                $img = file_get_contents($item['image']);
                $localPath = $path.'/'.md5($img).'.jpg';
                //保存图片到本地路径
                file_put_contents($localPath,$img);
                //data数组中新增一个自定义的本地路径字段
                $item['local_path'] = $localPath;
                return $item;
            });
            //更新data属性
            $this->setData($data);
            return $this;
        });
        $url = 'https://www.php.cn/toutiao-453216.html';
        $rules = [
            'title' => ['h1','text'],
            //'image' => ['.content p img','src'],
            'content' => ['.content p','html'],
            'description' => ['blockquote:eq(0)>p','text'],
        ];
        $data = $ql->get($url)->rules($rules)->query()->downloadImage('img')->getData();
        //$data = $ql->get($url)->find('.content p img')->attrs('src');
        dump($data->all());
        /*$rt = [];
        // 采集文章标题
        $rt['title'] = $ql->find('h1')->text();
        $rt['description'] = $ql->find('blockquote:eq(0)>p')->text();
        // 采集文章内容
        $rt['content'] = $ql->find('.content p')->html();
        dump($rt);*/
    }

    public function plugin()
    {
        $ql = QueryList::use(DownloadImage::class);
        $url = 'https://www.php.cn/toutiao-453216.html';
        $rules = [
            'title' => ['h1','text'],
            'time' => ['.profile>span:eq(1)','text','-i'],
            'content' => ['.content','html','-.share -.tags -.page -blockquote:last -blockquote:first -img:last'],
            'description' => ['blockquote:eq(0)>p','text'],
        ];
        $data = $ql->get($url)->rules($rules)->query()->getData(function($item) use($ql,$url){
            $item['content'] = $ql->downloadImage($item['content'],'www.php.cn',$url);
            return $item;
        });
        dump($data->all());
    }

    public function jhtest2()
    {
        $url = 'https://img.php.cn/upload/article/000/000/015/5ef82f287be6a659.jpg';
        /*$pasthData = pathinfo($url);
        dump($pasthData);
        exit;
        array:3 [▼
  "url" => "/upload/down/ee/8c598a5b22f676cb7ba9edced36827.jpg"
  "key" => "upload/down/ee/8c598a5b22f676cb7ba9edced36827.jpg"
  "file" => "F:/php_study/third/www.jhphpcms.com/public/upload/down/ee/8c598a5b22f676cb7ba9edced36827.jpg"
]
        */
        $local = LocalStorage::instance();
        $info = $local::down($url);
        dump($info);
    }

    public function jhtest()
    {
        //元数据采集规则
        $rules = [
            'title' => ['h2>a','text'],
            'link' => ['h2>a','href'],
            'thumb' => ['.ar-img>a>img','src'],
            'description' => ['.info','text'],
        ];
        //切片选择器
        $range = '.article-list ul';
        $url = 'https://www.php.cn/toutiao.html';
        $data = QueryList::get($url)->rules($rules)->range($range)->query()->getData();
        //打印结果
        print_r($data->all());

        //元数据采集规则
        $rules = [
            'title' => ['h2.entry-title>a','text'],
            'link' => ['h2.entry-title>a','href'],
            'thumb' => ['.thumbnail>a>img','src'],
            'description' => ['.archive-content','text'],
        ];
        //切片选择器
        $range = '#main article';
        $url = 'http://www.ttlsa.com/php/';
        $data = QueryList::get($url)->rules($rules)->range($range)->query()->getData();
        //打印结果
        print_r($data->all());
    }
}