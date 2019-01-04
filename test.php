<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/18
 * Time: 下午9:29
 */

//$renameDir = '/Users/luanjinyang/test/tenggang/json/1/';

//$files = scandir($renameDir);
//$titles = array();
//foreach ($files as $file) {
//    if (substr($file, 0, 1) == '.') {
//        continue;
//    }
//    $filePath = $renameDir . $file;
//    $cont = file_get_contents($filePath);
//    $contArray = json_decode($cont, true);
//    $titles[$contArray['title']] = true;
//}

//var_dump(count($titles));

//
//$zipDir = '/home/ftpuser/data/zyb-spider/local/';
//$zips = scandir($zipDir);
//foreach ($zips as $zip) {
//    if (substr($zip, 0, 1) == '.') {
//        continue;
//    }
//    if (substr($zip, strlen($zip)-3, 3) != 'zip') {
//        continue;
//    }
//    $newDir = intval($zip);
//    $newDirPath = $zipDir. $newDir;
//    $zipPath = $zipDir . $zip;
//    exec('unzip ' . $zipPath . ' -d ' . $newDirPath);
//    die();
//}
//
//
//
//
//
//$zipDir = '/home/ftpuser/data/zyb-spider/local/';
//$zips = scandir($zipDir);
//foreach ($zips as $zip) {
//    if (substr($zip, 0, 1) == '.') {
//        continue;
//    }
//    $dirname = $zipDir . $zip;
//    if (is_dir($dirname)) {
//        exec('rm -rf ' . $dirname);
//    }
//    die();
//}
//
//$localPath = '/home/ftpuser/data/zyb-spider/local/';
//$newLocalPath = '/home/ftpuser/data/zyb-spider/local.use/';
//$dirs = scandir($localPath);
//foreach ($dirs as $dir) {
//    if (substr($dir, 0, 1) == '.'){
//        continue;
//    }
//    $dirPath = $localPath . $dir;
//    if (!is_dir($dirPath)) {
//        continue;
//    }
//    $dirMovePath = $localPath . $dir . '/yddata/' . $dir;
//    echo 'move ' . $dirMovePath . ' ' . $newLocalPath . PHP_EOL;
//    die();
//    exec('mv ' . $dirMovePath . ' ' . $newLocalPath);
//
//}
//
//
//
//$localPath = '/home/ftpuser/data/zyb-spider/local/';
//$dirs = scandir($localPath);
//foreach ($dirs as $dir) {
//    if (substr($dir, 0, 1) == '.'){
//        continue;
//    }
//    $dirPath = $localPath . $dir;
//    if (!is_dir($dirPath)) {
//        continue;
//    }
//    $mofanggeFile = $localPath . $dir . '/www.mofangge.com.txt';
//    $zybFile = $localPath . $dir . '/www.zybang.com.txt';
//  //  unlink($mofanggeFile);
//  //  unlink($zybFile);
//    echo $mofanggeFile . PHP_EOL;
//    die();
//}

$hanjiaDir = '/Users/luanjinyang/test/sqlite/coverjson/寒假作业以清空水印/';
$dirs = scandir($hanjiaDir);
foreach ($dirs as  $key => $dir) {
    if (substr($dir, 0, 1) == '.') {
        continue;
    }
    $dir2 = $hanjiaDir . $dir . '/2/';
    if (!is_dir($dir2)) {
        continue;
    }
    $files = scandir($dir2);
    foreach ($files as  $file) {
        if (substr($file, 0, 1) == '.') {
            continue;
        }
        if (substr($file, strlen($file)-3, 3) != 'jpg') {
            continue;
        }
//        if ($file == '001.jpg' || $file == 'info.txt') {
//            $delFile = $dir2 . $file;
////            echo '删除文件' . $delFile . PHP_EOL;
//            unlink($delFile);
//            continue;
//        }
        $filePath = $dir2 . $file;
        $num = intval($file);
        if ($num < 10) {
            $num = '00' . $num;
        }else{
            if ($num >= 10 && $num < 100) {
                $num = '0' . $num;
            }
        }
        $fileNewPath = $dir2 . $num . '.png';
        rename($filePath, $fileNewPath);
    }
    if ($key > 2) {
        die();
    }

}

