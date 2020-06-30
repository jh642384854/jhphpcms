<?php
namespace app\index\controller;

class Content extends Base
{
    /**
     * 列表页
     */
    public function index()
    {
        $this->fetch('list');
    }

    /**
     * 内容页
     */
    public function show()
    {
        $this->fetch();
    }

    /**
     * 单页
     */
    public function page()
    {
        $this->fetch();
    }

    /**
     * 标签页
     */
    public function tag()
    {
        $this->fetch();
    }
}