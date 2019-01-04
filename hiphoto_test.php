<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2019/1/2
 * Time: 上午10:39
 */

ini_set('memory_limit', -1);

$imgPath = '/Users/luanjinyang/test/souti/10500/02cc3b7816edc595df17d18d819ef8d7/content.png';
if (!is_file($imgPath)) {
    echo 'is not file' . PHP_EOL;
    die();
}

$imgCont = file_get_contents($imgPath);
echo 'contLength:' . strlen($imgCont) . PHP_EOL;

$imgId = Wenku_Util_Image::upload($imgCont);
echo 'imgId:' . $imgId . PHP_EOL;
$picUrl = Wenku_Util_Image::picId2UrlBig($imgId);
echo 'picUrl:' . $picUrl . PHP_EOL;