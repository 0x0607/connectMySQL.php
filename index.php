<?php
// 
// +--------------------------------------------------------------------------+
// | 主程式
// +--------------------------------------------------------------------------+
//
require("DBConnection.php");
use Classes\DBConnection;

$DB = new DBConnection("test", "127.0.0.1", 3306, "root", "passwd");

$result = $DB->catch("SELECT VERSION() AS VERSION");
echo $result ? $result[0]['VERSION'] : "Failed to connect to database.";