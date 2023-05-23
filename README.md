# php-enjoy-tpl-engine
PHP Enjoy Template Engine,  MySQL数据库模板引擎

# 介绍
一款迅速查询插入更新删除的MySQL模板引擎，主要针对开发者数据处理的时候使用，可以迅速编写数据处理脚本


# 入门
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
   //未写完，后面补充
   

    

   


```