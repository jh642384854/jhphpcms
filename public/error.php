<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/10
 * Time: 16:54
 */
ob_start();
set_error_handler('errorHandler');

register_shutdown_function("errorCheck");


function errorHandler(){
    echo 2;
}
function errorCheck(){
    $error=error_get_last();
      print_r("<pre>");
      print_r($error);
      print_r("</pre>");
    if ($error['type']==4){
        $error_file = './error.txt';
        //if (file_exists($error_file)) {
            //创建日志
            $log_file=date("Y_m_d_G_i_s").".txt";
            $file_open = fopen($error_file,"w+");
            fclose($file_open);
        //}
    }
}


function test(Array $a){
    echo 1;
}
//强行报错
test(123);
