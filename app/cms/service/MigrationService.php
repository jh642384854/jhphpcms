<?php
namespace app\cms\service;


use Phinx\Migration\AbstractMigration;

/**
 * https://tsy12321.gitbooks.io/phinx-doc/
 * https://www.php.cn/toutiao-362620.html
 * https://blog.csdn.net/weixin_33695450/article/details/89910057 巨坑
 * Class MigrationService
 * @package app\cms\service
 */
class MigrationService extends AbstractMigration{

    public function getColumns()
    {
        echo '123';
    }
    /**
     *
     */
    public function up()
    {
        // TODO: Implement up() method.
    }

    /**
     *
     */
    public function down()
    {
        // TODO: Implement down() method.
    }
}