<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/12
 * Time: 上午10:28
 */

/**
 * 获取文库搜索界面的doc_id
 */

include "vendor/autoload.php";

use QL\QueryList;
use PhpOffice\PhpSpreadsheet;

$srcExcel = "/Users/luanjinyang/Desktop/文科书籍.xlsx";
$outExcel = "/Users/luanjinyang/Desktop/out.xlsx";
$tmpFile = "/Users/luanjinyang/Desktop/tmp.file";
$tmpDecodeFile = "/Users/luanjinyang/Desktop/tmpDecode.file";

$header = array(
    "书名" => "string",
    "作者" => "string",
    "doc_id1" => "string",
    "doc_id2" => "string",
    "doc_id3" => "string",
    "doc_id4" => "string",
    "doc_id5" => "string",
    "doc_id6" => "string",
    "doc_id7" => "string",
    "doc_id8" => "string",
    "doc_id9" => "string",
    "doc_id10" => "string",
);
//$write = new XLSXWriter();
//$writer->writeSheetHeader('Sheet1', $header);

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($srcExcel);
$sheet = $spreadsheet->getSheet(0);
$highestRow = $sheet->getHighestRow();
$highestColumm = $sheet->getHighestColumn();

for ($row=6238; $row <= $highestRow; $row++) { //行号从1开始
    $dataset = array();
    for ($column = 'A'; $column <= $highestColumm; $column++) { //列数是以A列开始
        $dataset[] = $sheet->getCell($column . $row)->getValue();
//        echo $column . $row . ":" . $sheet->getCell($column . $row)->getValue() . "<br\>";
    }

    $keyWord = trim($dataset[0] . " " .  $dataset[1]);

    $url = "https://wenku.baidu.com/search?lm=0&od=0&fr=top_home&word=" . urlencode($keyWord);
    $htmlCont = QueryList::get($url)->getHtml();

    $matches = array();
    $pattern = "/data-edocid=\"([0-9a-zA-Z]+)\"/";
    preg_match_all($pattern, $htmlCont, $matches);

    $data = array();
    $data = isset($matches[1]) ? $matches[1] : $matches[0];

    if (count($data) != 10) {
        file_put_contents('less.log', $keyWord, FILE_APPEND);
    }

    $data = array_merge($dataset, $data);
    file_put_contents($tmpDecodeFile, json_encode($data) . PHP_EOL, FILE_APPEND);
//    $writer->writeSheetRow('Sheet1', $data);
}

//$writer->writeToFile($outExcel);
