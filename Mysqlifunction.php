<?php

/**
 * 資料庫
 */
class Mysqlifunction
{
    /**
     * db
     */
    private $db;

    /**
     * 位置
     */
    private $hostname;

    /**
     * 帳號
     */
    private $username;

    /**
     * 密碼
     */
    private $password;

    /**
     * 資料庫
     */
    private $database;

    /**
     * 字元
     */
    private $character;

    public function __construct(array $config = null)
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }

        $this->connectDb();
    }

    /**
     * 資料庫連線
     */
    private function connectDb()
    {
        //連線
        $this->db = mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
        if (!$this->db) {
            echo "Error: Unable to connect to MySQL.".PHP_EOL;
            echo "Debugging errno:".mysqli_connect_errno().PHP_EOL;
            echo "Debugging error:".mysqli_connect_error().PHP_EOL;
            exit;
        }

        //字元設定
        mysqli_query($this->db, "SET NAMES '$this->character'");

        //預防資料過大
        set_time_limit(0);
    }

    /**
     * 資料庫備份
     */
    public function backup()
    {
        $db = $this->db;

        $filename = 'backup'.date('Ymd').'.sql';
        header("Content-type: text/sql");
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header("Pragma: no-cache");
        header("Expires: 0");

        //基本資訊
        echo '-- PHP版本:'.phpversion().";\r\n";
        echo '-- 時間:'.date('Y-m-d H:i:s').";\r\n\n";

        //設定
        echo 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";'."\r\n";
        echo 'SET AUTOCOMMIT = 0;'."\r\n";
        echo 'START TRANSACTION;'."\r\n\n";

        $tables = mysqli_query($db, "show tables");
        while ($table = mysqli_fetch_row($tables)) {
            $tableName = $table[0];
            $createTable = mysqli_query($db, "show create table `$tableName`");
            if (mysqli_num_rows($createTable)) {
                echo '-- 資料表'.$tableName.";\r\n";

                while ($createDetail = mysqli_fetch_row($createTable)) {
                    echo "DROP TABLE IF EXISTS `$tableName`;\r\n";
                    echo $createDetail[1].";\r\n";

                    $data = mysqli_query($db, "SELECT * FROM `$tableName`");
                    if (mysqli_num_rows($data)) {
                        echo "\r\n";
                        while ($d = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                            $keys = array_keys($d); //返回key value
                            $keys = array_map('addslashes',$keys); //將『 " 』隱藏
                            $keys = join('`,`',$keys);
                            $keys = "`".$keys."`";
                            $vals = array_values($d);
                            $vals = join("','",$vals);
                            $vals = "'".$vals."'";
                            echo "INSERT INTO `$tableName`($keys) VALUES($vals);\r\n";
                        }
                    }
                }
            }
            echo "\r\n";
        }

        mysqli_close($db);
    }

    /**
     * 資料庫還原
     */
    public function reduction($filePath = '')
    {
        if (empty($filePath) ) {
            return false;
        }

        if (!is_file($filePath)) {
            return false;
        }

        $db = $this->db;

        $sqls = file($filePath);
        $sqls = explode(";\r\n", implode('', $sqls));
        $count = 0;
        $errorCount = 0;
        $errorPart = [];

        foreach ($sqls as $content) {
            if (!empty($content)) {
                if (!mysqli_query($db, $content)) {
                    $errorPart[$count] = $content;
                    $errorCount++;
                }
            }
            $count++;
        }

        if (!empty($errorPart)) {
            echo '失敗:'.$errorCount."\r\n";
            echo '錯誤段落如下:'."\r\n";
            echo '<hr>';
            foreach ($errorPart as $part => $error) {
                echo '第'.$part.':'.$error."\r\n";
            }
        } else {
            echo '成功';
        }
    }
}
