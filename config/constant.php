<?php
return [
    'UploadTips' => '文件最大2Mb，支持bmp/png/jpeg/jpg/gif格式',
    'advCatType' => [
        'img' => '图片广告',
        'code' => '代码广告'
    ],
    //附件类别
    'attachmentType' => [
        'img' => [
            'name' => '图片',
            'suffix' => 'jpg,jpeg,png,gif',
            'icon' => 'fa fa-file-photo-o'
        ],
        'doc' => [
            'name' => '文档',
            'suffix' => 'doc,docx,xls,xlsx,ppt,pptx,pdf',
            'icon' => [
                'pdf' => 'fa-file-pdf-o',
                'doc' => 'fa fa-file-word-o',
                'docx' => 'fa fa-file-word-o',
                'xls' => 'fa fa-file-excel-o',
                'xlsx' => 'fa fa-file-excel-o',
                'ppt' => 'fa fa-file-powerpoint-o',
                'pptx' => 'fa fa-file-powerpoint-o',
            ],
        ],
        'zip' => [
            'name' => '压缩包',
            'suffix' => 'zip,rar,tar.gz,tar',
            'icon' => 'fa fa-file-zip-o'
        ],
        'mp3' => [
            'name' => '音频',
            'suffix' => 'mp3',
            'icon' => 'fa fa-file-sound-o'
        ],
        'mp4' => [
            'name' => '视频',
            'suffix' => 'mp4,avi,rmvb,flv,rm',
            'icon' => 'fa fa-file-video-o'
        ]
    ],
    //自定义模型所支持的字段类型
    'modelFieldType' => [
        'text' => [
            'name' => '单行文本',
            //第一个值是默认的字段类型
            'field_list' => ['varchar', 'char']
        ],
        'textarea' => [
            'name' => '多行文本',
            'field_list' => ['varchar', 'text']
        ],
        'richtext' => [
            'name' => '富文本',
            'field_list' => ['text']
        ],
        'number' => [
            'name' => '数字',
            'field_list' => ['int', 'tinyint', 'smallint', 'mediumint', 'integer', 'bigint', 'real', 'double', 'float', 'decimal', 'numeric']
        ],
        'date' => [
            'name' => '日期',
            'field_list' => ['date']
        ],
        'daterange' => [
            'name' => '日期带范围',
            'field_list' => ['varchar']
        ],
        'time' => [
            'name' => '时间',
            'field_list' => ['time']
        ],
        'timerange' => [
            'name' => '时间带范围',
            'field_list' => ['varchar']
        ],
        'datetime' => [
            'name' => '日期时间',
            'field_list' => ['int', 'timestamp', 'datetime']
        ],
        'datetimerange' => [
            'name' => '日期时间带范围',
            'field_list' => ['varchar']
        ],
        'select' => [
            'name' => '单选下拉框',
            'field_list' => ['varchar', 'char', 'int']
        ],
        'selects' => [
            'name' => '多选下拉框',
            'field_list' => ['varchar', 'char']
        ],
        'radio' => [
            'name' => '单选按钮',
            'field_list' => ['varchar', 'char', 'int']
        ],
        'checkbox' => [
            'name' => '复选按钮',
            'field_list' => ['varchar', 'char', 'int']
        ],
        'image' => [
            'name' => '单图片',
            'field_list' => ['varchar', 'char']
        ],
        'images' => [
            'name' => '多图片',
            'field_list' => ['varchar', 'text']
        ],
        'file' => [
            'name' => '单文件',
            'field_list' => ['varchar', 'char']
        ],
        'files' => [
            'name' => '多文件',
            'field_list' => ['varchar', 'text']
        ],
        'color_select' => [
            'name' => '颜色选择器',
            'field_list' => ['char', 'varchar']
        ],
        'silder' => [
            'name' => '滑块',
            'field_list' => ['char', 'varchar']
        ],
        'score' => [
            'name' => '评分',
            'field_list' => ['char', 'varchar']
        ],
        /*'transfer' => [
            'name' => '穿梭框',
            'field_list' => ['varchar', 'char']
        ],
        'map' => [
            'name' => '地图',
            'field_list' => ['varchar', 'text']
        ],
        'custom' => [
            'name' => '万能字段',
            'field_list' => ['varchar', 'text']
        ],*/
    ],
    //模型字段内置的正则表达式
    'modelPattern' => [
        'number' => [
            'name' => '数字',
            'rule' => '/^[0-9.-]+$/'
        ],
        'intger' => [
            'name' => '整数',
            'rule' => '/^[0-9-]+$/'
        ],
        'letter' => [
            'name' => '字母',
            'rule' => '/^[a-z]+$/i'
        ],
        'letter_number' => [
            'name' => '数字+字母',
            'rule' => '/^[0-9a-z]+$/i'
        ],
        'email' => [
            'name' => 'Email',
            'rule' => '/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/'
        ],
        'QQ' => [
            'name' => 'QQ',
            'rule' => '/^[0-9]{5,20}$/'
        ],
        'link' => [
            'name' => '超链接',
            'rule' => '/[a-zA-z]+://[^\s]*/'
        ],
        'phone' => [
            'name' => '手机号码',
            'rule' => '/^(1)[0-9]{10}$/'
        ],
        'tel' => [
            'name' => '电话号码',
            'rule' => '/^[0-9-]{6,13}$/'
        ],
        'post_code' => [
            'name' => '邮政编码',
            'rule' => '/^[0-9]{6}$/'
        ],
        'id_card' => [
            'name' => '身份证号',
            'rule' => '/^\d{17}[\d|x]|\d{15}$/'
        ],
        'chstr' => [
            'name' => '中文字符',
            'rule' => '/^[^\x00-\xff]$/' //[\u4e00-\u9fa5]
        ]
    ],
    'YesOrNo' => [
        '1' => '是',
        '0' => '否'
    ],
    //select下拉框数据来源
    'selectFieldDataSource' => [
        'diyconfig' => '系统通用配置',
        'moduledata' => '系统模块'
    ],
    'selectDataModule' => [
        'catgory' => '栏目列表'
    ],
    //字段索引类型
    'fieldIndexType' => ['Normal', 'Unique', 'Full Text'],
    //字段索引方法
    'fieldIndexFun' => ['BTREE', 'HASH']
];