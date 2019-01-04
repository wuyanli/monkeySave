<?php
/**
    把日期里面的文件夹中有  xxxx.png的挑出来
 */

$rootPath = isset($argv[1]) ? $argv[1] : NULL;
if ($rootPath === NULL) {
    die('请输入挑选的根目录');
}
if (!is_dir($rootPath)) {
    die($rootPath . '不存在');
}

$c = new ChoosePic($rootPath);
$c->main();

class ChoosePic{

    private $_rootPath = NULL;

    private $_outDir = '/Users/luanjinyang/test/bdPic/';

    private $_dateDir = NULL;

    private $_0Or1 = NULL;

    private $_codeDir = NULL;

    public function __construct($rootPath){
        if (substr($rootPath, strlen($rootPath)-1, 1) != '/') {
            $rootPath .= '/';
        }
        $this->_rootPath = $rootPath;
    }

    public function main(){
        $this->parseRoot();
    }

    private function parseRoot() {
        $dateDirs = scandir($this->_rootPath);
        foreach ($dateDirs as $dateDir) {
            if (substr($dateDir, 0, 1) == '.') {
                continue;
            }
            if ($dateDir != '2018-12-27') {
                continue;
            }
            $this->_dateDir = $dateDir;
            $datePath = $this->_rootPath . $dateDir . '/';
            if (is_dir($datePath)) {
                $this->parseDate($datePath);
            }
        }
    }

    private function parseDate($datePath) {
        $dir0 = $datePath . '0/';
        $dir1 = $datePath . '1/';
        if (is_dir($dir0)) {
            $this->_0Or1 = '0';
            $this->parseCodeDir($dir0);
        }
        if (is_dir($dir1)) {
            $this->_0Or1 = '1';
            $this->parseCodeDir($dir1);
        }
    }

    private function parseCodeDir($path) {
        $codeDirs = scandir($path);
        foreach ($codeDirs as $codeDir) {
            if (substr($codeDir, 0, 1)) {
                continue;
            }
            $this->_codeDir = $codeDir;
            $codePath = $path . $codeDir . '/';
            if (!is_dir($codePath)) {
                continue;
            }
            $files = scandir($codePath);
            foreach ($files as $file) {
                try {
                    if (substr($file, 0, 1) == '.') {
                        continue;
                    }
                    if ($file == 'content.png') {
                        continue;
                    }
                    $ext = substr($file, strlen($file)-3, 3);
                    if ($ext != 'png' && $ext != 'jpg') {
                        continue;
                    }
                    $filePath = $codePath . $file;
                    $outPath = $this->_outDir . '/' . $this->_dateDir .'/' . $this->_0Or1 . '/' . $this->_codeDir . '/' . $file;
                    if (file_exists($outPath)) {
                        continue;
                    }
                    if (!is_dir(dirname($outPath))) {
                        mkdir(dirname($outPath), 0777, true);
                    }
                    exec('cp '. $filePath . ' ' . $outPath);
                }catch (Exception $e) {
                    echo 'msg:' . $e->getMessage();
                }
            }
        }
    }
}

