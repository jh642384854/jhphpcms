<?php
/**
 * +----------------------------------------------------------------------
 * | 自定义标签
 * +----------------------------------------------------------------------
 */
namespace app\common\taglib;

use think\template\TagLib;

class Iemoo extends Taglib {

    protected $tags = array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'close'     => ['attr' => 'time,format', 'close' => 0],                           //闭合标签，默认为不闭合
        'open'      => ['attr' => 'name,type', 'close' => 1],
        'cate'      => ['attr' => 'id,name,limit','close' => 1],                                //通用栏目信息
        'position'  => ['attr' => 'name','close' => 1],                                   //通用位置信息
        'link'      => ['attr' => 'name,limit,id','close' => 1],                                   //获取友情链接
        'adv'       => ['attr' => 'name,limit,id','close' => 1],                           //获取广告信息
        'fragment'	=> ['attr' => 'name,type','close' => 1],                              //获取碎片信息
        'list'      => ['attr' => 'id,name,pagesize,where,limit,order','close' => 1],     //通用列表
		'page'      => ['attr' => 'id,name,pagesize,where,limit,order','close' => 1],     //通用单页
       
    );

    // 这是一个闭合标签的简单演示
    public function tagClose($tag)
    {
        $format = empty($tag['format']) ? 'Y-m-d H:i:s' : $tag['format'];
        $time   = empty($tag['time'])   ? time()        : $tag['time'];
        $parse  = '<?php ';
        $parse .= 'echo date("' . $format . '",' . $time . ');';
        $parse .= ' ?>';
        return $parse;
    }

    // 这是一个非闭合标签的简单演示
    public function tagOpen($tag, $content)
    {
        $type   = empty($tag['type']) ? 0 : 1; // 这个type目的是为了区分类型，一般来源是数据库
        $name   = $tag['name']; // name是必填项，这里不做判断了
        $parse  = '<?php ';
        $parse .= '$test_arr=[[1,3,5,7,9],[2,4,6,8,10]];'; // 这里是模拟数据
        $parse .= '$__LIST__ = $test_arr[' . $type . '];';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 通用导航信息
    Public function tagCate($tag, $content)
    {
       
        $tag['id']    = isset($tag['id'])    ? $tag['id']    : '';
		$tag['limit'] = isset($tag['limit']) ? $tag['limit'] : '0';
        $name         = isset($tag['name'])  ? $tag['name']  : 'vo';
		
        if (!empty($tag['id'])) {
            $catestr  = '$__CATE__ = \think\facade\Db::name(\'category\')->where(\'is_menu\',1)->order(\'sort desc,id desc\')->select();';
            $catestr .= '$__LIST__ = get_childs_on($__CATE__,' . $tag['id'] . ');';
        } else {
            $catestr  = '$__CATE__ = \think\facade\Db::name(\'category\')->where(\'is_menu\',1)->order(\'sort desc,id desc\')->select();';
            $catestr .= '$__LIST__ = unlimitedForLayer($__CATE__);';
        }
        //提取前N条数据,因为sql的LIMIT避免不了子栏目的问题
        if (!empty($tag['limit'])) {
            $catestr .= '$__LIST__ = array_slice($__LIST__, 0,' . $tag['limit'] . ');';
        }
		$parse  = '<?php ';
        $parse .= $catestr;
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }


    // 通用位置信息
    Public function tagPosition($tag, $content)
    {
        $name = $tag['name'] ? $tag['name'] : 'position';
        $parse  = '<?php ';
        $parse .= '$__CATE__   = \think\facade\Db::name(\'category\')->select();';
        $parse .= '$__CATEID__ = get_cate_id();';
        $parse .= '$__LIST__   = get_parents($__CATE__,$__CATEID__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= '<?php $' . $name . '[\'url\'] = get_url_list( $' . $name . '); ?>';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }


    // 通用单页列表
    Public function tagPage($tag, $content)
    {
        $name	= isset($tag['name']) ? $tag['name'] : 'page';
        $limit	= isset($tag['limit']) ? $tag['limit'] : '0';

        $parse  = '<?php ';
        $parse .= '$__LIST__   = \think\facade\Db::name(\'page\')->where(\'status\',1)->field(\'id,title,name\')->order(\'sort desc, id desc\')->limit('.$limit.')->select();';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= '<?php $' . $name . '[\'url\'] = get_url_page( $' . $name . '); ?>';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }


	// 获取友情链接
    Public function tagLink($tag, $content)
    {
        $name = isset($tag['name']) ? $tag['name'] : 'link';
        $limit = isset($tag['limit']) ? $tag['limit'] : '0';
        $id   = isset($tag['id'])   ? $tag['id']   : '';
        $parse = '<?php ';
        $parse .= '
			$__WHERE__ =  array();
            $__WHERE__[] =  [\'a.status\', \'=\', 1];
            if (!empty(\'' . $id . '\')) {
                $__WHERE__[] = [\'a.links_type_id\', \'=\', ' . $id . '];
            }';
		$parse .= '
            $__LIST__ = \think\facade\Db::name(\'links\')
            ->alias(\'a\')
            ->leftJoin(\'links_type lt\',\'a.links_type_id = lt.id\')
            ->field(\'a.*,lt.name as type_name\')
            ->where($__WHERE__)
			->limit('.$limit.')
            ->order(\'a.sort desc,a.id desc\')
            ->select();';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }


    // 获取广告信息
    Public function tagAdv($tag, $content)
    {
        $name = isset($tag['name']) ? $tag['name'] : 'adv';
        $limit = isset($tag['limit']) ? $tag['limit'] : '0';
        $id   = isset($tag['id'])   ? $tag['id']   : '';
        $parse = '<?php ';
        $parse .= '
			$__WHERE__ =  array();
            $__WHERE__[] =  [\'a.status\', \'=\', 1];
            if (!empty(\'' . $id . '\')) {
                $__WHERE__[] = [\'a.adv_position_id\', \'=\', ' . $id . '];
            }';
		$parse .= '
            $__LIST__ = \think\facade\Db::name(\'adv\')
            ->alias(\'a\')
            ->leftJoin(\'adv_position ap\',\'a.adv_position_id = ap.id\')
            ->field(\'a.*,ap.name as type_name\')
            ->where($__WHERE__)
			->limit('.$limit.')
            ->order(\'a.sort desc,a.id desc\')
            ->select();';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 通用碎片信息
    Public function tagFragment($tag, $content)
    {
		$name = isset($tag['name']) ? $tag['name']	: "fragment";
		$type = isset($tag['type']) ? $tag['type']	: "content";
      

		$parseStr = '<?php ';
        $parseStr .= ' $__INFO__ = \think\facade\Db::name("fragment")->where("name",\'' . $type . '\')->find();';
        $parseStr .= '?>';

        $parseStr .= '<?php if(!empty($__INFO__)): ?>';
        $parseStr .= '<?php $'.$name.' = $__INFO__; ?>';
        $parseStr .= $content;
        $parseStr .= '<?php endif; ?>';

		if (!empty($parseStr)) {
            return $parseStr;
        }
        return;

    }

    // 通用列表
    Public function tagList($tag, $content)
    {
        $id       = isset($tag['id'])       ? $tag['id']				: '0';						//可以为空
        $name     = isset($tag['name'])     ? $tag['name']				: "list";					//不可为空
        $order    = isset($tag['order'])    ? $tag['order']				: 'sort ASC,id DESC';		//排序
        $limit    = isset($tag['limit'])    ? $tag['limit']				: '10';						//多少条数据
        $where    = isset($tag['where'])    ? $tag['where'] . ' and status = 1 ' : 'status = 1';	//查询条件

		$cateid = $id ? $id : get_cate_id();

		//查询子分类,列表要包含子分类内容
		$all_cate = \think\facade\Db::name('category')->field('id,pid')->select();
		$ids = get_childs_id_str(get_childs_id($all_cate,$cateid),$cateid);

		$where = $where.' and category_id in ('.$ids.')';

        $parse  = '<?php ';
        $parse .= ' $archives = new app\common\taglib\iemoo\Archives;';
		$parse .= ' $__LIST__ = $archives->get_list("'.$where.'","'.$order.'","'.$limit.'");';
        $parse .= ' ?>';

        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
		$parse .= '<?php $' . $name . '[\'url\'] = get_url_show( $' . $name . '); ?>';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }




}