<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/21
 * Time: 9:57
 */
$arr1 = array("a"=>"PHP","b"=>"java","python");

$arr2 = array("c" =>"ruby","d" => "c++","go","a"=> "swift");

$arr3 = array_merge($arr2,$arr1);

$arr4 = $arr1 + $arr2;

print_r($arr3);
print_r($arr4);die;