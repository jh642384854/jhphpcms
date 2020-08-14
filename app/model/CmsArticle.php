<?php
declare (strict_types=1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class CmsArticle extends Model
{
    protected $table = 'cms_article';

    protected $resultSetType = '\app\common\Collection';
    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'catid' => 'int',
        'title' => 'string',
        'description' => 'string',
        'seo_keywords' => 'string',
        'seo_description' => 'string',
        'thumb' => 'string',
        'images' => 'string',
        'content' => 'string',
        'posids' => 'string',
        'sort' => 'int',
        'status' => 'int',
        'allow_comment' => 'int',
        'views' => 'int',
        'create_at' => 'int',
        'author' => 'string',
        'comefrom' => 'string',
        'tags' => 'string',
        'modelid' => 'int',
        'is_deleted' => 'int',
    ];
}
