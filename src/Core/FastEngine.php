<?php
/**
 * SqlTplEngine  file.
 * @author 林玉山 <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/php-enjoy-sql-engine
 * @copyright Copyright &copy; 2019-2100
 * @license https://github.com/1107012776/php-enjoy-sql-engine/blob/main/LICENSE
 */
namespace SqlTplEngine\Core;
/**
 * 快速构造引擎
 * Class FastEngine
 * @package SqlTplEngine\Core
 */
class FastEngine
{
    use ParsingTrait;



    //and string
    const CONDITION_AND = ' and ';


    /**
     * @var SPDO
     */
    protected $spdo = null;

    protected $_field = [];
    protected $_limit_str = '';
    protected $_order_str = '';
    protected $_group_str = '';
    protected $_field_str = '*';
    protected $_insert_data = [];
    protected $_update_data = [];
    protected $_last_insert_id = 0;  //最后插入的id
    protected $offset = 0; //偏移量
    protected $offset_limit = 0; //偏移之后返回数

    /**
     * FastEngine constructor.
     * @param SPDO $spdo
     * @param $tableName
     */
    public function __construct(SPDO $spdo, $tableName = '')
    {
        $this->spdo = $spdo;
        $this->tableName = $tableName;
    }

    public function where($condition){
        $keywords = [  //这边关键词不想限制太死了，不然有些时候输入关键词，会被过滤掉，导致开发者无法及时察觉错误
            'more', ';'
        ];
        foreach ($condition as $key => $val) {
            $key = trim($key);  //去空格
            if (in_array(strtolower($key), $keywords) //发现是条件关键词，则不允许
                || strpos($key, ' ') !== false //条件的key不能存在空格
                || strpos($key, ';') !== false //条件的key不能存在;
            ) {
                continue;
            }
            if (!isset($this->_condition[$key])) {
                $this->_condition[$key] = $val;
                continue;
            }
            if (isset($this->_condition[$key][0])
                && $this->_condition[$key][0] == 'more'
            ) {
                array_push($this->_condition[$key][1], $val);
            } else {  //为兼容一个键值多个查询条件
                $old = $this->_condition[$key];
                $this->_condition[$key] = [
                    'more', [$old]
                ];
                array_push($this->_condition[$key][1], $val);
            }
        }
        return $this;
    }


    public function insert($data = []){

    }

    public function update($data = []){
        $this->_pare();
    }

    public function delete(){
        $this->_pare();
    }

    public function find(){
        $this->_pare();
    }

    public function findAll(){
        $this->_pare();
    }

    /**
     * @param int $offset
     * @param $page_count
     * @return $this
     */
    public function limit($offset = 0, $page_count = null)
    {
        if (empty($page_count)) {
            $this->_limit_str = sprintf("%.0f", $offset);
        } else {
            $this->offset = sprintf("%.0f", $offset);  //偏移量必须单独处理，否者分页存在问题
            $this->offset_limit = sprintf("%.0f", $page_count);
            $this->_limit_str = sprintf("%.0f", $offset) . ',' . sprintf("%.0f", $page_count);
        }
        return $this;
    }

}