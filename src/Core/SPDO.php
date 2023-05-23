<?php
namespace SqlTplEngine\Core;

class SPDO extends \PDO
{
    protected $fetch_style = \PDO::FETCH_ASSOC;
    protected $attr_cursor = \PDO::CURSOR_FWDONLY;
    protected $tplBasePath = '';
    protected $error = '';
    protected $_startTransCount = 0; //事务开启统计

    public function startTrans()
    {
        return $this->beginTransaction();
    }

    public function beginTransaction()
    {
        $this->_startTransCount++;
        if ($this->_startTransCount <= 1) {
            return $this->beginTransaction();
        }
        return true;
    }

    public function rollBack()
    {
        $this->_startTransCount--;
        if ($this->_startTransCount <= 0) {
            return parent::rollBack();
        }
        return true;
    }

    public function commit()
    {
        $this->_startTransCount--;
        if ($this->_startTransCount <= 0) {
            return parent::commit();
        }
        return true;
    }

    /**
     * 重置
     */
    public function renew()
    {
        $this->reset();
        return $this;
    }

    protected function reset()
    {
        $this->error = '';
    }


    public function __construct($dsn, $username = null, $passwd = null, $options = null)
    {
        parent::__construct($dsn, $username, $passwd, $options);
    }

    public static function build($dsn, $user, $pass, $option = [], $queryStr = 'set names utf8mb4;')
    {
        $dbh = new SPDO($dsn, $user, $pass, $option);
        $dbh->query($queryStr);
        return $dbh;
    }

    public function setLoadTplBasePath($tplBasePath)
    {
        $this->tplBasePath = rtrim($tplBasePath, '/') . '/';
        return $this->tplBasePath;
    }

    public function loadTplParse($tplFilePath, $bindReplaceArr = [])
    {
        if (empty($this->tplBasePath)) {
            $this->error = 'tplBasePath is not configured';
            return '';
        }
        $arr = explode('.', $tplFilePath);
        $sql = file_get_contents($this->tplBasePath . $arr[0] . '.sql');
        $sql = preg_replace('/#remark\(.*\)/i', '', $sql);
        if (preg_match_all('/#namespace\(\\"(\w+)\\"\)/i', $sql, $matches)) {
            list($allNamespaceArr, $shortNamespaceArr) = $matches;
            $allNamespaceArrCount = count(array_unique($allNamespaceArr));
            $shortNamespaceArrCount = count(array_unique($shortNamespaceArr));
            if ($allNamespaceArrCount != count($allNamespaceArr)
                || $shortNamespaceArrCount != count($shortNamespaceArr)
            ) {  //存在重复
                $this->error = 'sql template namespace entries are duplicated';
                return '';
            }
        }
        if (preg_match_all('/#end\(\\"(\w+)\\"\)/i', $sql, $matches)) {
            list($allEndArr, $shortEndArr) = $matches;
            $allEndArrCount = count(array_unique($allEndArr));
            $shortEndArrCount = count(array_unique($shortEndArr));
            if ($allEndArrCount != count($allEndArr)
                || $shortEndArrCount != count($shortEndArr)
            ) {  //存在重复
                $this->error = 'sql template namespace entries are duplicated';
                return '';
            }
        }
        $data = [];
        foreach ($shortNamespaceArr as $index => $val) {
            $find1 = '#namespace("' . $val . '")';
            $find2 = '#end("' . $val . '")';
            $start = strpos($sql, $find1);
            $end = strpos($sql, $find2);
            $data[$val] = substr($sql, $start + strlen($find1), $end - ($start + strlen($find1)));
        }
        $childData = [];
        foreach ($data as $namespace => $sql) {
            if (preg_match_all('/#sql\(\\"(\w+)\\"\)/i', $sql, $matches)) {
                list($allSqlArr, $shortSqlArr) = $matches;
                foreach ($shortSqlArr as $index => $value) {
                    $find1 = '#sql("' . $value . '")';
                    $find2 = '#end';
                    $start = strpos($sql, $find1);
                    $index = $end = strpos($sql, $find2);
                    $childData[$namespace][$value] = substr($sql, $start + strlen($find1), $end - ($start + strlen($find1)));
                    $childData[$namespace][$value] = trim($childData[$namespace][$value]);
                    $sql = substr($sql, $index + strlen($find2));
                }
            }
        }
        if (empty($childData[$arr[1]][$arr[2]])) {
            $this->error = 'No sql template entry was matched';
            return '';
        }
        $sql = $childData[$arr[1]][$arr[2]];
        if (!empty($bindReplaceArr)) {
            foreach ($bindReplaceArr as $key => $value) {
                $sql = str_replace('${' . $key . '}', $value, $sql);
            }
        }
        return $sql;
    }


    /**
     * 查询列表
     * @param $sql
     * @param $data
     * @return array|bool
     */
    public function getAll($sql, $data = [])
    {
        if ($sql == '') {
            return false;
        }
        $this->reset();
        $statement = $this->prepare($sql, array(\PDO::ATTR_CURSOR => $this->attr_cursor));
        $res = $statement->execute($this->getBuildData($data));
        if (!empty($res)) {
            $tmp = $statement->fetchAll($this->fetch_style);
            return empty($tmp) ? [] : $tmp;
        }
        $this->error = $statement->errorInfo();
        return false;
    }

    /**
     * 查询一个
     * @param $sql
     * @param $data
     * @return array|bool
     */
    public function getOne($sql, $data = [])
    {
        if ($sql == '') {
            return false;
        }
        $this->reset();
        $statement = $this->prepare($sql, array(\PDO::ATTR_CURSOR => $this->attr_cursor));
        $res = $statement->execute($this->getBuildData($data));
        if (!empty($res)) {
            $tmp = $statement->fetchAll($this->fetch_style);
            return empty($tmp[0]) ? [] : $tmp[0];
        }
        $this->error = $statement->errorInfo();
        return false;
    }

    /**
     * 更新
     * @param $sql
     * @param $data
     * @return int
     */
    public function update($sql, $data = [])
    {
        if ($sql == '') {
            return false;
        }
        $this->reset();
        $statement = $this->prepare($sql, array(\PDO::ATTR_CURSOR => $this->attr_cursor));
        $res = $statement->execute($this->getBuildData($data));
        if (!empty($res)) {
            $tmp = $statement->rowCount();
            return $tmp;
        }
        $this->error = $statement->errorInfo();
        return false;
    }


    /**
     * 删除
     * @param $sql
     * @param $data
     * @return int
     */
    public function delete($sql, $data = [])
    {
        if ($sql == '') {
            return false;
        }
        $this->reset();
        $statement = $this->prepare($sql, array(\PDO::ATTR_CURSOR => $this->attr_cursor));
        $res = $statement->execute($this->getBuildData($data));
        if (!empty($res)) {
            $tmp = $statement->rowCount();
            return $tmp;
        }
        $this->error = $statement->errorInfo();
        return false;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 插入数据
     * @param $sql
     * @param $data
     * @return int
     */
    public function insert($sql, $data = [])
    {
        if ($sql == '') {
            return false;
        }
        $this->reset();
        $statement = $this->prepare($sql, array(\PDO::ATTR_CURSOR => $this->attr_cursor));
        $res = $statement->execute($this->getBuildData($data));
        if (!empty($res)) {
            $tmp = $statement->rowCount();
            return $tmp;
        }
        $this->error = $statement->errorInfo();
        return false;
    }

    protected function getBuildData($data = [])
    {
        $tmp = [];
        foreach ($data as $key => $val) {
            $tmp[':' . $key] = $val;
        }
        return $tmp;
    }

}