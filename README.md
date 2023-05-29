# php-enjoy-sql-engine
PHP Enjoy Sql Engine,  MySQL数据库模板引擎

# 介绍
一款迅速查询插入更新删除的MySQL模板引擎，主要针对开发者数据处理的时候使用，可以迅速编写数据处理脚本。
优点：可以把sql和php代码分离，php代码专注于处理业务逻辑。


# 入门 (具体查看tests目录)
## 创建数据库，创建表
```sql
CREATE TABLE `order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state` tinyint DEFAULT '0',
  `product_id` int unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT '',
  `nickname` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```
## 创建一个模板 tplFileName.sql
```sql
    #namespace("order")
        #sql("insert")
        #remark("注释：插入")
        insert into `order`(`product_id`,`state`)values(:product_id,:state);
        #end
        #sql("list")
        #remark("注释：查询列表")
        select * from `order` where product_id = :product_id;
        #end
        #sql("view")
        #remark("注释：查询单个")
        select * from `order` where id = :id;
        #end
        #sql("update")
        #remark("注释：更新")
        update  `order` set state = :update_state where id = :id;
        #end
        #sql("delete")
        #remark("注释：删除")
        delete from `order` where product_id = :product_id;
        #end
        #end("order")
    #namespace("user")
        #sql("insert")
        #remark("注释：插入")
        insert into `user`(`username`,`nickname`)values(:username,:nickname);
        #end
        #sql("list")
        #remark("注释：查询列表")
        select * from `user` where username = :username;
        #end
    #end("user")
```
## 代码实战
```php
 
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
         //insert插入
         $insertCountRes = $pdo->insert($pdo->loadTplParse('tplFileName.user.insert'), [
             'username' => 'lys',
             'nickname' => 'SqlTplEngine作者'
         ]);
         $this->assertEquals(!empty($insertCountRes) && $insertCountRes == 1, true);
         $list = $pdo->getAll($pdo->loadTplParse('tplFileName.user.list'), [
             'username' => 'lys'
         ]);
         $this->assertEquals(!empty($list) && count($list) >= 1, true);
     }
 
 }
```