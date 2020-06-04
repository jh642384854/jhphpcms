<?php

namespace app\cms\service;


use think\admin\Service;
use think\facade\Log;

/**
 * 数据表结构操作服务类
 * Class TableStructService
 * @package app\cms\service
 */
class TableStructService extends Service
{
    //创建表
    public function createTable()
    {

    }

    //删除表
    public function deleteTable($tableName)
    {
        if ($this->checkTabeExits($tableName)) {
            $sql = sprintf('DROP TABLE IF EXISTS %s', $tableName);
            return $this->app->db->execute($sql);
        } else {
            return false;
        }
    }

    /**
     * 重命名表
     * @param $oldTableName
     * @param $newTableName
     * @return bool|int
     */
    public function renameTable($oldTableName, $newTableName)
    {
        if ($this->checkTabeExits($oldTableName)) {
            $sql = sprintf('RENAME TABLE %s TO %s;', $oldTableName, $newTableName);
            return $this->app->db->execute($sql);
        } else {
            return false;
        }
    }

    /**
     * 检测表是否存在
     * @param $tableName
     * @param string $dataBase
     * @return bool
     */
    public function checkTabeExits($tableName, $dataBase = '')
    {
        $allTables = $this->getAllTables($dataBase);
        $flag = false;
        if (in_array($tableName, $allTables)) {
            $flag = true;
        }
        return $flag;
    }

    /**
     * 获取指定数据库的所有数据表
     * @param $database
     * @return array
     */
    public function getAllTables($database = '')
    {
        if ($database == "") {
            $database = $this->app->db->getConnection()->getConfig('database');
        }
        $sql = sprintf('SHOW TABLES FROM %s', $database);
        $rows = $this->app->db->query($sql);
        $tables = [];
        foreach ($rows as $row) {
            $tables[] = array_values($row)[0];
        }
        return $tables;
    }

    /**
     * 清空表数据
     * @param $tableName
     */
    public function truncateTable($tableName)
    {
        if ($this->checkTabeExits($tableName)) {
            $sql = sprintf('TRUNCATE TABLE %s', $this->quoteTableName($tableName));
            return $this->app->db->execute($sql);
        } else {
            return false;
        }
    }

    /**
     * 添加字段
     * @param $tableName
     * @param $fieldInfo
     * @return int
     */
    public function addField($tableName, $fieldInfo)
    {
        $flag = false;
        if (!$this->checkFieldExits($tableName, $fieldInfo['name'])) {
            $fieldStr = $this->getFieldSqlDefinition($fieldInfo);
            $sql = sprintf('ALTER TABLE %s ADD COLUMN %s;', $this->quoteTableName($tableName), $fieldStr);
            $this->app->db->execute($sql);
            $flag = true;
        }
        return $flag;
    }

    /**
     * 修改字段属性
     * @param $tableName
     * @param $fieldInfo
     * @return mixed
     */
    public function modifyField($tableName, $fieldInfo)
    {
        $flag = false;
        if ($this->checkFieldExits($tableName, $fieldInfo['name'])) {
            $fieldStr = $this->getFieldSqlDefinition($fieldInfo, false);
            $sql = sprintf('ALTER TABLE %s MODIFY COLUMN %s;', $this->quoteTableName($tableName), $fieldStr);
            $this->app->db->execute($sql);
            $flag = true;
        }
        return $flag;
    }

    /**
     * 删除字段
     * @param $tableName
     * @param $fieldName
     * @return mixed
     */
    public function deleteField($tableName, $fieldName)
    {
        $flag = false;
        if ($this->checkFieldExits($tableName, $fieldName)) {
            $sql = sprintf('ALTER TABLE %s DROP COLUMN %s;', $this->quoteTableName($tableName), $this->quoteColumnName($fieldName));
            $this->app->db->execute($sql);
            $flag = true;
        }
        return $flag;
    }

