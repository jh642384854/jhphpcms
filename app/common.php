<?php

use think\facade\View;

/**
 * 错误信息反馈共页面
 * @param $msg
 * @return string
 */
//TODO 这个方法暂时没有用
function errorHandler($msg)
{
    return View::fetch(app()->getBasePath() . 'admin/view/error/error.html');
}

/**
 * 渲染字段设置的额外配置信息
 * @param $type 什么字段类型
 * @param string $default 默认的值
 * @return string
 */
function rendFieldOptions($type, $default = '')
{
    $data = config('constant.modelFieldType')[$type]['field_list'];
    if ($default == '') {
        $default = $data[0];
    }
    if (count($data) > 2) {
        $str = '<select name="setting[chartype]" class="layui-select" lay-filter="fieldtype_filter"  lay-search required>';
    } else {
        $str = '';
    }
    //如果模型字段可选的只要一个数据库字段可选，就直接返回一个隐藏域
    if (count($data) == 1) {
        $str .= '<input type="hidden" name="setting[chartype]" value="' . $data[0] . '" />';
    } else {
        foreach ($data as $val) {
            //如果模型字段可选的数据字段超过2种选择，使用select元素来渲染，如果等于2种选择，则使用radio来进行渲染。
            if (count($data) > 2) {
                if ($default == $val) {
                    $str .= '<option selected value="' . $val . '">' . $val . '</option>';
                } else {
                    $str .= '<option value="' . $val . '">' . $val . '</option>';
                }
            } else if (count($data) == 2) {
                if ($default == $val) {
                    $str .= '<input type="radio" name="setting[chartype]" checked value="' . $val . '" title="' . $val . '">';
                } else {
                    $str .= '<input type="radio" name="setting[chartype]" value="' . $val . '" title="' . $val . '">';
                }
            }
        }
    }
    if (count($data) > 2) {
        $str .= '</select>';
    }
    return $str;
}