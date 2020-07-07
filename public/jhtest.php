<?php

$url = '/blog-968.html';
$url2 = 'https://www.liqingbo.cn/news/blog-968.html';
$urlData = parse_url($url);
$urlData2 = parse_url($url2);
if(!isset($urlData['host']) || $urlData['host'] == 'www.liqingbo.cn'){
    echo '过滤相对地址链接';
}
if(!isset($urlData2['host']) || $urlData2['host'] == 'www.liqingbo.cn'){
    echo '过滤指定的URL地址';
}
print_r(pathinfo($url));