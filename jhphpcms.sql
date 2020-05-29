/*
Navicat MySQL Data Transfer

Source Server         : 本机连接
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : jhphpcms

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2020-05-29 17:46:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for cms_article
-- ----------------------------
DROP TABLE IF EXISTS `cms_article`;
CREATE TABLE `cms_article` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `title` char(80) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `style` char(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `thumb` char(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `keywords` char(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` char(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `tags` char(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `posids` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `islink` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `username` char(20) COLLATE utf8mb4_bin NOT NULL,
  `author` char(20) COLLATE utf8mb4_bin NOT NULL,
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `update_at` int(10) unsigned NOT NULL DEFAULT '0',
  `create_at` int(10) unsigned NOT NULL DEFAULT '0',
  `news_title` varchar(150) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '新闻标题',
  `new_catid` int(10) NOT NULL DEFAULT '0' COMMENT '栏目ID',
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`sort`,`id`),
  KEY `listorder` (`catid`,`status`,`sort`,`id`),
  KEY `catid` (`catid`,`status`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of cms_article
-- ----------------------------

-- ----------------------------
-- Table structure for cms_article_data
-- ----------------------------
DROP TABLE IF EXISTS `cms_article_data`;
CREATE TABLE `cms_article_data` (
  `id` mediumint(8) NOT NULL,
  `content` text COLLATE utf8mb4_bin NOT NULL COMMENT '内容'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of cms_article_data
-- ----------------------------

-- ----------------------------
-- Table structure for cms_category
-- ----------------------------
DROP TABLE IF EXISTS `cms_category`;
CREATE TABLE `cms_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `en_name` varchar(200) NOT NULL COMMENT '分类英文名称',
  `modelid` smallint(5) NOT NULL DEFAULT '0' COMMENT '模型ID',
  `parent_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '分类父id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态,1:发布,0:不发布',
  `sort` smallint(5) NOT NULL DEFAULT '1' COMMENT '排序',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '分类描述',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '分类层级关系路径',
  `is_menu` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否作为菜单显示',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '分类图片',
  `seo_title` varchar(100) NOT NULL DEFAULT '',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(255) NOT NULL DEFAULT '',
  `category_template` varchar(50) NOT NULL DEFAULT '' COMMENT '栏目页模板页',
  `list_template` varchar(50) NOT NULL DEFAULT '' COMMENT '分类列表模板',
  `show_template` varchar(50) NOT NULL DEFAULT '' COMMENT '分类文章页模板',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COMMENT='文章分类表';

-- ----------------------------
-- Records of cms_category
-- ----------------------------
INSERT INTO `cms_category` VALUES ('1', '新闻中心', 'News', '6', '0', '1', '1', '栏目描述', '0-1', '1', '', 'SEO标题', 'SEO关键字', 'SEO描述', '', '', '');
INSERT INTO `cms_category` VALUES ('15', '公司新闻', '', '6', '1', '1', '1', '', '0-1-15', '0', '', '', '', '', 'category.html', 'list.html', 'show.html');
INSERT INTO `cms_category` VALUES ('16', '三级栏目', '', '6', '15', '1', '1', '', '0-1-15-16', '0', '', '', '', '', 'category.html', 'list.html', 'show.html');
INSERT INTO `cms_category` VALUES ('17', '产品中心', 'Products', '7', '0', '1', '1', '', '0-17', '0', '', '', '', '', 'category.html', 'list.html', 'show.html');

-- ----------------------------
-- Table structure for cms_model
-- ----------------------------
DROP TABLE IF EXISTS `cms_model`;
CREATE TABLE `cms_model` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '模型名称',
  `description` varchar(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '描述',
  `tablename` char(20) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '表名称',
  `status` tinyint(1) NOT NULL COMMENT '状态',
  `category_template` varchar(50) COLLATE utf8mb4_bin NOT NULL COMMENT '栏目页模版',
  `list_template` varchar(50) COLLATE utf8mb4_bin NOT NULL COMMENT '列表页模版',
  `show_template` varchar(50) COLLATE utf8mb4_bin NOT NULL COMMENT '内容也模版',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of cms_model
-- ----------------------------
INSERT INTO `cms_model` VALUES ('6', '文章模型', '文章模型', 'cms_article', '1', 'category.html', 'list.html', 'show.html', '2020-05-29 15:14:02');
INSERT INTO `cms_model` VALUES ('7', '图片模型', '图片模型', 'cms_picture', '1', 'category_picture.html', 'list_picture.html', 'show_picture.html', '2020-05-29 15:14:04');

-- ----------------------------
-- Table structure for cms_model_field
-- ----------------------------
DROP TABLE IF EXISTS `cms_model_field`;
CREATE TABLE `cms_model_field` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `modelid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '模型ID',
  `field` varchar(20) COLLATE utf8mb4_bin NOT NULL COMMENT '数据表字段名',
  `title` varchar(30) COLLATE utf8mb4_bin NOT NULL COMMENT '页面展示字段名称',
  `tips` text COLLATE utf8mb4_bin NOT NULL COMMENT '信息提示',
  `minlength` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '字段最大值',
  `maxlength` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '字段最小值',
  `pattern` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '数据校验(正则表达式)',
  `errortips` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '未校验通过的错误提示',
  `formtype` varchar(20) COLLATE utf8mb4_bin NOT NULL COMMENT '字段类型',
  `setting` mediumtext COLLATE utf8mb4_bin NOT NULL COMMENT '字段设置信息',
  `iscore` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是核心字段',
  `isindex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为该字段创建索引',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否作为主表字段',
  `isunique` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '值是否唯一',
  `isbase` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否作为基本信息 该信息将在添加页面左侧显示',
  `issearch` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否作为搜索条件',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用该字段',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `testfield` char(100) COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `modelid` (`modelid`,`disabled`) USING BTREE,
  KEY `field` (`field`,`modelid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of cms_model_field
-- ----------------------------
INSERT INTO `cms_model_field` VALUES ('59', '6', 'catid', '栏目', '', '1', '6', '/^[0-9]{1,6}$/', '请选择栏目', 'select', 0x7B2264656661756C7476616C7565223A22227D, '1', '0', '1', '0', '1', '1', '1', '0', '2020-05-26 10:57:59', '');
INSERT INTO `cms_model_field` VALUES ('60', '6', 'title', '文章标题', 0xE8AFB7E8BE93E585A5E69687E7ABA0E6A087E9A298, '1', '80', '', '请输入文章标题', 'title', '', '1', '0', '1', '0', '1', '1', '2', '0', '2020-05-25 17:27:34', '');
INSERT INTO `cms_model_field` VALUES ('61', '6', 'keywords', '文章关键字', 0xE8AFB7E8BE93E585A5E69687E7ABA0E585B3E994AEE5AD97, '0', '150', '', '请输入文章关键字', 'keywords', 0x7B2273697A65223A22313030222C2264656661756C7476616C7565223A22227D, '1', '0', '1', '0', '1', '0', '3', '0', '2020-05-25 17:27:36', '');
INSERT INTO `cms_model_field` VALUES ('62', '6', 'tags', '文章标签', 0xE8AFB7E8BE93E585A5E69687E7ABA0E6A087E7ADBE, '0', '150', '', '请输入文章标签', 'tags', 0x7B2273697A65223A22313030222C2264656661756C7476616C7565223A22227D, '1', '0', '1', '0', '1', '0', '4', '0', '2020-05-25 17:27:37', '');
INSERT INTO `cms_model_field` VALUES ('63', '6', 'description', '文章摘要', 0xE8AFB7E8BE93E585A5E69687E7ABA0E69198E8A681, '0', '255', '', '请输入文章摘要', 'title', 0x7B227769647468223A223938222C22686569676874223A223436222C2264656661756C7476616C7565223A22222C22656E61626C6568746D6C223A2230227D, '1', '0', '1', '0', '1', '0', '5', '0', '2020-05-25 17:27:39', '');
INSERT INTO `cms_model_field` VALUES ('64', '6', 'conent', '内容', 0xE8AFB7E8BE93E585A5E69687E7ABA0E58685E5AEB9, '1', '999999', '', '文章内容不能为空', 'editor', 0x7B22746F6F6C626172223A2266756C6C222C2264656661756C7476616C7565223A22222C22656E61626C656B65796C696E6B223A2231222C227265706C6163656E756D223A2232222C226C696E6B5F6D6F6465223A2230222C22656E61626C6573617665696D616765223A2231227D, '1', '0', '1', '0', '1', '1', '6', '0', '2020-05-25 17:27:40', '');
INSERT INTO `cms_model_field` VALUES ('65', '6', 'pages', '文章分页方式', 0x3C64697620636C6173733D22636F6E74656E745F61747472223E3C6C6162656C3E3C696E707574206E616D653D226164645F696E74726F647563652220747970653D22636865636B626F7822202076616C75653D22312220636865636B65643EE698AFE590A6E688AAE58F96E58685E5AEB93C2F6C6162656C3E3C696E70757420747970653D227465787422206E616D653D22696E74726F637564655F6C656E677468222076616C75653D22323030222073697A653D2233223EE5AD97E7ACA6E887B3E58685E5AEB9E69198E8A6810D0A3C6C6162656C3E3C696E70757420747970653D22636865636B626F7822206E616D653D226175746F5F7468756D62222076616C75653D22312220636865636B65643EE698AFE590A6E88EB7E58F96E58685E5AEB9E7ACAC3C2F6C6162656C3E3C696E70757420747970653D227465787422206E616D653D226175746F5F7468756D625F6E6F222076616C75653D2231222073697A653D22322220636C6173733D22223EE5BCA0E59BBEE78987E4BD9CE4B8BAE6A087E9A298E59BBEE789870D0A3C2F6469763E, '0', '80', '', '', 'pages', '', '1', '0', '0', '0', '1', '0', '7', '0', '2020-05-25 17:27:41', '');
INSERT INTO `cms_model_field` VALUES ('66', '6', 'posids', '推荐位', '', '0', '80', '', '', 'posid', '', '1', '0', '0', '0', '1', '0', '8', '0', '2020-05-25 17:27:43', '');
INSERT INTO `cms_model_field` VALUES ('67', '6', 'thumb', '文章缩略图', 0xE8AFB7E4B88AE4BCA0E69687E7ABA0E7BCA9E795A5E59BBE, '1', '100', '', '请上传文章缩略图', 'image', 0x7B2273697A65223A223530222C2264656661756C7476616C7565223A22222C2273686F775F74797065223A2231222C2275706C6F61645F6D617873697A65223A2231303234222C2275706C6F61645F616C6C6F77657874223A226A70677C6A7065677C6769667C706E677C626D70222C2277617465726D61726B223A2230222C22697373656C656374696D616765223A2231222C22696D616765735F7769647468223A22222C22696D616765735F686569676874223A22227D, '1', '0', '1', '0', '0', '0', '9', '0', '2020-05-25 17:27:44', '');
INSERT INTO `cms_model_field` VALUES ('68', '6', 'views', '点击量', '', '0', '10', '', '', 'islink', '', '0', '0', '1', '0', '1', '0', '10', '0', '2020-05-25 17:27:46', '');
INSERT INTO `cms_model_field` VALUES ('69', '6', 'copyfrom', '文章来源', '', '0', '100', '', '', 'copyfrom', 0x7B2264656661756C7476616C7565223A22227D, '0', '0', '0', '0', '1', '0', '11', '0', '2020-05-25 17:31:09', '');
INSERT INTO `cms_model_field` VALUES ('70', '6', 'username', '文章发布者', '', '0', '20', '', '', 'text', '', '1', '0', '1', '0', '1', '0', '12', '0', '2020-05-25 17:27:49', '');
INSERT INTO `cms_model_field` VALUES ('71', '6', 'author', '文章作者', 0xE9A1B5E99DA2E5B195E7A4BAE79A84E69687E7ABA0E4BD9CE88085, '0', '20', '', '', 'text', '', '1', '0', '1', '0', '1', '0', '13', '0', '2020-05-25 17:27:50', '');
INSERT INTO `cms_model_field` VALUES ('72', '6', 'islink', '转向链接', '', '0', '0', '', '', 'islink', '', '0', '0', '1', '0', '1', '0', '14', '0', '2020-05-25 17:27:52', '');
INSERT INTO `cms_model_field` VALUES ('73', '6', 'status', '文章状态', '', '0', '10', '', '', 'box', '', '0', '0', '1', '0', '1', '0', '15', '0', '2020-05-25 17:27:54', '');
INSERT INTO `cms_model_field` VALUES ('74', '6', 'sort', '文章排序', '', '0', '6', '', '', 'number', '', '1', '0', '1', '0', '1', '0', '16', '0', '2020-05-25 17:27:56', '');
INSERT INTO `cms_model_field` VALUES ('75', '6', 'is_deleted', '是否标记为删除', '', '0', '1', '', '', 'radio', '', '1', '0', '1', '0', '1', '0', '17', '0', '2020-05-25 17:27:59', '');
INSERT INTO `cms_model_field` VALUES ('76', '6', 'relation', '相关文章', '', '0', '0', '', '', 'omnipotent', '', '0', '0', '1', '0', '1', '0', '18', '0', '2020-05-25 17:28:01', '');
INSERT INTO `cms_model_field` VALUES ('77', '6', 'create_at', '文章创建时间', '', '0', '10', '', '', 'datetime', '', '1', '0', '1', '0', '1', '0', '19', '0', '2020-05-25 17:28:04', '');
INSERT INTO `cms_model_field` VALUES ('78', '6', 'update_at', '文章更新时间', '', '0', '10', '', '', 'datetime', '', '1', '0', '1', '0', '1', '0', '20', '0', '2020-05-25 17:28:06', '');
INSERT INTO `cms_model_field` VALUES ('79', '7', 'catid', '栏目', '', '1', '6', '/^[0-9]{1,6}$/', '请选择栏目', 'select', 0x7B2264656661756C7476616C7565223A22227D, '0', '1', '1', '0', '1', '1', '1', '0', '2020-05-26 11:31:18', '');
INSERT INTO `cms_model_field` VALUES ('80', '7', 'title', '文章标题', 0xE8AFB7E8BE93E585A5E69687E7ABA0E6A087E9A298, '1', '80', '', '请输入文章标题', 'title', '', '0', '0', '1', '0', '1', '1', '2', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('81', '7', 'keywords', '文章关键字', 0xE8AFB7E8BE93E585A5E69687E7ABA0E585B3E994AEE5AD97, '0', '150', '', '请输入文章关键字', 'keywords', 0x7B2273697A65223A22313030222C2264656661756C7476616C7565223A22227D, '0', '0', '1', '0', '1', '0', '3', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('82', '7', 'tags', '文章标签', 0xE8AFB7E8BE93E585A5E69687E7ABA0E6A087E7ADBE, '0', '150', '', '请输入文章标签', 'tags', 0x7B2273697A65223A22313030222C2264656661756C7476616C7565223A22227D, '0', '0', '1', '0', '1', '0', '4', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('83', '7', 'description', '文章摘要', 0xE8AFB7E8BE93E585A5E69687E7ABA0E69198E8A681, '0', '255', '', '请输入文章摘要', 'title', 0x7B227769647468223A223938222C22686569676874223A223436222C2264656661756C7476616C7565223A22222C22656E61626C6568746D6C223A2230227D, '0', '0', '1', '0', '1', '0', '5', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('84', '7', 'conent', '内容', 0xE8AFB7E8BE93E585A5E69687E7ABA0E58685E5AEB9, '1', '999999', '', '文章内容不能为空', 'editor', 0x7B22746F6F6C626172223A2266756C6C222C2264656661756C7476616C7565223A22222C22656E61626C656B65796C696E6B223A2231222C227265706C6163656E756D223A2232222C226C696E6B5F6D6F6465223A2230222C22656E61626C6573617665696D616765223A2231227D, '0', '0', '1', '0', '1', '1', '6', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('85', '7', 'pages', '文章分页方式', 0x3C64697620636C6173733D22636F6E74656E745F61747472223E3C6C6162656C3E3C696E707574206E616D653D226164645F696E74726F647563652220747970653D22636865636B626F7822202076616C75653D22312220636865636B65643EE698AFE590A6E688AAE58F96E58685E5AEB93C2F6C6162656C3E3C696E70757420747970653D227465787422206E616D653D22696E74726F637564655F6C656E677468222076616C75653D22323030222073697A653D2233223EE5AD97E7ACA6E887B3E58685E5AEB9E69198E8A6810D0A3C6C6162656C3E3C696E70757420747970653D22636865636B626F7822206E616D653D226175746F5F7468756D62222076616C75653D22312220636865636B65643EE698AFE590A6E88EB7E58F96E58685E5AEB9E7ACAC3C2F6C6162656C3E3C696E70757420747970653D227465787422206E616D653D226175746F5F7468756D625F6E6F222076616C75653D2231222073697A653D22322220636C6173733D22223EE5BCA0E59BBEE78987E4BD9CE4B8BAE6A087E9A298E59BBEE789870D0A3C2F6469763E, '0', '80', '', '', 'pages', '', '0', '0', '0', '0', '1', '0', '7', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('86', '7', 'posids', '推荐位', '', '0', '80', '', '', 'posid', '', '0', '0', '0', '0', '1', '0', '8', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('87', '7', 'thumb', '文章缩略图', 0xE8AFB7E4B88AE4BCA0E69687E7ABA0E7BCA9E795A5E59BBE, '1', '100', '', '请上传文章缩略图', 'image', 0x7B2273697A65223A223530222C2264656661756C7476616C7565223A22222C2273686F775F74797065223A2231222C2275706C6F61645F6D617873697A65223A2231303234222C2275706C6F61645F616C6C6F77657874223A226A70677C6A7065677C6769667C706E677C626D70222C2277617465726D61726B223A2230222C22697373656C656374696D616765223A2231222C22696D616765735F7769647468223A22222C22696D616765735F686569676874223A22227D, '0', '0', '1', '0', '0', '0', '9', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('88', '7', 'views', '点击量', '', '0', '10', '', '', 'islink', '', '1', '0', '1', '0', '1', '0', '10', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('89', '7', 'copyfrom', '文章来源', '', '0', '100', '', '', 'copyfrom', 0x7B2264656661756C7476616C7565223A22227D, '0', '0', '0', '0', '1', '0', '11', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('90', '7', 'username', '文章发布者', '', '0', '20', '', '', 'text', '', '0', '0', '1', '0', '1', '0', '12', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('91', '7', 'author', '文章作者', 0xE9A1B5E99DA2E5B195E7A4BAE79A84E69687E7ABA0E4BD9CE88085, '0', '20', '', '', 'text', '', '0', '0', '1', '0', '1', '0', '13', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('92', '7', 'islink', '转向链接', '', '0', '0', '', '', 'islink', '', '1', '0', '1', '0', '1', '0', '14', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('93', '7', 'status', '文章状态', '', '0', '10', '', '', 'box', '', '1', '0', '1', '0', '1', '0', '15', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('94', '7', 'sort', '文章排序', '', '0', '6', '', '', 'number', '', '0', '0', '1', '0', '1', '0', '16', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('95', '7', 'is_deleted', '是否标记为删除', '', '0', '1', '', '', 'radio', '', '0', '0', '1', '0', '1', '0', '17', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('96', '7', 'relation', '相关文章', '', '0', '0', '', '', 'omnipotent', '', '1', '0', '1', '0', '1', '0', '18', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('97', '7', 'create_at', '文章创建时间', '', '0', '10', '', '', 'datetime', '', '0', '0', '1', '0', '1', '0', '19', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('98', '7', 'update_at', '文章更新时间', '', '0', '10', '', '', 'datetime', '', '0', '0', '1', '0', '1', '0', '20', '0', '2020-05-25 17:17:41', '');
INSERT INTO `cms_model_field` VALUES ('111', '6', 'news_title', '新闻标题', '', '0', '0', '', '', 'text', 0x7B227769647468223A22222C2264656661756C7476616C7565223A22222C226368617274797065223A2263686172222C226C656E677468223A22313530227D, '1', '0', '1', '0', '1', '0', '0', '0', '2020-05-27 17:56:39', null);
INSERT INTO `cms_model_field` VALUES ('121', '6', 'new_catid', '栏目ID', 0xE8AFB7E98089E68BA9E6A08FE79BAE4944, '0', '0', '/^[0-9-]+$/', '请选择合法的栏目', 'select', 0x7B227769647468223A22313030222C226C656E677468223A223130222C22637373223A226C617975692D696E707574222C226F7074696F6E735F66726F6D223A226D6F64756C6564617461222C226F7074696F6E735F6D6F64756C65223A22636174676F7279227D, '1', '0', '1', '0', '1', '0', '0', '0', '2020-05-28 17:53:28', null);

-- ----------------------------
-- Table structure for cms_picture
-- ----------------------------
DROP TABLE IF EXISTS `cms_picture`;
CREATE TABLE `cms_picture` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `title` char(80) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `style` char(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `thumb` char(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `keywords` char(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `description` char(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `tags` char(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `posids` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `islink` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `username` char(20) COLLATE utf8mb4_bin NOT NULL,
  `author` char(20) COLLATE utf8mb4_bin NOT NULL,
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `update_at` int(10) unsigned NOT NULL DEFAULT '0',
  `create_at` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`sort`,`id`),
  KEY `listorder` (`catid`,`status`,`sort`,`id`),
  KEY `catid` (`catid`,`status`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of cms_picture
-- ----------------------------

-- ----------------------------
-- Table structure for cms_picture_data
-- ----------------------------
DROP TABLE IF EXISTS `cms_picture_data`;
CREATE TABLE `cms_picture_data` (
  `id` mediumint(8) NOT NULL,
  `content` text COLLATE utf8mb4_bin NOT NULL COMMENT '内容'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of cms_picture_data
-- ----------------------------

-- ----------------------------
-- Table structure for module_adv
-- ----------------------------
DROP TABLE IF EXISTS `module_adv`;
CREATE TABLE `module_adv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL COMMENT '广告类别',
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '广告标题',
  `thumb` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '广告图',
  `sort` int(10) NOT NULL COMMENT '排序',
  `url` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '广告跳转地址',
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '广告描述',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除(1删除,0未删)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of module_adv
-- ----------------------------
INSERT INTO `module_adv` VALUES ('2', '1', '首页广告图', 'http://www.jhphpcms.com/upload/91/bfc962a697bfc6582339425d10bbb2.jpg', '1', 'http://www.baidu.com/', '广告描述', '2020-05-20 14:52:37', '0');

-- ----------------------------
-- Table structure for module_advcat
-- ----------------------------
DROP TABLE IF EXISTS `module_advcat`;
CREATE TABLE `module_advcat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL COMMENT '广告位标题',
  `type` char(5) COLLATE utf8mb4_bin NOT NULL COMMENT '类型',
  `width` varchar(10) CHARACTER SET utf8mb4 NOT NULL COMMENT '广告位宽度',
  `height` varchar(10) CHARACTER SET utf8mb4 NOT NULL COMMENT '广告位高度',
  `description` varchar(255) CHARACTER SET utf8mb4 NOT NULL COMMENT '广告位描述',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`(191)) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of module_advcat
-- ----------------------------
INSERT INTO `module_advcat` VALUES ('1', '首页banner图', 'img', '1920', '450', '首页banner图', '2020-05-20 15:41:46');
INSERT INTO `module_advcat` VALUES ('2', '内页侧边广告', 'code', '300', '250', '内页侧边广告描述', '2020-05-20 15:41:05');

-- ----------------------------
-- Table structure for module_attachment
-- ----------------------------
DROP TABLE IF EXISTS `module_attachment`;
CREATE TABLE `module_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module` char(15) COLLATE utf8mb4_bin NOT NULL,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `filename` char(50) COLLATE utf8mb4_bin NOT NULL,
  `filepath` char(200) COLLATE utf8mb4_bin NOT NULL,
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `fileext` char(10) COLLATE utf8mb4_bin NOT NULL,
  `downloads` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `uploadtime` int(10) unsigned NOT NULL DEFAULT '0',
  `uploadip` char(15) COLLATE utf8mb4_bin NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态(0正常,-1不正常)',
  `filemd5` char(32) COLLATE utf8mb4_bin NOT NULL,
  `siteid` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `filemd5` (`filemd5`) USING BTREE,
  KEY `fileext` (`fileext`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of module_attachment
-- ----------------------------
INSERT INTO `module_attachment` VALUES ('2', 'poster', '0', 'test.mp3', '2019/0618/20190618020208675.mp3', '526398', 'mp3', '0', '1', '1560837727', '127.0.0.1', '-1', '14f4e0b0bb37f04380d21f07cdacc1e0', '1');
INSERT INTO `module_attachment` VALUES ('3', 'content', '6', 'mongodb.xls', 'upload/91/mongodb.xls', '90972', 'xls', '24', '1', '1560838700', '127.0.0.1', '1', 'bee599f75d5b93c9135b3dacbfa89eeb', '1');
INSERT INTO `module_attachment` VALUES ('4', 'content', '7', 'news2.zip', '2019/0618/20190618021933580.zip', '49132', 'zip', '0', '1', '1560838772', '127.0.0.1', '-1', '7cc253199593bdd0f730ab439caa3a67', '1');
INSERT INTO `module_attachment` VALUES ('5', 'content', '8', 'news3.flv', '2019/0618/20190618022137273.flv', '62530', 'flv', '0', '1', '1560838896', '127.0.0.1', '-1', '8e11bb102a69d15ea1bca8777d42bc51', '1');
INSERT INTO `module_attachment` VALUES ('6', 'content', '9', 'a4c768cb4af5b29f4082854063bf5c.jpg', 'upload/88/a4c768cb4af5b29f4082854063bf5c.jpg', '610647', 'jpg', '2', '1', '1560839005', '127.0.0.1', '1', 'c31e5b23dac2cf27f4a09d36d1b86fa7', '1');
INSERT INTO `module_attachment` VALUES ('7', 'content', '10', '999.xlsx', '2019/0618/20190618022543583.jpg', '30726', 'xlsx', '0', '1', '1560839142', '127.0.0.1', '-1', 'b2aa724ff0bb5575ec18a79d6017b7f3', '1');

-- ----------------------------
-- Table structure for module_diy_config
-- ----------------------------
DROP TABLE IF EXISTS `module_diy_config`;
CREATE TABLE `module_diy_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `value` varchar(500) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `value_type` varchar(10) CHARACTER SET utf8mb4 DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '描述',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is_deleted',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of module_diy_config
-- ----------------------------
INSERT INTO `module_diy_config` VALUES ('1', 'BUY_VIP_YEAR_MONEY', '2000', 'single', '网吧购买VIP包年所需金额(单位元)', '0');
INSERT INTO `module_diy_config` VALUES ('2', 'SPEED_C_PLUG_DEFAULT', 'onebuy_bus:0\r\nlol_bus:1\r\nsynthesize:0\r\nspeed_game:1\r\nqad_bus:1\r\nlolAD_bus:0\r\ndevil_bus:1', 'many', '加速器C端插件版默认开启版本', '0');
INSERT INTO `module_diy_config` VALUES ('3', 'TESHU_CHANNEL_PAYBACK', '10', 'single', '特殊渠道分成返现(单位元)', '0');
INSERT INTO `module_diy_config` VALUES ('4', 'QUDAO_CHANNEL_PAYBACK', '0:55\r\n50:65\r\n100:70', 'many', '渠道商分成返现比例(一行一个级别[推广网吧当前活跃数],达到一个级别超出部分用新的比例[百分比数字]计算,级别和比例用“:”分隔)', '1');
INSERT INTO `module_diy_config` VALUES ('5', 'BROWSER_HOME_URL_DEFAULT', 'baidu2zzz', 'single', '浏览器业务之首页默认值（如：baidu）', '0');
INSERT INTO `module_diy_config` VALUES ('6', 'DOCK_SERVICE', 'dock_alliance:1\r\ndock_qad:1\r\ndock_desktop:1\r\ndock_browser:1\r\ndock_speed:1\r\ndock_speed_desktop:1\r\ndock_speed_attach:1', 'many', 'Dock业务之服务开关', '0');
INSERT INTO `module_diy_config` VALUES ('7', 'LH_BROWSER_HOME_URL_DEFAULT', 'sougoudaohang', 'single', '领航浏览器业务之首页默认值（如：baidu）', '0');
INSERT INTO `module_diy_config` VALUES ('8', 'QUDAO_CHANNEL_PAYBACK', '0:55\r\n50:65\r\n100:70', 'many', '渠道商分成返现比例(一行一个级别[推广网吧当前活跃数],达到一个级别超出部分用新的比例[百分比数字]计算,级别和比例用“:”分隔)', '1');
INSERT INTO `module_diy_config` VALUES ('10', 'TAX', '6', 'single', '税点，6%', '0');

-- ----------------------------
-- Table structure for module_link
-- ----------------------------
DROP TABLE IF EXISTS `module_link`;
CREATE TABLE `module_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_bin NOT NULL COMMENT '链接标题',
  `url` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '链接URL',
  `thumb` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '链接图片',
  `webmaster` varchar(30) COLLATE utf8mb4_bin NOT NULL COMMENT '站长名称',
  `tel` char(11) COLLATE utf8mb4_bin NOT NULL COMMENT '站长电话',
  `email` varchar(100) COLLATE utf8mb4_bin NOT NULL COMMENT '站长邮箱',
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '描述',
  `sort` int(10) NOT NULL COMMENT '排序',
  `is_deleted` tinyint(1) NOT NULL COMMENT '是否删除(1删除,0未删)',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `title` (`name`) USING BTREE,
  KEY `url` (`url`(191)) USING BTREE,
  KEY `is_deleted` (`is_deleted`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Records of module_link
-- ----------------------------
INSERT INTO `module_link` VALUES ('1', '百度2', 'https://www.baidu.com.cn/', '', '张三2', '15124526524', 'faq@jhphpcms.com', '站点描述new', '1', '0', '2020-05-20 11:53:04');
INSERT INTO `module_link` VALUES ('2', '新浪', 'https://www.sina.com.cn/', 'http://www.jhphpcms.com/upload/88/a4c768cb4af5b29f4082854063bf5c.jpg', '李四', '15142542542', 'link@sina.com.cn', '新浪站点描述', '2', '0', '2020-05-20 12:40:09');

-- ----------------------------
-- Table structure for phinxlog
-- ----------------------------
DROP TABLE IF EXISTS `phinxlog`;
CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of phinxlog
-- ----------------------------
INSERT INTO `phinxlog` VALUES ('20200526095151', 'MyNewMigration', '2020-05-26 17:58:14', '2020-05-26 17:58:14', '0');

-- ----------------------------
-- Table structure for system_auth
-- ----------------------------
DROP TABLE IF EXISTS `system_auth`;
CREATE TABLE `system_auth` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT '' COMMENT '权限名称',
  `desc` varchar(500) DEFAULT '' COMMENT '备注说明',
  `sort` bigint(20) unsigned DEFAULT '0' COMMENT '排序权重',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '权限状态(1使用,0禁用)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_auth_title` (`title`) USING BTREE,
  KEY `idx_system_auth_status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='系统-权限';

-- ----------------------------
-- Records of system_auth
-- ----------------------------
INSERT INTO `system_auth` VALUES ('1', '编辑', '网站编辑', '0', '1', '2020-05-20 11:57:18');

-- ----------------------------
-- Table structure for system_auth_node
-- ----------------------------
DROP TABLE IF EXISTS `system_auth_node`;
CREATE TABLE `system_auth_node` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `auth` bigint(20) unsigned DEFAULT '0' COMMENT '角色',
  `node` varchar(200) DEFAULT '' COMMENT '节点',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_auth_auth` (`auth`) USING BTREE,
  KEY `idx_system_auth_node` (`node`(191)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统-授权';

-- ----------------------------
-- Records of system_auth_node
-- ----------------------------

-- ----------------------------
-- Table structure for system_config
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config` (
  `type` varchar(20) DEFAULT '' COMMENT '分类',
  `name` varchar(100) DEFAULT '' COMMENT '配置名',
  `value` varchar(500) DEFAULT '' COMMENT '配置值',
  KEY `idx_system_config_type` (`type`) USING BTREE,
  KEY `idx_system_config_name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统-配置';

-- ----------------------------
-- Records of system_config
-- ----------------------------
INSERT INTO `system_config` VALUES ('base', 'site_name', 'ThinkAdmin');
INSERT INTO `system_config` VALUES ('base', 'site_icon', 'https://v6.thinkadmin.top/upload/f47b8fe06e38ae99/08e8398da45583b9.png');
INSERT INTO `system_config` VALUES ('base', 'site_copy', '©版权所有 2019-2020 楚才科技');
INSERT INTO `system_config` VALUES ('base', 'app_name', 'ThinkAdmin');
INSERT INTO `system_config` VALUES ('base', 'app_version', 'v6.0');
INSERT INTO `system_config` VALUES ('base', 'miitbeian', '粤ICP备16006642号-2');
INSERT INTO `system_config` VALUES ('storage', 'qiniu_http_protocol', 'http');
INSERT INTO `system_config` VALUES ('storage', 'type', 'local');
INSERT INTO `system_config` VALUES ('storage', 'allow_exts', 'doc,gif,icon,jpg,mp3,mp4,p12,pem,png,rar,xls,xlsx');
INSERT INTO `system_config` VALUES ('storage', 'qiniu_region', '华东');
INSERT INTO `system_config` VALUES ('storage', 'qiniu_bucket', '');
INSERT INTO `system_config` VALUES ('storage', 'qiniu_http_domain', '');
INSERT INTO `system_config` VALUES ('storage', 'qiniu_access_key', '');
INSERT INTO `system_config` VALUES ('storage', 'qiniu_secret_key', '');
INSERT INTO `system_config` VALUES ('wechat', 'type', 'api');
INSERT INTO `system_config` VALUES ('wechat', 'token', '');
INSERT INTO `system_config` VALUES ('wechat', 'appid', '');
INSERT INTO `system_config` VALUES ('wechat', 'appsecret', '');
INSERT INTO `system_config` VALUES ('wechat', 'encodingaeskey', '');
INSERT INTO `system_config` VALUES ('wechat', 'thr_appid', '');
INSERT INTO `system_config` VALUES ('wechat', 'thr_appkey', '');
INSERT INTO `system_config` VALUES ('wechat', 'mch_id', '');
INSERT INTO `system_config` VALUES ('wechat', 'mch_key', '');
INSERT INTO `system_config` VALUES ('wechat', 'mch_ssl_type', 'pem');
INSERT INTO `system_config` VALUES ('wechat', 'mch_ssl_p12', '');
INSERT INTO `system_config` VALUES ('wechat', 'mch_ssl_key', '');
INSERT INTO `system_config` VALUES ('wechat', 'mch_ssl_cer', '');
INSERT INTO `system_config` VALUES ('storage', 'alioss_http_protocol', 'http');
INSERT INTO `system_config` VALUES ('storage', 'alioss_point', 'oss-cn-hangzhou.aliyuncs.com');
INSERT INTO `system_config` VALUES ('storage', 'alioss_bucket', '');
INSERT INTO `system_config` VALUES ('storage', 'alioss_http_domain', '');
INSERT INTO `system_config` VALUES ('storage', 'alioss_access_key', '');
INSERT INTO `system_config` VALUES ('storage', 'alioss_secret_key', '');
INSERT INTO `system_config` VALUES ('storage', 'link_type', 'none');
INSERT INTO `system_config` VALUES ('storage', 'local_http_protocol', 'http');

-- ----------------------------
-- Table structure for system_data
-- ----------------------------
DROP TABLE IF EXISTS `system_data`;
CREATE TABLE `system_data` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT '' COMMENT '配置名',
  `value` longtext COMMENT '配置值',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_data_name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统-数据';

-- ----------------------------
-- Records of system_data
-- ----------------------------

-- ----------------------------
-- Table structure for system_menu
-- ----------------------------
DROP TABLE IF EXISTS `system_menu`;
CREATE TABLE `system_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned DEFAULT '0' COMMENT '上级ID',
  `title` varchar(100) DEFAULT '' COMMENT '菜单名称',
  `icon` varchar(100) DEFAULT '' COMMENT '菜单图标',
  `node` varchar(100) DEFAULT '' COMMENT '节点代码',
  `url` varchar(400) DEFAULT '' COMMENT '链接节点',
  `params` varchar(500) DEFAULT '' COMMENT '链接参数',
  `target` varchar(20) DEFAULT '_self' COMMENT '打开方式',
  `sort` int(11) unsigned DEFAULT '0' COMMENT '排序权重',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(0:禁用,1:启用)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_menu_node` (`node`) USING BTREE,
  KEY `idx_system_menu_status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COMMENT='系统-菜单';

-- ----------------------------
-- Records of system_menu
-- ----------------------------
INSERT INTO `system_menu` VALUES ('2', '0', '系统管理', '', '', '#', '', '_self', '100', '1', '2018-09-05 18:04:52');
INSERT INTO `system_menu` VALUES ('3', '4', '系统菜单管理', 'layui-icon layui-icon-layouts', '', 'admin/menu/index', '', '_self', '1', '1', '2018-09-05 18:05:26');
INSERT INTO `system_menu` VALUES ('4', '2', '系统配置', '', '', '#', '', '_self', '20', '1', '2018-09-05 18:07:17');
INSERT INTO `system_menu` VALUES ('5', '12', '系统用户管理', 'layui-icon layui-icon-username', '', 'admin/user/index', '', '_self', '1', '1', '2018-09-06 11:10:42');
INSERT INTO `system_menu` VALUES ('7', '12', '访问权限管理', 'layui-icon layui-icon-vercode', '', 'admin/auth/index', '', '_self', '2', '1', '2018-09-06 15:17:14');
INSERT INTO `system_menu` VALUES ('11', '4', '系统参数配置', 'layui-icon layui-icon-set', '', 'admin/config/index', '', '_self', '4', '1', '2018-09-06 16:43:47');
INSERT INTO `system_menu` VALUES ('12', '2', '权限管理', '', '', '#', '', '_self', '10', '1', '2018-09-06 18:01:31');
INSERT INTO `system_menu` VALUES ('27', '4', '系统任务管理', 'layui-icon layui-icon-log', '', 'admin/queue/index', '', '_self', '3', '1', '2018-11-29 11:13:34');
INSERT INTO `system_menu` VALUES ('49', '4', '系统日志管理', 'layui-icon layui-icon-form', '', 'admin/oplog/index', '', '_self', '2', '1', '2019-02-18 12:56:56');
INSERT INTO `system_menu` VALUES ('56', '0', '微信管理', '', '', '#', '', '_self', '200', '1', '2019-12-09 11:00:37');
INSERT INTO `system_menu` VALUES ('57', '56', '微信管理', '', '', '#', '', '_self', '0', '1', '2019-12-09 13:56:58');
INSERT INTO `system_menu` VALUES ('58', '57', '微信接口配置', 'layui-icon layui-icon-set', '', 'wechat/config/options', '', '_self', '0', '1', '2019-12-09 13:57:28');
INSERT INTO `system_menu` VALUES ('59', '57', '微信支付配置', 'layui-icon layui-icon-rmb', '', 'wechat/config/payment', '', '_self', '0', '1', '2019-12-09 13:58:42');
INSERT INTO `system_menu` VALUES ('60', '56', '微信定制', '', '', '#', '', '_self', '0', '1', '2019-12-09 18:35:16');
INSERT INTO `system_menu` VALUES ('61', '60', '微信粉丝管理', 'layui-icon layui-icon-username', '', 'wechat/fans/index', '', '_self', '0', '1', '2019-12-09 18:35:37');
INSERT INTO `system_menu` VALUES ('62', '60', '微信图文管理', 'layui-icon layui-icon-template-1', '', 'wechat/news/index', '', '_self', '0', '1', '2019-12-09 18:43:51');
INSERT INTO `system_menu` VALUES ('63', '60', '微信菜单配置', 'layui-icon layui-icon-cellphone', '', 'wechat/menu/index', '', '_self', '0', '1', '2019-12-09 22:49:28');
INSERT INTO `system_menu` VALUES ('64', '60', '回复规则管理', 'layui-icon layui-icon-engine', '', 'wechat/keys/index', '', '_self', '0', '1', '2019-12-14 14:09:04');
INSERT INTO `system_menu` VALUES ('65', '60', '关注回复配置', 'layui-icon layui-icon-senior', '', 'wechat/keys/subscribe', '', '_self', '0', '1', '2019-12-14 14:10:31');
INSERT INTO `system_menu` VALUES ('66', '60', '默认回复配置', 'layui-icon layui-icon-util', '', 'wechat/keys/defaults', '', '_self', '0', '1', '2019-12-14 14:11:18');
INSERT INTO `system_menu` VALUES ('67', '0', '模块管理', 'layui-icon layui-icon-read', '', '#', '', '_self', '0', '1', '2020-05-19 09:46:21');
INSERT INTO `system_menu` VALUES ('68', '67', '友情链接', 'layui-icon layui-icon-link', '', 'module/link/index', '', '_self', '0', '1', '2020-05-19 09:46:45');
INSERT INTO `system_menu` VALUES ('69', '0', 'CMS管理', '', '', '#', '', '_self', '0', '1', '2020-05-20 09:06:57');
INSERT INTO `system_menu` VALUES ('70', '69', '自定义表单管理', 'layui-icon layui-icon-tabs', '', 'cms/diyform/index', '', '_self', '0', '1', '2020-05-20 09:08:24');
INSERT INTO `system_menu` VALUES ('71', '69', '内容管理', 'layui-icon layui-icon-file', '', 'cms/index/index', '', '_self', '0', '1', '2020-05-20 09:09:14');
INSERT INTO `system_menu` VALUES ('72', '69', '栏目管理', 'layui-icon layui-icon-template-1', '', 'cms/category/index', '', '_self', '0', '1', '2020-05-20 09:09:46');
INSERT INTO `system_menu` VALUES ('73', '69', '模型管理', '', '', '#', '', '_self', '0', '1', '2020-05-20 09:11:07');
INSERT INTO `system_menu` VALUES ('74', '69', '标签管理', 'layui-icon layui-icon-rate', '', 'cms/tag/index', '', '_self', '0', '1', '2020-05-20 09:11:32');
INSERT INTO `system_menu` VALUES ('75', '69', '碎片管理', 'layui-icon layui-icon-app', '', 'cms/block/index', '', '_self', '0', '1', '2020-05-20 09:12:38');
INSERT INTO `system_menu` VALUES ('76', '69', '单页管理', 'layui-icon layui-icon-form', '', 'cms/page/index', '', '_self', '0', '1', '2020-05-20 09:13:16');
INSERT INTO `system_menu` VALUES ('77', '69', '评论管理', 'layui-icon layui-icon-align-center', '', 'cms/comment/index', '', '_self', '0', '1', '2020-05-20 09:15:41');
INSERT INTO `system_menu` VALUES ('78', '69', '推荐位管理', 'layui-icon layui-icon-praise', '', 'cms/recommend/index', '', '_self', '0', '1', '2020-05-20 09:21:41');
INSERT INTO `system_menu` VALUES ('79', '67', '广告管理', '', '', '#', '', '_self', '0', '1', '2020-05-20 09:28:40');
INSERT INTO `system_menu` VALUES ('80', '67', '通用配置管理', 'layui-icon layui-icon-set', '', 'module/config/index', '', '_self', '0', '1', '2020-05-20 09:29:12');
INSERT INTO `system_menu` VALUES ('81', '67', '采集管理', 'layui-icon layui-icon-screen-restore', '', 'module/collect/index', '', '_self', '0', '1', '2020-05-20 09:31:55');
INSERT INTO `system_menu` VALUES ('82', '67', '计划任务管理', 'fa fa-calendar', '', 'module/crontab/index', '', '_self', '0', '1', '2020-05-20 09:32:45');
INSERT INTO `system_menu` VALUES ('83', '67', '木马扫描', 'fa fa-stethoscope', '', 'module/trojan/index', '', '_self', '0', '1', '2020-05-20 09:35:42');
INSERT INTO `system_menu` VALUES ('84', '67', '附件管理', 'fa fa-truck', '', 'module/attachment/index', '', '_self', '0', '1', '2020-05-20 09:37:47');
INSERT INTO `system_menu` VALUES ('85', '0', '扩展管理', '', '', '#', '', '_self', '0', '1', '2020-05-20 09:38:29');
INSERT INTO `system_menu` VALUES ('86', '85', '数据库工具', 'fa fa-database', '', 'extension/database/index', '', '_self', '0', '1', '2020-05-20 10:26:38');
INSERT INTO `system_menu` VALUES ('87', '85', '敏感词管理', 'layui-icon layui-icon-survey', '', 'extension/sensitiveword/index', '', '_self', '0', '1', '2020-05-20 10:28:03');
INSERT INTO `system_menu` VALUES ('88', '85', '更新全站缓存', 'layui-icon layui-icon-delete', '', 'extension/cacheall/index', '', '_self', '0', '1', '2020-05-20 10:30:26');
INSERT INTO `system_menu` VALUES ('89', '85', '联动菜单管理', 'fa fa-venus-double', '', 'extension/linkagemenu/index', '', '_self', '0', '1', '2020-05-20 10:33:04');
INSERT INTO `system_menu` VALUES ('90', '67', '投票管理', 'fa fa-hand-grab-o', '', 'module/vote/index', '', '_self', '0', '1', '2020-05-20 10:39:54');
INSERT INTO `system_menu` VALUES ('92', '79', '广告位管理', 'fa fa-audio-description', '', 'module/advcat/index', '', '_self', '0', '1', '2020-05-20 12:05:01');
INSERT INTO `system_menu` VALUES ('93', '79', '广告管理', 'layui-icon layui-icon-picture', '', 'module/adv/index', '', '_self', '0', '1', '2020-05-20 12:05:19');
INSERT INTO `system_menu` VALUES ('94', '85', '流量统计', 'layui-icon layui-icon-chart', '', 'extension/flow/index', '', '_self', '0', '1', '2020-05-20 13:58:44');
INSERT INTO `system_menu` VALUES ('96', '73', '模型列表', 'layui-icon layui-icon-component', '', 'cms/model/index', '', '_self', '0', '1', '2020-05-26 10:28:02');
INSERT INTO `system_menu` VALUES ('97', '73', '基础模型配置', '', '', 'cms/base_model/index', '', '_self', '0', '1', '2020-05-26 10:28:53');

-- ----------------------------
-- Table structure for system_oplog
-- ----------------------------
DROP TABLE IF EXISTS `system_oplog`;
CREATE TABLE `system_oplog` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `node` varchar(200) NOT NULL DEFAULT '' COMMENT '当前操作节点',
  `geoip` varchar(15) NOT NULL DEFAULT '' COMMENT '操作者IP地址',
  `action` varchar(200) NOT NULL DEFAULT '' COMMENT '操作行为名称',
  `content` varchar(1024) NOT NULL DEFAULT '' COMMENT '操作内容描述',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人用户名',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COMMENT='系统-日志';

-- ----------------------------
-- Records of system_oplog
-- ----------------------------
INSERT INTO `system_oplog` VALUES ('1', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-18 17:44:54');
INSERT INTO `system_oplog` VALUES ('2', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-19 08:52:02');
INSERT INTO `system_oplog` VALUES ('3', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-19 09:31:04');
INSERT INTO `system_oplog` VALUES ('4', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-19 13:01:21');
INSERT INTO `system_oplog` VALUES ('5', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-20 09:03:54');
INSERT INTO `system_oplog` VALUES ('6', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-20 10:25:48');
INSERT INTO `system_oplog` VALUES ('7', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-20 11:15:45');
INSERT INTO `system_oplog` VALUES ('8', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-20 12:39:58');
INSERT INTO `system_oplog` VALUES ('9', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-20 13:28:44');
INSERT INTO `system_oplog` VALUES ('10', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-20 17:48:17');
INSERT INTO `system_oplog` VALUES ('11', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-21 10:15:38');
INSERT INTO `system_oplog` VALUES ('12', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-21 10:54:46');
INSERT INTO `system_oplog` VALUES ('13', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-21 16:20:27');
INSERT INTO `system_oplog` VALUES ('14', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-22 08:53:34');
INSERT INTO `system_oplog` VALUES ('15', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-22 13:47:48');
INSERT INTO `system_oplog` VALUES ('16', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-22 17:34:04');
INSERT INTO `system_oplog` VALUES ('17', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-25 10:07:26');
INSERT INTO `system_oplog` VALUES ('18', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-25 11:25:12');
INSERT INTO `system_oplog` VALUES ('19', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-25 12:54:18');
INSERT INTO `system_oplog` VALUES ('20', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-25 13:56:24');
INSERT INTO `system_oplog` VALUES ('21', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-25 14:28:39');
INSERT INTO `system_oplog` VALUES ('22', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-25 17:13:01');
INSERT INTO `system_oplog` VALUES ('23', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-26 09:33:36');
INSERT INTO `system_oplog` VALUES ('24', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-26 13:00:27');
INSERT INTO `system_oplog` VALUES ('25', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-26 14:03:36');
INSERT INTO `system_oplog` VALUES ('26', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-26 17:38:17');
INSERT INTO `system_oplog` VALUES ('27', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-27 10:50:32');
INSERT INTO `system_oplog` VALUES ('28', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-27 16:09:47');
INSERT INTO `system_oplog` VALUES ('29', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-28 08:35:56');
INSERT INTO `system_oplog` VALUES ('30', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-28 13:02:07');
INSERT INTO `system_oplog` VALUES ('31', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-28 14:03:55');
INSERT INTO `system_oplog` VALUES ('32', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-29 09:18:00');
INSERT INTO `system_oplog` VALUES ('33', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-29 10:45:08');
INSERT INTO `system_oplog` VALUES ('34', 'admin/login/index', '127.0.0.1', '用户登录', '登录系统后台成功', 'admin', '2020-05-29 13:55:17');

-- ----------------------------
-- Table structure for system_queue
-- ----------------------------
DROP TABLE IF EXISTS `system_queue`;
CREATE TABLE `system_queue` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '任务编号',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '任务名称',
  `command` varchar(500) DEFAULT '' COMMENT '执行指令',
  `exec_pid` bigint(20) DEFAULT '0' COMMENT '执行进程',
  `exec_data` longtext COMMENT '执行参数',
  `exec_time` bigint(20) DEFAULT '0' COMMENT '执行时间',
  `exec_desc` varchar(500) DEFAULT '' COMMENT '执行描述',
  `enter_time` decimal(20,4) DEFAULT '0.0000' COMMENT '开始时间',
  `outer_time` decimal(20,4) DEFAULT '0.0000' COMMENT '结束时间',
  `loops_time` bigint(20) DEFAULT '0' COMMENT '循环时间',
  `attempts` bigint(20) DEFAULT '0' COMMENT '执行次数',
  `rscript` tinyint(1) DEFAULT '1' COMMENT '任务类型(0单例,1多例)',
  `status` tinyint(1) DEFAULT '1' COMMENT '任务状态(1新任务,2处理中,3成功,4失败)',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_queue_code` (`code`) USING BTREE,
  KEY `idx_system_queue_title` (`title`) USING BTREE,
  KEY `idx_system_queue_status` (`status`) USING BTREE,
  KEY `idx_system_queue_rscript` (`rscript`) USING BTREE,
  KEY `idx_system_queue_create_at` (`create_at`) USING BTREE,
  KEY `idx_system_queue_exec_time` (`exec_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统-任务';

-- ----------------------------
-- Records of system_queue
-- ----------------------------

-- ----------------------------
-- Table structure for system_user
-- ----------------------------
DROP TABLE IF EXISTS `system_user`;
CREATE TABLE `system_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT '' COMMENT '用户账号',
  `password` varchar(32) DEFAULT '' COMMENT '用户密码',
  `nickname` varchar(50) DEFAULT '' COMMENT '用户昵称',
  `headimg` varchar(255) DEFAULT '' COMMENT '头像地址',
  `authorize` varchar(255) DEFAULT '' COMMENT '权限授权',
  `contact_qq` varchar(20) DEFAULT '' COMMENT '联系QQ',
  `contact_mail` varchar(20) DEFAULT '' COMMENT '联系邮箱',
  `contact_phone` varchar(20) DEFAULT '' COMMENT '联系手机',
  `login_ip` varchar(255) DEFAULT '' COMMENT '登录地址',
  `login_at` varchar(20) DEFAULT '' COMMENT '登录时间',
  `login_num` bigint(20) DEFAULT '0' COMMENT '登录次数',
  `describe` varchar(255) DEFAULT '' COMMENT '备注说明',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(0禁用,1启用)',
  `sort` bigint(20) DEFAULT '0' COMMENT '排序权重',
  `is_deleted` tinyint(1) DEFAULT '0' COMMENT '删除(1删除,0未删)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_system_user_username` (`username`) USING BTREE,
  KEY `idx_system_user_deleted` (`is_deleted`) USING BTREE,
  KEY `idx_system_user_status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10002 DEFAULT CHARSET=utf8mb4 COMMENT='系统-用户';

-- ----------------------------
-- Records of system_user
-- ----------------------------
INSERT INTO `system_user` VALUES ('10000', 'admin', '21232f297a57a5a743894a0e4a801fc3', '系统管理员', '', '', '', '', '', '127.0.0.1', '2020-05-29 13:55:17', '1091', '', '1', '0', '0', '2015-11-13 15:14:22');
INSERT INTO `system_user` VALUES ('10001', 'test', '', '测试账户', '', '', '', '', '', '', '', '0', '', '1', '0', '0', '2020-05-20 11:56:49');

-- ----------------------------
-- Table structure for wechat_fans
-- ----------------------------
DROP TABLE IF EXISTS `wechat_fans`;
CREATE TABLE `wechat_fans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appid` varchar(50) DEFAULT '' COMMENT '公众号APPID',
  `unionid` varchar(100) DEFAULT '' COMMENT '粉丝unionid',
  `openid` varchar(100) DEFAULT '' COMMENT '粉丝openid',
  `tagid_list` varchar(100) DEFAULT '' COMMENT '粉丝标签id',
  `is_black` tinyint(1) unsigned DEFAULT '0' COMMENT '是否为黑名单状态',
  `subscribe` tinyint(1) unsigned DEFAULT '0' COMMENT '关注状态(0未关注,1已关注)',
  `nickname` varchar(200) DEFAULT '' COMMENT '用户昵称',
  `sex` tinyint(1) unsigned DEFAULT '0' COMMENT '用户性别(1男性,2女性,0未知)',
  `country` varchar(50) DEFAULT '' COMMENT '用户所在国家',
  `province` varchar(50) DEFAULT '' COMMENT '用户所在省份',
  `city` varchar(50) DEFAULT '' COMMENT '用户所在城市',
  `language` varchar(50) DEFAULT '' COMMENT '用户的语言(zh_CN)',
  `headimgurl` varchar(500) DEFAULT '' COMMENT '用户头像',
  `subscribe_time` bigint(20) unsigned DEFAULT '0' COMMENT '关注时间',
  `subscribe_at` datetime DEFAULT NULL COMMENT '关注时间',
  `remark` varchar(50) DEFAULT '' COMMENT '备注',
  `subscribe_scene` varchar(200) DEFAULT '' COMMENT '扫码关注场景',
  `qr_scene` varchar(100) DEFAULT '' COMMENT '二维码场景值',
  `qr_scene_str` varchar(200) DEFAULT '' COMMENT '二维码场景内容',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `index_wechat_fans_openid` (`openid`) USING BTREE,
  KEY `index_wechat_fans_unionid` (`unionid`) USING BTREE,
  KEY `index_wechat_fans_is_back` (`is_black`) USING BTREE,
  KEY `index_wechat_fans_subscribe` (`subscribe`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信-粉丝';

-- ----------------------------
-- Records of wechat_fans
-- ----------------------------

-- ----------------------------
-- Table structure for wechat_fans_tags
-- ----------------------------
DROP TABLE IF EXISTS `wechat_fans_tags`;
CREATE TABLE `wechat_fans_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `appid` varchar(50) DEFAULT '' COMMENT '公众号APPID',
  `name` varchar(35) DEFAULT NULL COMMENT '标签名称',
  `count` bigint(20) unsigned DEFAULT '0' COMMENT '总数',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建日期',
  KEY `index_wechat_fans_tags_id` (`id`) USING BTREE,
  KEY `index_wechat_fans_tags_appid` (`appid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信-标签';

-- ----------------------------
-- Records of wechat_fans_tags
-- ----------------------------

-- ----------------------------
-- Table structure for wechat_keys
-- ----------------------------
DROP TABLE IF EXISTS `wechat_keys`;
CREATE TABLE `wechat_keys` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `appid` char(100) DEFAULT '' COMMENT '公众号APPID',
  `type` varchar(20) DEFAULT '' COMMENT '类型(text,image,news)',
  `keys` varchar(100) DEFAULT NULL COMMENT '关键字',
  `content` text COMMENT '文本内容',
  `image_url` varchar(255) DEFAULT '' COMMENT '图片链接',
  `voice_url` varchar(255) DEFAULT '' COMMENT '语音链接',
  `music_title` varchar(100) DEFAULT '' COMMENT '音乐标题',
  `music_url` varchar(255) DEFAULT '' COMMENT '音乐链接',
  `music_image` varchar(255) DEFAULT '' COMMENT '缩略图片',
  `music_desc` varchar(255) DEFAULT '' COMMENT '音乐描述',
  `video_title` varchar(100) DEFAULT '' COMMENT '视频标题',
  `video_url` varchar(255) DEFAULT '' COMMENT '视频URL',
  `video_desc` varchar(255) DEFAULT '' COMMENT '视频描述',
  `news_id` bigint(20) unsigned DEFAULT NULL COMMENT '图文ID',
  `sort` bigint(20) unsigned DEFAULT '0' COMMENT '排序字段',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(0禁用,1启用)',
  `create_by` bigint(20) unsigned DEFAULT '0' COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `index_wechat_keys_appid` (`appid`) USING BTREE,
  KEY `index_wechat_keys_type` (`type`) USING BTREE,
  KEY `index_wechat_keys_keys` (`keys`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='微信-关键字';

-- ----------------------------
-- Records of wechat_keys
-- ----------------------------
INSERT INTO `wechat_keys` VALUES ('1', '', 'text', 'hello', '说点什么吧', 'http://www.jhphpcms.com/static/theme/img/image.png', '', '音乐标题', '', 'http://www.jhphpcms.com/static/theme/img/image.png', '音乐描述', '视频标题', '', '视频描述', '0', '0', '1', '0', '2020-05-26 10:02:39');
INSERT INTO `wechat_keys` VALUES ('3', '', 'text', '你好', '说点什么吧', 'http://www.jhphpcms.com/static/theme/img/image.png', '', '音乐标题', '', 'http://www.jhphpcms.com/static/theme/img/image.png', '音乐描述', '视频标题', '', '视频描述', '0', '0', '1', '0', '2020-05-29 16:32:30');

-- ----------------------------
-- Table structure for wechat_media
-- ----------------------------
DROP TABLE IF EXISTS `wechat_media`;
CREATE TABLE `wechat_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appid` varchar(100) DEFAULT '' COMMENT '公众号ID',
  `md5` varchar(32) DEFAULT '' COMMENT '文件md5',
  `type` varchar(20) DEFAULT '' COMMENT '媒体类型',
  `media_id` varchar(100) DEFAULT '' COMMENT '永久素材MediaID',
  `local_url` varchar(300) DEFAULT '' COMMENT '本地文件链接',
  `media_url` varchar(300) DEFAULT '' COMMENT '远程图片链接',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `index_wechat_media_appid` (`appid`) USING BTREE,
  KEY `index_wechat_media_md5` (`md5`) USING BTREE,
  KEY `index_wechat_media_type` (`type`) USING BTREE,
  KEY `index_wechat_media_media_id` (`media_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信-素材';

-- ----------------------------
-- Records of wechat_media
-- ----------------------------

-- ----------------------------
-- Table structure for wechat_news
-- ----------------------------
DROP TABLE IF EXISTS `wechat_news`;
CREATE TABLE `wechat_news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `media_id` varchar(100) DEFAULT '' COMMENT '永久素材MediaID',
  `local_url` varchar(300) DEFAULT '' COMMENT '永久素材外网URL',
  `article_id` varchar(60) DEFAULT '' COMMENT '关联图文ID(用英文逗号做分割)',
  `is_deleted` tinyint(1) unsigned DEFAULT '0' COMMENT '删除状态(0未删除,1已删除)',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `create_by` bigint(20) DEFAULT NULL COMMENT '创建人',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `index_wechat_news_artcle_id` (`article_id`) USING BTREE,
  KEY `index_wechat_news_media_id` (`media_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信-图文';

-- ----------------------------
-- Records of wechat_news
-- ----------------------------

-- ----------------------------
-- Table structure for wechat_news_article
-- ----------------------------
DROP TABLE IF EXISTS `wechat_news_article`;
CREATE TABLE `wechat_news_article` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT '' COMMENT '素材标题',
  `local_url` varchar(300) DEFAULT '' COMMENT '永久素材显示URL',
  `show_cover_pic` tinyint(4) unsigned DEFAULT '0' COMMENT '显示封面(0不显示,1显示)',
  `author` varchar(20) DEFAULT '' COMMENT '文章作者',
  `digest` varchar(300) DEFAULT '' COMMENT '摘要内容',
  `content` longtext COMMENT '图文内容',
  `content_source_url` varchar(200) DEFAULT '' COMMENT '原文地址',
  `read_num` bigint(20) unsigned DEFAULT '0' COMMENT '阅读数量',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信-文章';

-- ----------------------------
-- Records of wechat_news_article
-- ----------------------------
