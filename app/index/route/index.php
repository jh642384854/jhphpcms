<?php

use think\facade\Route;

Route::pattern([
    'name' => '\w+',
    'id'   => '\d+',
    'page' => '\d+',
]);

//后台登录入口
//Route::get('admin','@admin/login')->ext('html');

//站点首页
Route::get('/','Index/index')->ext('html');

//在线留言
Route::get('message/index$','Message/index')->ext('html')->name('message');
Route::rule('message/add$','Message/add','GET|POST')->ext('html')->name('message_add');

//单页
Route::get('page/<name>$','Content/page')->ext('html')->name('page');

//标签页面
Route::get('tag/<id>$','Content/tag')->ext('html')->name('tag');

//栏目列表页
Route::get('list/<name>/<page?>','Content/index')->ext('html')->name('index');
//内容页
Route::get('show/<name>/<id>$','Content/show')->ext('html')->name('show');

//定义全局MISS路由
Route::miss('index/miss');