<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/20
 * Time: 上午10:54
 */

//
//$srcDir = '/Users/luanjinyang/test/sqlite/kuaidui14447.sqlite';
//$jsonDir = '/Users/luanjinyang/test/sqlite/json/';
//$logPath = '/Users/luanjinyang/test/tenggang/warn.log';
//
//class SQLiteDB extends SQLite3
//{
//    function __construct()
//    {
//        $this->open('/Users/luanjinyang/test/sqlite/kuaidui14447.sqlite');
//    }
//}
//$db = new SQLiteDB();
//if(!$db){
//    echo $db->lastErrorMsg();
//} else {
//    echo "Yes, Opened database successfully\n";
//    $sql = "select * from lefe_main";
//
//    //检索所有记录
//    $result = $db->query($sql);
//    $i = 0;
//    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
//       $filename = $row['title'];
//       $filePath = $jsonDir . $filename;
//       if (file_exists($filePath)) {
//           continue;
//       }
//        file_put_contents($filePath, json_encode($row));
//    }
//
//}

/**
 * 找封面不存在的脚本第一段
 */
//$jsonInfoPath = '/Users/luanjinyang/test/sqlite/json/';
//$coverJsonPath = '/Users/luanjinyang/test/sqlite/coverjson/';
//$jsons = scandir($coverJsonPath);
//foreach ($jsons as $json) {
//    if (substr($json, 0, 1) == '.') {
//        continue;
//    }
//    $jsonFile = $coverJsonPath . $json;
//    $jsonCoverDir = mb_substr($jsonFile, 0, mb_strlen($jsonFile) - 5);
//    if (!is_dir($jsonCoverDir)) {
//        mkdir($jsonCoverDir, 0777, true);
//    }
//    $jsonCont = file_get_contents($jsonFile);
//    $jsonArray = json_decode($jsonCont, true);
//    foreach ($jsonArray as $key => $item) {
//        $jsonInfoFile = $jsonInfoPath . $item;
//        if (!file_exists($jsonInfoFile)) {
//            echo $json . '/' . $jsonInfoFile . '不存在' . PHP_EOL;
//            continue;
//        }
//        $downJsonCont = file_get_contents($jsonInfoFile);
//        $downJsonArray = json_decode($downJsonCont, true);
//        if (!isset($downJsonArray['coverPage'])) {
//            echo $json . '/' . $jsonInfoFile . '不存在封面图' . PHP_EOL;
//            continue;
//        }
//        $outputFile = $jsonCoverDir . '/' . $item . '/1/fm.png';
//        if (!is_dir(dirname($outputFile))) {
//            mkdir(dirname($outputFile), 0777, true);
//        }
//        $img = file_get_contents($downJsonArray['coverPage']);
//        file_put_contents($outputFile, $img);
//
//    }
//}


/**
 * 找封面的脚本不存在第二段,用前面找没有找到的脚本
 */
$jsonInfoPath = '/Users/luanjinyang/test/sqlite/json/';
$coverDir = '/Users/luanjinyang/test/sqlite/coverjson/';
$jsonFiles = scandir($jsonInfoPath);

$unfoundPath = '/Users/luanjinyang/test/sqlite/coverjson/notFound';
$fh = fopen($unfoundPath, 'r');
while (!feof($fh)) {
    $line = trim(fgets($fh));
    if (empty($line)) {
        continue;
    }
    list($author, $file) = explode('/', $line);
    $authorDir = $coverDir . $author . '/' . $file . '/1/';
    if(!is_dir($authorDir)) {
        mkdir($authorDir, 0777, true);
    }
    $flag = false;
    foreach ($jsonFiles as $jsonFile) {
        if (strpos($jsonFile, $file) === false) {
            continue;
        }
        $flag = true;
        $jsonInfoFile = $jsonInfoPath . $jsonFile;
        $jsonCont = file_get_contents($jsonInfoFile);
        $jsonArray = json_decode($jsonCont, true);
        $img = file_get_contents($jsonArray['coverPage']);
        file_put_contents($authorDir . 'fm.png', $img);
    }

    if (!$flag) {
        echo $line . ' not found'  . PHP_EOL;
    }

}



/**
 * 素燕的后半部分，把sqlite切割为5000个json一个文件夹的形式
 */
//$logFile = '/Users/luanjinyang/test/sqlit/error.log';
//$jsonInfoDir = '/Users/luanjinyang/test/sqlite/json/';
//
//$picRoot = '/Volumes/only/素燕后半部分文件/pic/';
//$pics = scandir($picRoot);
//foreach ($pics as $pic) {
//    echo 'pic:' . $pic . PHP_EOL;
//    if(substr($pic, 0, 1) == '.') {
//        continue;
//    }
//    $jsonInfoFile = $jsonInfoDir . $pic;
//    if (!file_exists($jsonInfoFile)) {
//        file_put_contents($logFile, $pic . ' not found' . PHP_EOL, FILE_APPEND);
//        continue;
//    }
//    $str = '';
//    $picDir = $picRoot . $pic;
//    $picDir2 = $picDir . '/2';
//    if (!is_dir($picDir2)) {
//        mkdir($picDir2, 0777, true);
//    }
////    echo 'mv ' . $picDir . '/*.png ' .$picDir2 . PHP_EOL;
//    exec('mv ' . $picDir . '/*.png ' .$picDir2);
//    $picDir1 = $picDir . '/1';
//    if (!is_dir($picDir1)) {
//        mkdir($picDir1, 0777, true);
//    }
//    $jsonCont = file_get_contents($jsonInfoFile);
//    $jsonArray = json_decode($jsonCont, true);
//    if (!isset($jsonArray['coverPage'])) {
//        echo $jsonInfoFile . '没有封面图' . PHP_EOL;
//        continue;
//    }
//    $img = file_get_contents($jsonArray['coverPage']);
//    $fmPath = $picDir1 . '/fm.png';
//    file_put_contents($fmPath, $img);
//    $infoTxt = $picDir2 . '/info.txt';
//    $answers = $jsonArray['answers'];
//    $answerArr = explode(',', $answers);
//    $answerNUm = count(array_filter($answerArr));
//    $str = '图片个数：' . $answerNUm . PHP_EOL;
//    file_put_contents($infoTxt, $str);
////    die();
//}



