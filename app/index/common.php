<?php

use app\cms\service\CategoryService;
use think\facade\Db;

/**
 * 返回指定栏目路径层级
 * @param $catid 栏目id
 * @param $symbol 栏目间隔符
 */
function catpos($catid, $symbol=' > '){
    $category_arr = CategoryService::instance()->getAllCategoryFromCache();
    if(!isset($category_arr[$catid])) return '';
    $siteurl = rtrim(sysconf('base.site_url'),'/');
    $pos = '<a href="'.$siteurl.'">首页</a>'.$symbol;
    $arrparentid = array_filter(explode(',', $category_arr[$catid]['arrparentid'].','.$catid));
    foreach($arrparentid as $catid) {
        $url = getCategoryUrl($category_arr[$catid]);
        if(strpos($url, '://') === false) $url = $siteurl.$url;
        $pos .= '<a href="'.$url.'">'.$category_arr[$catid]['name'].'</a>'.$symbol;
    }
    return $pos;
}

/**
 * 返回指定栏目的层级目录，用来生成目录的url地址
 * @param $catid
 * @return string
 */
function getCatPath($catid){
    $category_arr = CategoryService::instance()->getAllCategoryFromCache();
    $pos = '';
    $arrparentid = array_filter(explode(',', $category_arr[$catid]['arrparentid'].','.$catid));
    foreach($arrparentid as $catid) {
        $pos .= $category_arr[$catid]['url_path'].'_';
    }
    return rtrim($pos,'_');
}

/**
 * 组合多维数组
 * @param $cate
 * @param string $name
 * @param int $pid
 * @return array
 */
function unlimitedForLayer($cate, $name = 'sub', $pid = 0)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $pid) {
            $v[$name] = unlimitedForLayer($cate, $name, $v['id']);
            $v['url'] = getCategoryUrl($v);
            $arr[] = $v;
        }
    }
    return $arr;
}


/**
 * 传递一个父级分类ID返回当前子分类
 * @param $cate
 * @param $pid
 * @return array
 */
function get_childs_on($cate, $pid)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $pid) {
            $v['sub'] = get_childs($cate, $v['id']);
            $v['url'] = getCategoryUrl($v);
            $arr[] = $v;
        }
    }
    return $arr;
}

/**
 * 传递一个父级分类ID返回所有子分类
 * @param $cate
 * @param $pid
 * @return array
 */
function get_childs($cate, $pid)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $pid) {
            $v['url'] = getCategoryUrl($v);
            $arr[] = $v;
            $arr = array_merge($arr, get_childs($cate, $v['id']));
        }
    }
    return $arr;
}
/***
 * 获取当前栏目ID
 * @return mixed
 */
function get_cate_id()
{
    if (\think\facade\Request::has('name')) {
        $urlpath = \think\facade\Request::param('name');
    } else {
        $urlpath = \think\facade\Request::controller();
    }
    $result = CategoryService::instance()->getCategoryByUrlPath($urlpath);
    $catid = 0;
    if (count($result) > 0) {
        $catid = $result['id'];
    }
    return $catid;
}

/**
 * 标签页URL地址
 * @param $v
 * @return bool|string
 */
function get_url_tag($v)
{
    $v['url'] = substr(url('tag', ['id' => $v['id']]), 6);
    return $v['url'];
}
// 获取单页地址
function get_url_page($v)
{
    $v['url'] = substr(url('page', ['name' => $v['url_path']]), 6);
    return $v['url'];
}

// 获取列表链接地址
function get_url_list($v)
{
    //判断是否直接跳转
    if (trim($v['link_url']) == '') {
        $v['url'] = substr(url('index', ['name' => getCatPath($v['id'])]), 6);
    }
    return $v['url'];
}

// 获取详情URL
function get_url_show($v)
{
    if ($v) {
        $url = substr(url('show', ['name' => getCatPath($v['catid']), 'id' => $v['id']]), 6);//这里截取了/index前缀。
    }
    return $url;
}

/**
 * 获取栏目的地址
 * @param $v
 * @return bool|string
 */
function getCategoryUrl($v){
  if($v['modelid']>0){
    $url = get_url_list($v);
  }else{
      $url = get_url_page($v);
  }
  return $url;
}

/**
 * 生成缩略图
 * @param $img
 * @param $width
 * @param $height
 */
function thumb($img, $width, $height)
{
    $imagePath = getImage($img);
    if ($imagePath != config('constant.DefaultImg')) {
        try {
            $imgInfo = pathinfo($img);
            $thumbFilePath = $imgInfo['dirname'] . '/thumb_' . $width . '_' . $height . '_' . $imgInfo['basename'];
            if (!file_exists($thumbFilePath)) {
                $imgRes = \think\Image::open($img);
                $imgRes->thumb($width, $height)->save($thumbFilePath);
            }
            $thumbPath = $thumbFilePath;
        } catch (\think\image\Exception $e) {
            $thumbPath = config('constant.DefaultImg');
        }
    } else {
        $thumbPath = config('constant.DefaultImg');
    }
    return $thumbPath;
}

/**
 * 获取资源图片
 * @param $img
 * @return string
 */
function getImage($img)
{
    if (empty($img) || $img == '') {
        $img = config('constant.DefaultImg');
    }
    return $img;
}

/**
 * 获取碎片信息
 * @param $data
 * @return string
 */
function get_block_content($data)
{
    $content = '';
    if (count($data) > 0) {
        if ($data[0]['type'] === 'image') {
            $content = '<img src="' . $data[0]['content'] . '" class="img-responsive" />';
        } else {
            $content = $data[0]['content'];
        }
    }
    return $content;
}

function getNewsTagsLink($tags){
    if($tags != ''){
        $tags = explode(',', $tags);
        $newTag = [];
        foreach ($tags as $tag) {
            $exits = Db::name('cms_tag')->where(['name' => $tag])->field('id')->find();
            if ($exits) {
                $newTag[] = [
                    'id' => $exits['id'],
                    'name' => $tag,
                    'url' => get_url_tag($exits)
                ];
            }
        }
        return $newTag;
    }else{
        return '';
    }
}