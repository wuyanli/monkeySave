<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/17
 * Time: 下午11:19
 */


//$dir = '/Users/luanjinyang/test/sqlite/outDir/';
//
//$leftDir = '/Users/luanjinyang/test/sqlite/leftDir/';
//
//$picDir = '/Users/luanjinyang/test/sqlite/pic/';
//
//$haveDown = '/Users/luanjinyang/test/sqlite/haveDown/';
//
//$files = scandir($dir);
//
//foreach ($files as $key => $file) {
//    try{
//        if (substr($file, 0, 1) == '.') {
//            continue;
//        }
//        if (is_dir($file)) {
//            exec('mv ' . $dir . $file . ' ' . $leftDir);
//            continue;
//        }
//
////        $arr[$file] = true;
////        continue;
//
//        $filePath = $dir . $file;
//        $dirPath = $picDir . $file;
//
//        $haveDownFile = $haveDown . $file;
//
////        echo $haveDownFile . PHP_EOL;
//
//        if (file_exists($haveDownFile)) {
////            echo '已下载' . PHP_EOL;
//            continue;
//        }
//
//        file_put_contents($haveDownFile, 'true');
//
//        if (!is_dir($dirPath)) {
//            mkdir($dirPath, 0777, true);
//        }
//
//        $cont = file_get_contents($filePath);
//        $contArray = explode(',', $cont);
//        foreach ($contArray as $key => $item) {
//            $picCont = file_get_contents($item);
//            file_put_contents($dirPath . '/' . ($key+1) . '.png', $picCont);
//        }
//    }catch (Exception $e) {
//
//    }
//
//}


/**
 * 下载滕刚的
 */

$num = isset($argv[1]) ? $argv[1] : null;
if ($num == null) {
    echo '请输入下载第几个文件夹'  . PHP_EOL;
    die();
}

$srcDir = '/Users/luanjinyang/test/chuanfeng/json/' . $num;
$outDir = $srcDir . '_out';
$logPath = '/Users/luanjinyang/test/chuanfeng/warn.log';


$files = scandir($srcDir);
foreach ($files as $file) {
    try{
        if (substr($file, 0, 1) == '.') {
            continue;
        }
        $filPath = $srcDir . '/' . $file;
        $cont = file_get_contents($filPath);
        $contArray = json_decode($cont, true);

        if (!isset($contArray['answers']) || !isset($contArray['title'])) {
            continue;
        }
        $outPath = $outDir . '/' . $contArray['title'] . '/2';
        $coverPath = $outDir . '/' . $contArray['title'] . '/1';
        if (!is_dir($outPath)) {
            mkdir($outPath, 0777, true);
        }
        if (!is_dir($coverPath)) {
            mkdir($coverPath, 0777, true);
        }
        $infoPath = $outPath . '/info.txt';
        if(file_exists($infoPath)) {
            continue;
        }

        $answers = $contArray['answers'];
        $answers = explode(',', $answers);
        foreach ($answers as $key => $answer) {
            if (empty(trim($answer))) {
                continue;
            }
            if (strpos($answer, '@') !== false) {
                list(, $img) = explode('@', $answer);
            }else{
                if ($key+1 < 10) {
                    $img = '00' . ($key+1) . '.png';
                }
                if ($key+1 >= 10 && $key+1 < 100) {
                    $img = '0' . ($key+1) . '.png';
                }
                if ($key+1 >= 100) {
                    $img = ($key+1) . '.png';
                }
            }
            $imgPath = $outPath . '/' . $img;

            $ctx = stream_context_create(array(
                    'http' => array('timeout' => 5,
                        'proxy' => 'tcp://124.42.7.103:80',//101.132.142.124:8080',
                        'request_fulluri' => True,)
                )
            );
            $imgCont = file_get_contents($answer, False, $ctx);
//            $imgCont = file_get_contents($answer);
            if (empty($imgCont)) {
                file_put_contents($logPath, date('Y-m-d H:i:s', time()) . $infoPath .'中的' . $img . '下载失败', FILE_APPEND);
                continue;
            }
            file_put_contents($imgPath, $imgCont);
        }
        $infoCont = '年纪:' . $contArray['grade'] . PHP_EOL;
        $infoCont .= '关键词：' . $contArray['keyword'] . PHP_EOL;
        $infoCont .= '标签：' . $contArray['title'] . PHP_EOL;
        $infoCont .= 'id:' . $contArray['bookId'] . PHP_EOL;
        $infoCont .= '图片数目:' . count(array_filter($answers)) . PHP_EOL;

        $img = file_get_contents($contArray['coverPage']);
        $imgFile = $coverPath . '/fm.png';
        file_put_contents($imgFile, $img);
        file_put_contents($infoPath, $infoCont);
die();
    }catch (Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }
}