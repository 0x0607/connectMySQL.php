<?php

namespace Classes;

use PDO, Exception, PDOException, InvalidArgumentException;
//
// +--------------------------------------------------------------------------+
// | 資料庫交互Interface
// +--------------------------------------------------------------------------+
// | 用於處理基本資料庫相關作業
// +--------------------------------------------------------------------------+
//
interface Database
{
    /**
     * 連線至資料庫
     * 
     * @param string    $dbname 資料庫名稱
     * @param string    $host 位址
     * @param int       $port 端口
     * @param string    $user 用戶
     * @param string    $passwd 密碼
     * @param string    $charset 字符集
     * @return void
     */
    public function connect($dbname, $host, $port, $user, $passwd, $charset);

    /**
     * 抓取資料
     * 
     * @param string    $query SQL語句
     * @param array     $params 代填入值
     * @return array|null
     */
    public function catch($query, $params);

    /**
     * 提交資料
     * 
     * @param string    $query SQL語句
     * @param array     $params 代填入值
     * @return int
     */
    public function commit($query, $params);
}
//
// +--------------------------------------------------------------------------+
// | 連線至資料庫
// +--------------------------------------------------------------------------+
// | 使用PDO進行資料庫交互
// +--------------------------------------------------------------------------+
//
class PDOConnection implements Database
{
    protected static $debugmode = false;
    protected static PDO $connection;

    public function __construct()
    {
        self::$debugmode = defined('DEBUG') ? DEBUG : false;
    }

    // 確認連線狀態
    private function check_connection()
    {
        if (empty(self::$connection)) throw new Exception("Error: Database connection failed.");
        return null;
    }

    public function connect($dbname, $host, $port, $user, $passwd, $charset)
    {
        $dsn = "mysql:dbname={$dbname};host={$host};port={$port};charset={$charset}";
        try {
            $connection = new PDO($dsn, $user, $passwd);
            // $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $connection;
        } catch (Exception $e) {
            if (self::$debugmode) exit($e);
            else exit('Error: Connect to MySQL was failed.');
        }
    }

    public function commit($query, $params = [])
    {
        try {
            $this->check_connection();
            queryFormat::check_data_type($params);

            if (empty($params)) {
                $stmt = self::$connection->query($query);
                $result = $stmt->rowCount();
            } else {
                $stmt = self::$connection->prepare($query);
                $stmt->execute($params);
                $result = $stmt->rowCount();
            }

            return $result;
        } catch (PDOException $e) {
            if (self::$debugmode) throw new Exception($e->getMessage());
            else throw new Exception('Error: Commit rows was failed.');
        }
    }

    public function catch($query, $params = [])
    {
        try {
            $this->check_connection();
            queryFormat::check_data_type($params);

            if (empty($params)) $stmt = self::$connection->query($query);
            else {
                $stmt = self::$connection->prepare($query);
                $stmt->execute($params);
            }
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (PDOException $e) {
            if (self::$debugmode) throw new Exception($e->getMessage());
            else throw new Exception('Error: Catch data was failed.');
        }
    }
}
//
// +--------------------------------------------------------------------------+
// | 連線至資料庫
// +--------------------------------------------------------------------------+
// | 使用Mysqli進行資料庫交互
// +--------------------------------------------------------------------------+
//
class MysqliConnection implements Database
{
    public function connect($dbname, $host, $port, $use, $passwd, $charset)
    {
        return null;
    }

    public function catch($query, $params)
    {
        return null;
    }

    public function commit($query, $params)
    {
        return null;
    }
}
// 
// +--------------------------------------------------------------------------+
// | 資料庫預備語句格式化工具
// +--------------------------------------------------------------------------+
// | 簡單處理一些預填入值
// +--------------------------------------------------------------------------+
//
class queryFormat
{
    public function __construct()
    {
        return null;
    }

    /**
     * 確認預備語句參數資料型態
     * 
     * @param array     $params 參數陣列
     * @return null   
     */
    public static function check_data_type(&$params)
    {
        if (!is_array($params)) throw new InvalidArgumentException("Error: wrong data type.");
        return null;
    }

    /**
     * 格式化欄位名稱
     * 
     * @param array     $params 欄位名稱陣列
     * @return string   格式化後的欄位名稱字串
     */
    public static function format_columns(&$params)
    {
        self::check_data_type($params);
        $result = array_map(function ($key) {
            return $key === '*' ? $key : "`{$key}`";
        }, array_keys($params));
        return implode(', ', $result);
    }

    /**
     * 格式化佔位符
     * 
     * @param array     $columnParams 欄位名稱陣列
     * @return string   格式化後的佔位符字串
     */
    public static function format_placeholders(&$columnParams)
    {
        self::check_data_type($params);
        return implode(', ', array_fill(0, count($columnParams), '?'));
    }

    /**
     * 格式化條件
     * 
     * @param array     $params 條件陣列
     * @param string    $split 分隔符，預設為逗號
     * @return string   格式化後的條件字串
     */
    public static function format_conditions(&$params, $split = ',')
    {
        self::check_data_type($params);
        $split = strtoupper(trim($split));

        if (!in_array($split, ['AND', 'OR', ','])) $split = ',';


        $result = array_map(fn($key) => "`{$key}` = ?", array_keys($params));
        return implode(" {$split} ", $result);
    }
}
//
// +--------------------------------------------------------------------------+
// | 連線至資料庫
// +--------------------------------------------------------------------------+
// | 使用PDO方式連線 (自選extends)
// +--------------------------------------------------------------------------+
//
class DBConnection extends PDOConnection
{
    public function __construct($dbname, $host = "127.0.0.1", $port = 3306, $user = "root", $passwd = "root", $charset = 'utf8mb4')
    {
        if (empty(self::$connection)) self::$connection = $this->connect($dbname, $host, $port, $user, $passwd, $charset);
    }

    /**
     * 測試連線至資料庫
     * @return array
     */
    public function try_connect()
    {
        $result = $this->catch("SELECT VERSION() AS VERSION;");
        return !empty($result) ? $result[0]['VERSION'] : "ERROR";
    }
}