    /**
     * 重命名表字段
     * @param $tableName
     * @param $oldFieldName
     * @param $newFieldName
     * @return int
     */
    public function renameField($tableName, $oldFieldName, $fieldInfo)
    {
        $flag = false;
        if ($this->checkFieldExits($tableName, $fieldInfo['name'])) {
            $fieldStr = $this->getFieldSqlDefinition($fieldInfo, false);
            $sql = sprintf('ALTER TABLE %s CHANGE COLUMN %s %s', $this->quoteTableName($tableName), $this->quoteColumnName($oldFieldName), $fieldStr);
            $this->app->db->execute($sql);
            $flag = true;
        }
        return $flag;
    }

    /**
     *处理字段信息
     * @param $fieldInfo 字段的配置信息
     * @param $isnew 是否新创建字段
     * @return string
     */
    private function getFieldSqlDefinition($fieldInfo, $isnew = true)
    {
        if (is_array($fieldInfo)) {
            $newFieldInfo = [];
            $fieldType = strtolower($fieldInfo['type']);
            switch ($fieldType) {
                case 'varchar':
                case 'char':
                    $newFieldInfo['length'] = empty($fieldInfo['length']) ? 100 : $fieldInfo['length'];
                    $newFieldInfo['charset'] = empty($fieldInfo['charset']) ? 'utf8mb4' : $fieldInfo['charset'];
                    $newFieldInfo['collate'] = empty($fieldInfo['collate']) ? 'utf8mb4_bin' : $fieldInfo['collate'];
                    $newFieldInfo['defaultvalue'] = empty($fieldInfo['defaultvalue']) ? '' : $fieldInfo['defaultvalue'];
                    break;
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'integer':
                case 'int':
                case 'bigint':
                case 'bit':
                    if (!isset($fieldInfo['defaultvalue']) || $fieldInfo['defaultvalue'] == '') {
                        $defalutVal = 0;
                    } else {
                        $defalutVal = $fieldInfo['defaultvalue'];
                    }
                    $newFieldInfo['defaultvalue'] = $defalutVal;
                    break;
                case 'real':
                case 'double':
                case 'float':
                case 'decimal':
                case 'numeric':
                    $newFieldInfo['defaultvalue'] = empty($fieldInfo['defaultvalue']) ? 0 : $fieldInfo['defaultvalue'];
                    $newFieldInfo['precision'] = empty($fieldInfo['precision']) ? 0 : $fieldInfo['precision'];
                    break;
                case 'tinytext':
                case 'mediumtext':
                case 'text':
                case 'longtext':
                    $newFieldInfo['charset'] = empty($fieldInfo['charset']) ? 'utf8mb4' : $fieldInfo['charset'];
                    $newFieldInfo['collate'] = empty($fieldInfo['collate']) ? 'utf8mb4_bin' : $fieldInfo['collate'];
                    $newFieldInfo['defaultvalue'] = empty($fieldInfo['defaultvalue']) ? '' : $fieldInfo['defaultvalue'];
                    break;
                case 'timestamp':
                    $newFieldInfo['defaultvalue'] = empty($fieldInfo['defaultvalue']) ? 'CURRENT_TIMESTAMP' : $fieldInfo['defaultvalue'];
                    break;
                case 'datetime':
                    $newFieldInfo['defaultvalue'] = empty($fieldInfo['defaultvalue']) ? '' : $fieldInfo['defaultvalue'];
                    break;
            }

            $newFieldInfo['name'] = $fieldInfo['name'];
            $newFieldInfo['type'] = $fieldInfo['type'];
            $newFieldInfo['isNull'] = empty($fieldInfo['isNull']) ? 'NOT NULL' : $fieldInfo['isNull'];
            $newFieldInfo['length'] = empty($fieldInfo['length']) ? 100 : $fieldInfo['length'];
            $newFieldInfo['commment'] = empty($fieldInfo['commment']) ? '' : $fieldInfo['commment'];
        }
        $sql = $this->quoteColumnName($newFieldInfo['name']) . ' ' . $newFieldInfo['type'];
        if (in_array($newFieldInfo['type'], ['real', 'double', 'float', 'numeric'])) {
            $sql .= '(' . $newFieldInfo['length'] . ',' . $newFieldInfo['precision'] . ')';
        } else {
            $sql .= '(' . $newFieldInfo['length'] . ')';
        }
        //判断是否为新增字段
        if ($isnew) {
            $sql .= ' ';
            if (in_array($newFieldInfo['type'], ['char', 'varchar', 'tinytext', 'mediumtext', 'text', 'longtext'])) {
                $sql .= 'CHARACTER SET ' . $newFieldInfo['charset'] . ' COLLATE ' . $newFieldInfo['collate'] . ' ';
            }
            $sql .= $newFieldInfo['isNull'] . " DEFAULT '" . $newFieldInfo['defaultvalue'] . "'";
            if ($newFieldInfo['commment'] != '') {
                $sql .= " COMMENT '" . $newFieldInfo['commment'] . "'";
            }
        } else {
            //如果是修改字段
            //①、修改了原来的字符集
            if (!empty($fieldInfo['charset']) && !empty($fieldInfo['collate']) && $fieldInfo['charset'] != "" && $fieldInfo['collate'] != "") {
                if (in_array($newFieldInfo['type'], ['char', 'varchar', 'tinytext', 'mediumtext', 'text', 'longtext'])) {
                    $sql .= ' CHARACTER SET ' . $newFieldInfo['charset'] . ' COLLATE ' . $newFieldInfo['collate'] . ' ';
                }
            }
            //②、修改是否允许为NULL
            if (!empty($fieldInfo['isNull']) && $fieldInfo['isNull'] != "") {
                $sql .= $newFieldInfo['isNull'];
            }
            //③、修改了原来的默认值
            if (!empty($fieldInfo['defaultvalue']) && $fieldInfo['defaultvalue'] != "") {
                $sql .= " DEFAULT '" . $newFieldInfo['defaultvalue'] . "'";
            }
            //④、修改了原来的注释
            if (!empty($fieldInfo['commment']) && $fieldInfo['commment'] != "") {
                $sql .= " COMMENT '" . $newFieldInfo['commment'] . "'";
            }
        }
        return $sql;
    }

