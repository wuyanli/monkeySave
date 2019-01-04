<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/19
 * Time: 上午11:54
 */

include_once('vendor/autoload.php');

$outFile = 'C:\Users\Administrator\Desktop\checkSpaceResult.xlsx';

define('SEPARATOR', '/');

$dirPath = isset($argv[1]) ? $argv[1] : null;
if ($dirPath == null) {
    echo <<<EOL
        usage:
            php      c:\shell\checkInfo.php     d:\\test

EOL;
    die();
}

if (!is_dir($dirPath)) {
    echo $dirPath . '不是文件夹' . PHP_EOL;
    die();
}

if (substr($dirPath, strlen($dirPath)-1, 1) == '/') {
    $dirPath = substr($dirPath, 0, strlen($dirPath) - 1);
}

$files = scandir($dirPath);

$errorArray = array();
checkFile($files, $dirPath, $errorArray);

$header = array(
    '文件全路径'=>'string',//text
);

$writer = new XLSXWriter();
$writer->writeSheetHeader('Sheet1', $header);

foreach ($errorArray as $row) {
    $writer->writeSheetRow('Sheet1', array($row));
}

$writer->writeToFile($outFile);

function checkFile($files, $supDir, &$errorArray) {
    foreach ($files as $file) {
        if (substr($file, 0 ,1) == '.') {
            continue;
        }
        $filePath = $supDir . SEPARATOR . $file;
        if (strpos($file, ' ') !== false || strpos($file, ' ') !== false) {
            echo 'filePath:' . $filePath . PHP_EOL;
            $errorArray[] = $filePath;
        }

        if (is_dir($filePath)) {
            $subFiles = scandir($filePath);
            checkFile($subFiles, $filePath, $errorArray);
        }
    }
}

