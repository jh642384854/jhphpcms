<?php

namespace app\common\taglib;

use app\cms\service\CategoryService;
use think\template\TagLib;

class Jhcms extends TagLib
{
    /**
     * 定义标签列表
     * @var array
     */
    protected $tags = [
        // 标签定义： [attr：属性列表，close：是否闭合（0 或者1 默认1）， alias：标签别名，level 嵌套层次]
        'list' => ['attr' => 'name,limit,order,fields,where,catid', 'close' => 1],
        'latest' => ['attr' => 'name,limit,order,fields,where', 'close' => 1],  //最新资讯
        'hit' => ['attr' => 'name,limit,fields,where', 'close' => 1],     //浏览量最高
        'close' => ['attr' => 'time,format', 'close' => 0],
        'block' => ['attr' => 'name', 'close' => 0],
        'link' => ['attr' => 'name,limit,order,fields,where', 'close' => 1],
        'adv' => ['attr' => 'cid,name,limit,order,fields,where', 'close' => 1],
        'recommend' => ['attr' => 'cid,name,limit,order,fields,where', 'close' => 1],
        'menu' => ['attr' => 'name,limit,order', 'close' => 1],
        'tag' => ['attr' => 'name,limit,order,fields', 'close' => 1],
        'form' => ['attr' => 'name,limit,order,fields', 'close' => 0],  //自定义表单
    ];

    // 这是一个闭合标签的简单演示
    public function tagClose($tag)
    {
        $format = empty($tag['format']) ? 'Y-m-d H:i:s' : $tag['format'];
        $time = empty($tag['time']) ? time() : $tag['time'];
        $parse = '<?php ';
        $parse .= 'echo date("' . $format . '",' . $time . ');';
        $parse .= ' ?>';
        return $parse;
    }

    /**
     * 通用列表标签
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagList($tag, $content)
    {
        $catid = $tag['catid'];
        $name = isset($tag['name']) ? $tag['name'] : "list";
        $limit = isset($tag['limit']) ? intval($tag['limit']) : 10; //查询条数
        $order = isset($tag['order']) ? $tag['order'] : 'id DESC'; //排序
        $fields = isset($tag['fields']) ? $tag['fields'] : '*';  //查询字段
        $where = isset($tag['where']) ? $tag['where'] . ' and status = 1 ' : 'status = 1';    //查询条件

        $categoryData = CategoryService::instance()->getAllCategoryFromCache();
        if ($categoryData[$catid]) {
            if ($categoryData[$catid]['haschild']) {
                $where .= ' AND catid IN (' . $categoryData[$catid]['childids'] . ')';
            } else {
                $where .= ' AND catid = ' . $catid;
            }
        }

        $parse = '<?php ';
        $parse .= '$__LIST__ = think\facade\Db::table("cms_article")->where("' . $where . '")->field("' . $fields . '")->limit(' . $limit . ')->order("' . $order . '")->select();';
        $parse .= '?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= '<?php $' . $name . '["url"] = get_url_show($' . $name . '); ?>';
        $parse .= '<?php $' . $name . '["thumb"] = getImage($' . $name . '["thumb"]); ?>';
        $parse .= '<?php $' . $name . '["tags"] = getNewsTagsLink($' . $name . '["tags"]); ?>';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /**
     * 获取最新资讯
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagLatest($tag, $content)
    {
        $name = isset($tag['name']) ? $tag['name'] : "list";
        $limit = isset($tag['limit']) ? intval($tag['limit']) : 10; //查询条数
        $order = isset($tag['order']) ? $tag['order'] : 'id DESC'; //排序
        $fields = isset($tag['fields']) ? $tag['fields'] : '*';  //查询字段
        $where = isset($tag['where']) ? $tag['where'] . ' and status = 1 ' : 'status = 1';    //查询条件

        $parse = '<?php ';
        $parse .= '$__LIST__ = think\facade\Db::table("cms_article")->where("' . $where . '")->field("' . $fields . '")->limit(' . $limit . ')->order("' . $order . '")->select();';
        $parse .= '?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= '<?php $' . $name . '["url"] = get_url_show($' . $name . '); ?>';
        $parse .= '<?php $' . $name . '["thumb"] = getImage($' . $name . '["thumb"]); ?>';
        $parse .= '<?php $' . $name . '["tags"] = getNewsTagsLink($' . $name . '["tags"]); ?>';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /**
     * 获取点击量最高数据
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagHit($tag, $content)
    {
        $name = isset($tag['name']) ? $tag['name'] : "list";
        $limit = isset($tag['limit']) ? intval($tag['limit']) : 10; //查询条数
        $order = 'views DESC,id DESC'; //排序
        $fields = isset($tag['fields']) ? $tag['fields'] : '*';  //查询字段
        $where = isset($tag['where']) ? $tag['where'] . ' and status = 1 ' : 'status = 1';    //查询条件

        $parse = '<?php ';
        $parse .= '$__LIST__ = think\facade\Db::table("cms_article")->where("' . $where . '")->field("' . $fields . '")->limit(' . $limit . ')->order("' . $order . '")->select();';
        $parse .= '?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= '<?php $' . $name . '["url"] = get_url_show($' . $name . '); ?>';
        $parse .= '<?php $' . $name . '["thumb"] = getImage($' . $name . '["thumb"]); ?>';
        $parse .= '<?php $' . $name . '["tags"] = getNewsTagsLink($' . $name . '["tags"]); ?>';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /**
     * 推荐位列表
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagRecommend($tag, $content)
    {
        $posid = isset($tag['posid']) ? intval($tag['posid']) : 1; //查询条数
        $limit = isset($tag['limit']) ? intval($tag['limit']) : 10; //查询条数
        $order = isset($tag['order']) ? $tag['order'] : 'b.id DESC'; //排序
        $fields = isset($tag['fields']) ? $tag['fields'] : 'b.*';  //查询字段
        $where = isset($tag['where']) ? $tag['where'] . ' and b.is_deleted = 0 ' : 'b.is_deleted = 0';    //查询条件
        $name = isset($tag['name']) ? $tag['name'] : "list";

        $where .= ' AND a.posid = ' . $posid;

        $parse = '<?php ';
        $parse .= '$__LIST__ = \think\facade\Db::table("cms_recommend_data")->alias("a")->leftJoin("cms_article b","a.aid = b.id")->where("' . $where . '")->field("' . $fields . '")->limit(' . $limit . ')->order("' . $order . '")->select();';
        $parse .= '?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= '<?php $' . $name . '["url"] = get_url_show($' . $name . '); ?>';
        $parse .= '<?php $' . $name . '["thumb"] = getImage($' . $name . '["thumb"]); ?>';
        $parse .= '<?php $' . $name . '["tags"] = getNewsTagsLink($' . $name . '["tags"]); ?>';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /**
     * 自定义表单标签
     * @param $tag
     * @return string
     */
    public function tagBlock($tag)
    {
        $name = $tag['name'];
        $parse = '<?php ';
        $parse .= ' $__DATA__ = \think\facade\Db::name("module_diy_form")->where("tablename",\'' . $name . '\')->column("id");';
        $parse .= 'echo get_block_content($__DATA__);';
        $parse .= '?>';
        return $parse;
    }

