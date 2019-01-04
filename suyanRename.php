<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/20
 * Time: 下午9:55
 */

$picDir = '/Volumes/only/素燕后半部分文件/pic/';

$dirs = scandir($picDir);
foreach ($dirs as $dir) {
    echo 'dir:' . $dir . PHP_EOL;
    if (substr($dir, 0, 1) == '.') {
        continue;
    }
    $dir2 = $picDir . $dir . '/2';
    if (!is_dir($dir2)) {
        continue;
    }
    $files = scandir($dir2);
    foreach ($files as $file) {
        if (substr($file, 0, 1) == '.') {
            continue;
        }
        if (substr($file,  strlen($file)-3, 3) != 'png') {
            continue;
        }
        $num = intval($file);
        if ($num < 10) {
            $imgFile = '00' . $num . '.png';
        }
        if ($num >= 10 && $num < 100) {
            $imgFile = '0' . $num . '.png';
        }
        $oldFile = $dir2 . '/' . $file;
        $newFile = $dir2 . '/' . $imgFile;
        rename($oldFile, $newFile);
    }

}