    /**
     * 获取表的所有字段
     * @param $tableName
     * @return array
     */
    public function getAllFields($tableName)
    {
        $fields = [];
        if ($this->checkTabeExits($tableName)) {
            $rows = $this->app->db->query(sprintf('SHOW COLUMNS FROM %s', $this->quoteTableName($tableName)));
            foreach ($rows as $row) {
                $fields[$row['Field']] = $row;
            }
        }
        return $fields;
    }

    /**
     * 检查字段是否存在
     * @param $tableName
     * @param $fieldName
     * @return bool
     */
    public function checkFieldExits($tableName, $fieldName)
    {
        $flag = false;
        if ($this->checkTabeExits($tableName)) {
            $rows = $this->app->db->query(sprintf('SHOW COLUMNS FROM %s', $this->quoteTableName($tableName)));
            foreach ($rows as $column) {
                if (strcasecmp($column['Field'], $fieldName) === 0) {
                    $flag = true;
                    break;
                }
            }
        }
        return $flag;
    }

    //创建索引
    public function addIndex()
    {

    }

    //删除索引
    public function deleteIndex()
    {

    }

    //重命名索引
    public function renameIndex()
    {

    }

    /**
     * 为tablename添加`符号进行引用
     * @param $tableName 数据表名称
     * @return mixed
     */
    public function quoteTableName($tableName)
    {
        return str_replace('.', '`.`', $this->quoteColumnName($tableName));
    }

    /**
     * 为columnName添加`符号进行引用
     * @param $columnName 字段名
     * @return mixed
     */
    public function quoteColumnName($columnName)
    {
        return '`' . str_replace('`', '``', $columnName) . '`';
    }

}