    /**
     * 碎片标签
     * @param $tag
     * @return string
     */
    public function tagForm($tag)
    {
        $name = $tag['name'];
        $parse = '<?php ';
        $parse .= ' $__DATA__ = \think\facade\Db::name("cms_block")->where("alias",\'' . $name . '\')->column("content,type");';
        $parse .= 'echo get_block_content($__DATA__);';
        $parse .= '?>';
        return $parse;
    }

    /**
     * 友情链接
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagLink($tag, $content)
    {
        $limit = isset($tag['limit']) ? intval($tag['limit']) : 10; //查询条数
        $order = isset($tag['order']) ? $tag['order'] : 'id DESC'; //排序
        $fields = isset($tag['fields']) ? $tag['fields'] : '*';  //查询字段
        $where = isset($tag['where']) ? $tag['where'] . ' and is_deleted = 0 ' : 'is_deleted = 0';    //查询条件
        $name = isset($tag['name']) ? $tag['name'] : "list";

        $parse = '<?php ';
        $parse .= '$__LIST__ = think\facade\Db::table("module_link")->where("' . $where . '")->field("' . $fields . '")->limit(' . $limit . ')->order("' . $order . '")->select();';
        $parse .= '?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /**
     * 广告列表
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagAdv($tag, $content)
    {
        $cid = isset($tag['cid']) ? intval($tag['cid']) : 1; //查询条数
        $limit = isset($tag['limit']) ? intval($tag['limit']) : 10; //查询条数
        $order = isset($tag['order']) ? $tag['order'] : 'id DESC'; //排序
        $fields = isset($tag['fields']) ? $tag['fields'] : '*';  //查询字段
        $where = isset($tag['where']) ? $tag['where'] . ' and is_deleted = 0 ' : 'is_deleted = 0';    //查询条件
        $name = isset($tag['name']) ? $tag['name'] : "list";

        $where .= ' AND cid = ' . $cid;

        $parse = '<?php ';
        $parse .= '$__LIST__ = think\facade\Db::table("module_adv")->where("' . $where . '")->field("' . $fields . '")->limit(' . $limit . ')->order("' . $order . '")->select();';
        $parse .= '?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }



    /**
     * 菜单列表
     * @param $tag
     * @param $content
     */
    public function tagMenu($tag, $content)
    {
        $pid = isset($tag['pid']) ? intval($tag['pid']) : 0; //查询条数
        $limit = isset($tag['limit']) ? intval($tag['limit']) : 10; //查询条数
        $order = isset($tag['order']) ? $tag['order'] : 'sort ASC,id ASC'; //排序
        $name = isset($tag['name']) ? $tag['name'] : "list";

        if (!empty($pid)) {
            $catestr = '$__CATE__ = \think\facade\Db::name("cms_category")->where("is_menu",1)->order("' . $order . '")->select();';
            $catestr .= '$__LIST__ = get_childs_on($__CATE__,' . $pid . ');';
        } else {
            $catestr = '$__CATE__ = \think\facade\Db::name("cms_category")->where("is_menu",1)->order("' . $order . '")->select();';
            $catestr .= '$__LIST__ = unlimitedForLayer($__CATE__);';
        }
        //提取前N条数据,因为sql的LIMIT避免不了子栏目的问题
        if (!empty($limit)) {
            $catestr .= '$__LIST__ = array_slice($__LIST__, 0,' . $limit . ');';
        }

        $parse = '<?php ';
        $parse .= $catestr;
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /**
     * 标签列表
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagTag($tag, $content)
    {
        $limit = isset($tag['limit']) ? intval($tag['limit']) : 10; //查询条数
        $order = isset($tag['order']) ? $tag['order'] : 'nums DESC,id DESC'; //排序
        $fields = isset($tag['fields']) ? $tag['fields'] : '*';  //查询字段
        $name = isset($tag['name']) ? $tag['name'] : "list";

        $parse = '<?php ';
        $parse .= '$__LIST__ = \think\facade\Db::table("cms_tag")->field("' . $fields . '")->limit(' . $limit . ')->order("' . $order . '")->select();';
        $parse .= '?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= '<?php $' . $name . '["url"] = get_url_tag($' . $name . '); ?>';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }
}