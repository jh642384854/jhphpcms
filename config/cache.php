<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2020 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://demo.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

return [
    // 默认缓存驱动
    'default' => 'file',
    //应用中使用的cache Key
    'usekey' => [
        'category' => [
            'key' => 'AllCategory',
            'describe' => '缓存所有栏目'
        ],
        'notecategory' => [
            'key' => 'AllNoteCategory',
            'describe' => '缓存所有笔记栏目'
        ],
        'model' => [
            'key' => 'AllModel',
            'describe' => '缓存所有的模型'
        ],
        'recommend' => [
            'key' => 'AllRecommend',
            'describe' => '缓存所有的推荐位'
        ],
        'field' => [
            'key' => 'AllField',
            'describe' => '缓存指定模型的所有字段'
        ],
        'tags' => [
            'key' => 'AllTags',
            'describe' => '缓存所有的标签信息'
        ],
        'cascader' => [
            'key' => 'Cascader',
            'describe' => '缓存指定级联菜单数据'
        ],
        'config' => [
            'key' => 'Config',
            'describe' => '缓存指定系统配置数据'
        ],
        'keylink' => [
            'key' => 'Keylink',
            'describe' => '缓存关键字链接数据'
        ]
    ],
    // 缓存连接方式配置
    'stores' => [
        'file' => [
            // 驱动方式
            'type' => 'File',
            // 缓存保存目录
            'path' => app()->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
            // 缓存名称前缀
            'prefix' => '',
            // 缓存有效期 0 表示永久缓存
            'expire' => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制
            'serialize' => [],
        ],
    ],
];
