<?php
/**
 * SqlTplEngine  file.
 * @author 林玉山 <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/php-enjoy-sql-engine
 * @copyright Copyright &copy; 2019-2100
 * @license https://github.com/1107012776/php-enjoy-sql-engine/blob/main/LICENSE
 */

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


class DemoTest extends TestCase
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

    public function testStart()
    {
        $pdo = $this->getPdo();
        //insert插入
        $insertCountRes = $pdo->insert($pdo->loadTplParse('tplFileName.order.insert'), [
            'product_id' => 1,
            'state' => 2
        ]);
        $this->assertEquals(!empty($insertCountRes) && $insertCountRes == 1, true);

        $insertId = $pdo->lastInsertId();
        //查询单个
        $info = $pdo->getOne($pdo->loadTplParse('tplFileName.order.view'), [
            'id' => $insertId
        ]);
        $this->assertEquals(!empty($info), true);

        //列表查询
        $list = $pdo->getAll($pdo->loadTplParse('tplFileName.order.list'), [
            'product_id' => 1
        ]);

        $this->assertEquals(!empty($list) && count($list) == 1, true);

        //更新
        $changeCountRes = $pdo->update($pdo->loadTplParse('tplFileName.order.update'), [
            'id' => $insertId,
            'update_state' => 1
        ]);

        $this->assertEquals(!empty($changeCountRes) && $changeCountRes == 1, true);

        //删除
        $deleteCountRes = $pdo->delete($pdo->loadTplParse('tplFileName.order.delete'), [
            'product_id' => 1
        ]);

        $this->assertEquals(!empty($deleteCountRes) && $deleteCountRes == 1, true);
        //用户表操作
        //insert插入 第一条数据
        $insertCountRes = $pdo->insert($pdo->loadTplParse('tplFileName.user.insert'), [
            'username' => 'lys',
            'nickname' => 'SqlTplEngine作者'
        ]);
        $this->assertEquals(!empty($insertCountRes) && $insertCountRes == 1, true);
        //列表查询
        $list = $pdo->getAll($pdo->loadTplParse('tplFileName.user.list'), [
            'username' => 'lys'
        ]);
        $this->assertEquals(!empty($list) && count($list) == 1, true);
        //insert插入 第二条数据
        $insertCountRes = $pdo->insert($pdo->loadTplParse('tplFileName.user.insert'), [
            'username' => 'lys-1',
            'nickname' => 'SqlTplEngine作者'
        ]);
        $this->assertEquals(!empty($insertCountRes) && $insertCountRes == 1, true);
        //列表查询（不安全，通过占位替换赋值变量给模板）
        $list = $pdo->getAll($pdo->loadTplParse('tplFileName.user.where_list', [
            'where' => 'username = \'lys\''
        ]));
        $this->assertEquals(!empty($list) && count($list) == 1, true);
        //删除
        $deleteCountRes = $pdo->delete($pdo->loadTplParse('tplFileName.user.delete'), [
            'username' => 'lys'
        ]);
        $this->assertEquals(!empty($deleteCountRes) && $deleteCountRes == 1, true);
        //删除
        $deleteCountRes = $pdo->delete($pdo->loadTplParse('tplFileName.user.delete'), [
            'username' => 'lys-1'
        ]);
        $this->assertEquals(!empty($deleteCountRes) && $deleteCountRes == 1, true);
    }


    /**
     * 事务测试
     */
    public function testTrans()
    {
        $pdo = $this->getPdo();
        $pdo->startTrans();
        //insert插入
        $insertCountRes = $pdo->insert($pdo->loadTplParse('tplFileName.order.insert'), [
            'product_id' => 1,
            'state' => 2
        ]);
        $this->assertEquals(!empty($insertCountRes) && $insertCountRes == 1, true);
        $insertId = $pdo->lastInsertId();
        $pdo->rollBack();
        //查询单个
        $info = $pdo->getOne($pdo->loadTplParse('tplFileName.order.view'), [
            'id' => $insertId
        ]);
        $this->assertEquals(empty($info), true);
        $pdo->startTrans();
        $pdo->startTrans();
        //insert插入
        $insertCountRes = $pdo->insert($pdo->loadTplParse('tplFileName.order.insert'), [
            'product_id' => 1,
            'state' => 2
        ]);
        $this->assertEquals(!empty($insertCountRes) && $insertCountRes == 1, true);
        $insertId = $pdo->lastInsertId();
        $pdo->rollBack();
        $pdo->rollBack();
        //查询单个
        $info = $pdo->getOne($pdo->loadTplParse('tplFileName.order.view'), [
            'id' => $insertId
        ]);
        $this->assertEquals(empty($info), true);
        $pdo->startTrans();
        $pdo->startTrans();
        //insert插入
        $insertCountRes = $pdo->insert($pdo->loadTplParse('tplFileName.order.insert'), [
            'product_id' => 1,
            'state' => 2
        ]);
        $this->assertEquals(!empty($insertCountRes) && $insertCountRes == 1, true);
        $insertId = $pdo->lastInsertId();
        $pdo->commit();
        $pdo->commit();
        //查询单个
        $info = $pdo->getOne($pdo->loadTplParse('tplFileName.order.view'), [
            'id' => $insertId
        ]);
        $this->assertEquals(!empty($info), true);
        //删除
        $deleteCountRes = $pdo->delete($pdo->loadTplParse('tplFileName.order.delete'), [
            'product_id' => 1
        ]);
        $this->assertEquals(!empty($deleteCountRes) && $deleteCountRes == 1, true);

    }

}


