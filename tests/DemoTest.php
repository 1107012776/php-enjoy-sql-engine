<?php

namespace SqlTplEngine\Test;

$file_load_path = __DIR__ . '/../../../autoload.php';
if (file_exists($file_load_path)) {
    require_once $file_load_path;
} else {
    $vendor = __DIR__ . '/../vendor/autoload.php';
    require_once $vendor;
}

use PHPUnit\Framework\TestCase;
use SqlTplEngine\Core\SPDO;



class DemoTest  extends TestCase
{
    public function getPdo()
    {
        $dbms = 'mysql';     //数据库类型
        $host = '127.0.0.1'; //数据库主机名
        $dbName = 'sql_tpl_engine';    //使用的数据库名称
        $user = 'root';      //数据库连接用户名
        $pass = '123456';          //对应的密码
        $dsn = "$dbms:host=$host;dbname=$dbName;port=3306;charset=utf8mb4";
        $pdo = SPDO::build($dsn, $user, $pass);
        $pdo->setLoadTplBasePath(dirname(__FILE__) . '/tplSql');
        return $pdo;
    }

    public function startTest()
    {
        $pdo = $this->getPdo();
        //insert插入
        $insertCountRes = $pdo->insert($pdo->loadTplParse('tplFileName.order.insert'), [
            'product_id' => 1,
            'state' => 2
        ]);

        var_dump($insertCountRes);

        //查询单个
        $info = $pdo->getOne($pdo->loadTplParse('tplFileName.order.view'), [
            'id' => $pdo->lastInsertId()
        ]);

        var_dump($info);
        //列表查询
        $list = $pdo->getAll($pdo->loadTplParse('tplFileName.order.list'), [
            'product_id' => 1
        ]);
        var_dump($list);

        //更新
        $changeCountRes = $pdo->update($pdo->loadTplParse('tplFileName.order.update'), [
            'id' => 1,
            'update_state' => 1
        ]);
        var_dump($changeCountRes);

        //删除
        $deleteCountRes = $pdo->delete($pdo->loadTplParse('tplFileName.order.delete'), [
            'product_id' => 1
        ]);

        var_dump($deleteCountRes);
    }

}


