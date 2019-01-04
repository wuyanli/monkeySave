<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/17
 * Time: 下午10:46
 */

$outDir = '/Users/luanjinyang/test/suyan/json/';
$haveDown = '/Users/luanjinyang/test/sqlite/json/';


class SQLiteDB extends SQLite3
{
   function __construct()
   {
       $this->open('/Users/luanjinyang/test/suyan/kuaidui39200.sqlite');
   }
}
$db = new SQLiteDB();
if(!$db){
    echo $db->lastErrorMsg();
} else {
    echo "Yes, Opened database successfully\n";

    $sql = " select * from  lefe_main  ";

    //检索所有记录
    $result = $db->query($sql);
    $i = 0;
    $dirnum = 0;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {

        try{
            if ($i % 5000 == 0) {
                $dirnum += 1;
            }
            if (!isset($row['answers'])) {
                continue;
            }
            $filename = isset($row['title']) ? $row['title'] : 'random' . rand(0-9999) ;
            $filename = $outDir .  $dirnum . '/' . $filename . '.json';
            $haveDownFile = $haveDown . $row['title'];
            if (file_exists($filename)) {
                echo $filename . '已经存在' . PHP_EOL;
                continue;
            }

            $dirname = dirname($filename);
            if (!is_dir($dirname)) {
                mkdir($dirname, 0777, true);
            }
            file_put_contents($filename, json_encode($row));
            $i++;
//            file_put_contents($downFile, true);
        }catch (Exception $e) {
            echo 'title:' . $row['title'] . PHP_EOL;
            echo 'key:' . $key . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
        }
//        die();
    }

}