<?php
declare(strict_types=1);
/**
 * Date: 2020/8/13
 * Time: 15:39
 */

namespace app\index\service;

use app\model\CmsArticle;
use think\admin\Service;
use think\facade\Db;

class ArticleService extends Service
{

    /**
     * 根据文章ID来查询具体的文章内容
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAricleById($id)
    {
        $results = [];
        if(!empty($id) && $id >0){
            $article = CmsArticle::findOrEmpty($id);
            if(!$article->isEmpty()){
                $results = $article->toArray();
            }
        }
        return $results;
    }

    /**
     * 更新文章阅读量
     * @param $id
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function updateArticleByid($id)
    {
        return Db::table('cms_article')->where('id', $id)->inc('views')->update();
    }


}