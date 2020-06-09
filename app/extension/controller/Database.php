<?php

namespace app\extension\controller;

use database\BackupRecover;
use database\TableOpt;
use think\admin\Controller;

/**
 * 数据库管理
 * Class Database
 * @package app\extension\controller
 */
class Database extends Controller
{
    protected $backupPath;
    protected $dbmenu;
    protected $tableOpt;

    protected function initialize()
    {
        $this->tableOpt = new Tableopt();
        $this->backupPath = trim($this->app->getRootPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR;
        $this->dbmenu = [
            'backup' => ['name' => '数据备份', 'url' => url('index', ['op' => 'backup'])],
            'recover' => ['name' => '数据恢复', 'url' => url('index', ['op' => 'recover'])],
        ];
    }

    /**
     * 数据库管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->op = $this->request->get('op', 'backup', 'trim');
        if ($this->op == 'backup') {
            $this->list = $this->tableOpt->getAllTableInfo();
        } else {
            $this->list = $this->getAllBackupFiles();
        }
        $this->fetch();
    }

    /**
     * 获取所有备份的文件列表
     * @return array
     */
    private function getAllBackupFiles()
    {
        $flag = \FilesystemIterator::KEY_AS_FILENAME;
        $glob = new \FilesystemIterator($this->backupPath, $flag);

        $dataList = [];

        foreach ($glob as $name => $file) {
            if (preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)) {
                $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
                $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                $part = $name[6];
                if (isset($dataList["{$date} {$time}"])) {
                    $info = $dataList["{$date} {$time}"];
                    $info['part'] = max($info['part'], $part);
                    $info['size'] = $info['size'] + $file->getSize();
                } else {
                    $info['part'] = $part;
                    $info['size'] = $file->getSize();
                }
                $info['time'] = "{$date} {$time}";
                $time = strtotime("{$date} {$time}");
                $extension = strtoupper($file->getExtension());
                $info['compress'] = ($extension === 'SQL') ? '无' : $extension;
                $info['name'] = date('Ymd-His', $time);
                $info['id'] = $time;
                $dataList["{$date} {$time}"] = $info;
            }
        }
        return $dataList;
    }


    /**
     * 备份数据库
     * @auth true
     */
    public function backup()
    {
        if ($this->request->isPost()) {
            $tableNames = $this->request->post('tables');
            if (empty($tableNames)) {
                return $this->error('请选择您要备份的数据表');
            }
            $tables = explode(',', $tableNames);
            //获取数据库备份配置信息
            $config = [
                'path' => $this->backupPath,
                'part' => config('database.backup.part', 20971520),
                'compress' => config('database.backup.compress', 1),
                'level' => config('database.backup.compress_level', 4),
            ];

            //检查当前是否有正在执行的备份任务
            $lock = $config['path'] . 'backup.lock';
            if (is_file($lock)) {
                return $this->error('检测到有一个备份任务正在执行，请稍后再试');
            } else {
                //创建锁文件
                file_put_contents($lock, $this->request->time());
            }
            //生成备份文件信息
            $file = [
                'name' => date('Ymd-His', $this->request->time()),
                'part' => 1
            ];
            //创建备份文件
            $backupRecover = new BackupRecover($file, $config);
            $start = 0;
            if ($backupRecover->create() !== false) {
                // 备份指定表
                foreach ($tables as $table) {
                    $start = $backupRecover->backup($table, $start);
                    while (0 !== $start) {
                        if (false === $start) {
                            return $this->error('备份出错');
                        }
                        $start = $backupRecover->backup($table, $start[0]);
                    }
                }
                // 备份完成，删除锁定文件
                unlink($lock);
                //TODO 记录一下备份信息
            }
            return $this->success('备份完成');
        }
    }

    /**
     * 恢复数据库
     * @auth true
     */
    public function recover()
    {
        $id = trim($this->request->post('id'));
        if (empty($id)) {
            return $this->error('请选择您要恢复的备份文件');
        }
        $name = date('Ymd-His', $id) . '-*.sql*';
        $path = $this->backupPath . $name;
        $files = glob($path);
        $list = array();
        foreach ($files as $name) {
            $basename = basename($name);
            $match = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
            $gz = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
            $list[$match[6]] = array($match[6], $name, $gz);
        }
        ksort($list);
        // 检测文件正确性
        $last = end($list);
        if (count($list) === $last[0]) {
            foreach ($list as $item) {
                $config = [
                    'path' => $this->backupPath,
                    'compress' => $item[2]
                ];
                $backupRecover = new BackupRecover($item, $config);
                $start = $backupRecover->import(0);
                // 导入所有数据
                while (0 !== $start) {
                    if (false === $start) {
                        return $this->error('数据恢复出错');
                    }
                    $start = $backupRecover->import($start[0]);
                }
            }
            return $this->success('数据恢复完成');
        }
        return $this->error('备份文件可能已经损坏，请检查');
    }

    /**
     * 优化数据表
     * @auth true
     */
    public function optimize()
    {
        $tables = $this->request->post('tables');
        if ($this->tableOpt->optimizeTable($tables)) {
            return $this->success('优化成功');
        } else {
            return $this->success('优化失败');
        }
    }

    /**
     * 修复数据表
     * @auth true
     */
    public function repair()
    {
        $tables = $this->request->post('tables');
        if ($this->tableOpt->repairTable($tables)) {
            return $this->success('修复成功');
        } else {
            return $this->success('修复失败');
        }
    }

    /**
     * 删除备份数据库
     * @auth true
     */
    public function delbackup()
    {
        $id = trim($this->request->post('id'));
        if (empty($id)) {
            return $this->error('请选择您要删除的备份文件');
        }
        if (stripos($id, ',') > 0) {
            //批量删除操作
            foreach (explode(',', $id) as $val) {
                $this->delFile($val);
            }
        } else {
            $this->delFile($id);
        }
        return $this->success('备份文件删除成功');
    }

    private function delFile($id)
    {
        $name = date('Ymd-His', $id) . '-*.sql*';
        $path = $this->backupPath . $name;
        array_map("unlink", glob($path));
        if (count(glob($path)) && glob($path)) {
            return $this->error('备份文件删除失败，请检查权限');
        }
        return true;
    }
}