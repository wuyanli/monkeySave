<?php
/**
    切割天哥机子上面的下载的zyb.txt
 */

ini_set('memory_limit', -1);

$outPath = '/Users/luanjinyang/test/1/1/out/';

$logPath = 'splitHtml.log';

$srcPath = isset($argv[1]) ? $argv[1] : NULL;
if ($srcPath === NULL) {
    die('请输入切割根目录');
}
if (!is_dir($srcPath)) {
    die($srcPath . '不是文件夹');
}

$s = new SplitHtml($srcPath);
$s->main();

class SplitHtml{

    private  $_srcPath = NULL;

    private $_outPath = NULL;

    public function __construct($srcPath){
        if (substr($srcPath, strlen($srcPath)-1, 1) != '/') {
            $srcPath .= '/';
        }
        $this->_srcPath = $srcPath;
        $this->_outPath = dirname($this->_srcPath) . '/splitOut/';
    }

    public function main() {
        $this->parseSrc();
    }


    private function parseSrc() {
        $numberDirs = scandir($this->_srcPath);
        foreach ($numberDirs as $numberDir) {
            if (substr($numberDir, 0, 1) == '.') {
                continue;
            }
            $numberPath = $this->_srcPath . $numberDir;

            if (!is_dir($numberPath)) {
                continue;
            }
            $zybFile = $this->_srcPath . $numberDir . '/www.zybang.com.txt';
            if (!file_exists($zybFile)) {
                continue;
            }
            echo '...';
            $convertPath = $this->_srcPath . $numberDir . '/zyb.txt';
            exec('iconv -c -f gb18030 -t utf-8 ' . $zybFile . ' > ' . $convertPath);
            $cont = file_get_contents($convertPath);
            $cont = preg_replace("/[\t\n\r]+/","",$cont);
            $pattern = "/~BUF(.*?)~EOF!/";
            preg_match_all($pattern, $cont, $matches);
            if (empty($matches)) {
                continue;
            }

            foreach ($matches[1] as $key =>  $match) {
                $patternUrl = '/http:\/\/www\.zybang\.com\/question\/([0-9a-z]+)\.html/';
                $patternHtml = '/<!DOCTYPE html>(.+?)<\/html>/';
                preg_match($patternUrl, $match, $matchUrl);
                preg_match($patternHtml, $match, $matchHtml);

                if (!isset($matchHtml[1]) || empty($matchHtml[1])) {
                    continue;
                }

                if (!isset($matchUrl[1]) || empty($matchUrl[1])) {
                    continue;
                }

                $filename = $this->_outPath . $matchUrl[1] . '.html';
                if (!is_dir(dirname($filename))) {
                    mkdir(dirname($filename), 0777, true);
                }
                file_put_contents($filename, '' . $matchHtml[1] . '</html>');
            }
        }
    }
}

