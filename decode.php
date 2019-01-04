<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/12
 * Time: 下午6:07
 */

$outFile = "/Users/luanjinyang/Desktop/out.xlsx";
$decodeFile = "/Users/luanjinyang/Desktop/decode.xlsx";


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
    $pattern = "/data-docid=\"([0-9]+)\"/";
    preg_match_all($pattern, $htmlCont, $matches);

    $data = array();
    $data = isset($matches[1]) ? $matches[1] : $matches[0];

    if (count($data) != 10) {
        file_put_contents('less.log', $keyWord, FILE_APPEND);
    }

    $data = array_merge($dataset, $data);
    file_put_contents($tmpFile, json_encode($data) . PHP_EOL, FILE_APPEND);
//    $writer->writeSheetRow('Sheet1', $data);
}