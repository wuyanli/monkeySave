<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/12
 * Time: 下午12:44
 */

include_once('vendor/autoload.php');
$srcFile = '/Users/luanjinyang/Desktop/decode.file';
//$srcFile = "tmp.aa";
$outFile = '/Users/luanjinyang/Desktop/decode.xlsx';

$header = array(
    '书名'=>'string',//text
    '作者'=>'string',//text
    'doc_id1'=>'string',
    'doc_id2'=>'string',
    'doc_id3'=>'string',
    'doc_id4'=>'string',
    'doc_id5'=>'string',
    'doc_id6'=>'string',
    'doc_id7'=>'string',
    'doc_id8'=>'string',
    'doc_id9'=>'string',
    'doc_id10'=>'string',

);

$writer = new XLSXWriter();

$writer->writeSheetHeader('Sheet1', $header);

$fh = fopen($srcFile, 'r');
if (empty($fh)) {
    echo '大开文件错误' . PHP_EOL;
    exit(-1);
}
while(!feof($fh)) {
    $line = trim(fgets($fh));
    if (empty($line)) {
        continue;
    }
//    echo 'line:' . $line . PHP_EOL;
    $lineArray = json_decode($line, true);
    $writer->writeSheetRow('Sheet1', $lineArray);
}

$writer->writeToFile($outFile);
//$writer->writeToStdOut();
//echo $writer->writeToString();