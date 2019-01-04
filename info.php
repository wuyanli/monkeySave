<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/17
 * Time: 下午4:29
 */

include 'vendor/autoload.php';

define('LOG_FILE', 'info.log');

$excelPath = isset($argv[1]) ? $argv[1] : null;

if ($excelPath == null) {
    echo <<<EOL
     
    用法：
        php    info.php   D:\aa\bb.xlsx

EOL;
    die();
}

if (!file_exists($excelPath)) {
    echo $excelPath . '不存在' . PHP_EOL;
    die();
}

const SEPARATOR = '/';


$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelPath);
$sheet = $spreadsheet->getSheet(0);
$highestRow = $sheet->getHighestRow();
$highestColumm = $sheet->getHighestColumn();

for ($row=2; $row <= $highestRow; $row++) { //行号从2开始
    $dataset = array();
    $outData = array();

    $str = '';
    for ($column = 'A'; $column <= $highestColumm; $column++) { //列数是以A列开始
        switch ($column) {
            case 'A' :
                $key = '标题';
                $filename = $sheet->getCell($column . $row)->getValue();
                break;
            case 'B' :
                $key = '科目';
                break;
            case 'C' :
                $key = '年级';
                break;
            case 'D' :
                $key = '上下册';
                break;
            case 'E' :
                $key = '版本';
                break;
            case 'F' :
                $key = '版序';
                break;
            case 'G' :
                $key = 'ISBN';
                break;
            case 'H' :
                $key = '出版社';
                break;
            case 'I' :
                $key = '年份';
                break;
            case 'J' :
                $key = '出版时间';
                break;
            case 'K' :
                $key = '印刷时间';
                break;
            case 'L' :
                $key = '页数';
                break;
            case 'M' :
                $key = '类别';
                break;
            case 'N' :
                $key = '其他';
                break;
            case 'O' :
                $key = '上传者';
                break;
            default:
                break;
        }
        if ($column != 'L') {
            $str .= $key . ':' . $sheet->getCell($column . $row)->getValue() . PHP_EOL;
        }else {
            $picNum = getPicNum($filename, $excelPath);
            $str .= $key . ':' . $picNum . PHP_EOL;
        }

    }
    $str = substr($str, 0, strlen($str) - 1);
    $filePath = dirname($excelPath) . SEPARATOR . $filename . SEPARATOR . '1' . SEPARATOR . 'info.txt';
    $dirPath = dirname($filePath);
    if (!is_dir($dirPath)) {
        mkdir($dirPath, 0777, true);
    }
    file_put_contents($filePath, $str);
}

function getPicNum($filename, $excelPath) {
    $filepath = dirname($excelPath) . '/' . $filename;
    $dir2 = $filepath . '/2';
    if (!is_dir($dir2)) {
        file_put_contents(LOG_FILE, '[' . date('Y-m-d H:i:s', time()) . '] ' . $filepath . '不存在文件夹2' . PHP_EOL, FILE_APPEND);
        return -1;
    }
    $pics = scandir($dir2);
    $picNum = 0;
    foreach ($pics as $pic) {
        if (substr($pic, strlen($pic)-3, 3) != 'png') {
            continue;
        }
        $picNum += 1;
    }
    return $picNum;
}