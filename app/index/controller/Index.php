<?php

namespace app\index\controller;

/**
 * Class Index
 * @package app\index\controller
 */
class Index extends Base
{
    public function index()
    {
        $this->demo_time = time();
        $this->fetch();
    }

    //定义全局MISS路由
    public function miss()
    {
        $this->title = '不要瞎猜哦';
        $this->fetch();
    }
}