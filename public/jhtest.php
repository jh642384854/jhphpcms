<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/21
 * Time: 9:57
 */
$url = 'https://github.com/PHPOffice/PhpSpreadsheet';
$urlDatas = parse_url($url);
echo getDomain($urlDatas['host']).'<br />';
$url2 = 'https://www.baidu.com/';
$urlDatas2 = parse_url($url2);
echo getDomain($urlDatas2['host']).'<br />';



