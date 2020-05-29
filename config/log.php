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
    // 默认日志记录通道
    'default'      => 'file',
    // 日志记录级别
    'level'        => [],
    // 日志类型记录的通道 ['error'=>'email',...]
    'type_channel' => [],
    // 关闭全局日志写入
    'close'        => true,
    // 全局日志处理 支持闭包
    'processor'    => null,
    // 日志通道列表
    'channels'     => [
        'file' => [
            // 日志记录方式
            'type'           => 'File',
            // 日志保存目录
            'path'           => '',
            // 单文件日志写入
            'single'         => true,
            // 指定日志类型，只有指定了那些类型的日志才会被记录。使用\think\facade\Log来记录日志
            'level'          => ['info','error', 'alert', 'sql'], //debug, info, notice, warning, error, critical, alert, emergency,sql(仅当开启数据库调试模式有效)
            // 独立日志级别
            'apart_level'    => ['error', 'sql'],
            // 每个文件大小 ( 10兆 )
            'file_size'      => 1024 * 1024 * 10,
            // 日志日期格式
            'time_format'    => 'Y-m-d H:i:s',
            // 最大日志文件数量
            'max_files'      => 100,
            // 使用JSON格式记录
            'json'           => false,
            // 日志处理
            'processor'      => null,
            // 关闭通道日志写入
            'close'          => false,
            // 日志输出格式化
            'format'         => '[%s][%s] %s',
            // 是否实时写入
            'realtime_write' => false,
        ],
    ],
];
