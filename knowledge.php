<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/20
 * Time: 下午9:14
 */

/**
 * 给一个知识点的文件生成表格
 */
include_once('vendor/autoload.php');

$knowledgePath = '/Users/luanjinyang/test/tenggang/知识点总结/高中/';

$files = scandir($knowledgePath);
$rows = array();
$writer = new XLSXWriter();
foreach ($files as $key => $file) {
    if (substr($file, 0, 1) == '.') {
        continue;
    }
    $filePath = $knowledgePath . $file;
    if (!file_exists($filePath)) {
        echo $filePath . '不存在' . PHP_EOL;
        continue;
    }
    $fh = fopen($filePath, 'r');
    while(!feof($fh)) {
        $line = trim(fgets($fh),'()');
        $line = substr($line, 0 , strlen($line)-2);
        $line = str_replace('\'', '', $line);
        if (empty($line)) {
            continue;
        }
        $tmpArray = explode(',', $line);
        $rows[] = $tmpArray;
    }



//$writer->writeSheetHeader('Sheet1', $header);


    foreach ($rows as $row) {
        $writer->writeSheetRow('Sheet' . ($key+1), $row);
    }

    $rows = array();

}

$writer->writeToFile('b1.xlsx');
//$header = array(
//    '知识点1' => 'string',//text
//    '知识点1-1' => 'string',//text
//    '知识点1-1-1' => 'string',
//);

