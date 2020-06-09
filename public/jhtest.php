<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/21
 * Time: 9:57
 */
$table = ['cms_article','cms_article_data'];
$tables = implode('`,`', $table);
$sql = sprintf('OPTIMIZE TABLE %s',quoteTableName($tables));
echo "OPTIMIZE TABLE `{$tables}`<br />";
echo $sql;



function quoteTableName($tableName)
{
    return str_replace('.', '`.`', quoteColumnName($tableName));
}

/**
 * 为columnName添加`符号进行引用
 * @param $columnName 字段名
 * @return mixed
 */
function quoteColumnName($columnName)
{
    return '`' . str_replace('`', '``', $columnName) . '`';
}