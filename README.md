# php-enjoy-tpl-engine
PHP Enjoy Template Engine,  MySQL数据库模板引擎

# 介绍
一款迅速查询插入更新删除的MySQL模板引擎，主要针对开发者数据处理的时候使用，可以迅速编写数据处理脚本


# 入门
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
```
```php
    use SqlTplEngine\Core\SPDO;
    function getPdo(){
        $dbms = 'mysql';     //数据库类型
        $host = '127.0.0.1'; //数据库主机名
        $dbName = 'sql_tpl_engine';    //使用的数据库名称
        $user = 'root';      //数据库连接用户名
        $pass = '';          //对应的密码
        $dsn = "$dbms:host=$host;dbname=$dbName;port=3308;charset=utf8mb4";
        $pdo = SPDO::build($dsn, $user, $pass);
        $pdo->setLoadTplBasePath(dirname(__FILE__).'/tplSql');
        return $pdo;
    }
    $pdo = getPdo();
    //insert插入
    $insertCountRes = $pdo->insert($pdo->loadTplParse('tplFileName.order.insert'), [
        'product_id' => 1,
        'state' => 1
    ]);
   
    //查询单个
    $info = $pdo->getOne($pdo->loadTplParse('tplFileName.order.view'), [
        'id' => 1
    ]);
   
    //列表查询
    $list = $pdo->getAll($pdo->loadTplParse('tplFileName.order.list'), [
        'product_id' => 1
    ]);
   
    //更新
    $changeCountRes = $pdo->update($pdo->loadTplParse('tplFileName.order.update'), [
        'id' => 1,
        'update_state' => 1
    ]);   

    //删除
    $deleteCountRes = $pdo->delete($pdo->loadTplParse('tplFileName.order.delete'), [
        'product_id' => 1
    ]);   

```