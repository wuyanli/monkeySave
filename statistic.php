<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/21
 * Time: 下午8:16
 */

/**
 * 统计流量
 */
include_once('vendor/autoload.php');

$statisticFile = '/Users/luanjinyang/test/core/gaia.data.1';

$fh = fopen($statisticFile, 'r');
$total = array();
$i = 0;
while (!feof($fh)) {
    $line = trim(fgets($fh));
    $lineArray = explode(' 1001 ', strToBin($line));
    $num = binToStr(array_pop($lineArray));
    $com = binToStr(array_pop($lineArray));
    if (empty($num) || empty($com) || $num == 'NULL' || $com == 'NULL') {
        continue;
    }
    if (!isset($total[$com])) {
        $total[$com]['num'] = intval($num);
        $total[$com]['com'] = $com;
    }else{
        $total[$com]['num'] += intval($num);
    }
//    if ($i > 20) {
//        break;
//    }
//    $i++;
}

$ret = arraySort($total, 'num');
$m = 0;
foreach ($ret as $key => $item) {
    echo $item['com'] . ':' . $item['num'] . PHP_EOL;
    $m++;
    if ($m > 100) {
        break;
    }
}
function arraySort($array,$keys,$sort='desc') {
    $newArr = $valArr = array();
    foreach ($array as $key=>$value) {
        $valArr[$key] = $value[$keys];
    }
    ($sort == 'asc') ?  asort($valArr) : arsort($valArr);//先利用keys对数组排序，目的是把目标数组的key排好序
    reset($valArr); //指针指向数组第一个值
    foreach($valArr as $key=>$value) {
        $newArr[$key] = $array[$key];
    }
    return $newArr;
}


function strToBin($str){
    //1.列出每个字符
    $arr = preg_split('/(?<!^)(?!$)/u', $str);
    //2.unpack字符
    foreach($arr as &$v){
        $temp = unpack('H*', $v);
        $v = base_convert($temp[1], 16, 2);
        unset($temp);
    }

    return join(' ',$arr);
}

function binToStr($str){
    $arr = explode(' ', $str);
    foreach($arr as &$v){
        $v = pack("H".strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
    }

    return join('', $arr);
}