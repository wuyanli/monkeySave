<?php
/***************************************************************************
 *
 * Copyright (c) 2013 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/


ini_set('memory_limit', -1);
//Bd_Init::init();
//Bd_LayerProxy::init(Bd_Conf::getConf('/layerproxy/'));

$oriPhotoPath = isset($argv[1]) ? $argv[1] : NULL;
if ($oriPhotoPath === NULL) {
    echo '请输入原始图片路径' . PHP_EOL;
    die();
}

$hp = new HiPhoto($oriPhotoPath);
$hp->main();

class HiPhoto {

    private $_oriPhotoPath = null;

    private $_codeDir = null;

    private $_randDir = null;

    private $_conn = null;

    public function __construct($oriPhotoPath){
        if (substr($oriPhotoPath, strlen($oriPhotoPath)-1, 1) != '/') {
            $oriPhotoPath .= '/';
        }
        $this->_oriPhotoPath = $oriPhotoPath;
        $this->mysqlInit();
    }

    public function main() {
        if (!is_dir($this->_oriPhotoPath)) {
            echo $this->_oriPhotoPath . '文件夹不存在' . PHP_EOL;
            return false;
        }
        $this->parsePath();
    }

    private function parsePath() {
        $codeDirs = $this->getSubFiles($this->_oriPhotoPath);
        if (empty($codeDirs)) {
            echo $this->_oriPhotoPath . '为空' . PHP_EOL;
            return false;
        }

        foreach ($codeDirs as $codeDir) {
            $this->_codeDir = $codeDir;
            $codeDirPath = $this->_oriPhotoPath . $codeDir . '/';

            $dir0 = $codeDirPath . '0/';
            $dir1 = $codeDirPath . '1/';
            if (is_dir($dir0)) {
                $this->parseNumDir($dir0);
            }

            if (is_dir($dir1)) {
                $this->parseNumDir($dir1);
            }
        }
    }

    private function getSubFiles($dirPath) {
        if (!is_dir($dirPath)) {
            echo $dirPath . '不是文件夹' . PHP_EOL;
            return false;
        }
        $subFiles = array();
        $scanFiles = scandir($dirPath);
        foreach ($scanFiles as $subFile) {
            if (substr($subFile, 0, 1) == '.') {
                continue;
            }
            $subFiles[] = $subFile;
        }
        return $subFiles;
    }

    private function parseNumDir($numDir) {
        $randDirs = $this->getSubFiles($numDir);
        foreach ($randDirs as $randDir) {
            $this->_randDir = $randDir;
            $randDirPath = $numDir . $randDir . '/';
            $this->uploadHiPhoto($randDirPath);
        }
    }

    private function uploadHiPhoto($dirPath) {
        if (!is_dir($dirPath)) {
            echo $dirPath . '不是文件夹' . PHP_EOL;
            return false;
        }
        $files = $this->getSubFiles($dirPath);
        foreach ($files as $file) {
            $ext = substr($file, strlen($file)-3, 3);
            if (($ext != 'png' && $ext != 'jpg') || $file == 'content.png') {
                continue;
            }
            $filePath = $dirPath . $file;
            $imgCont = file_get_contents($filePath);

            list($oriId,) = explode('.', $file);
            $fileMd5 = md5($imgCont);

            echo 'imgid:' . '...' . PHP_EOL;
            echo 'oriId:' . $oriId . PHP_EOL;
            echo 'filemd5:' . $fileMd5 . PHP_EOL;
            echo 'picUrl:' . '...' . PHP_EOL;
            echo 'codeDir:' . $this->_codeDir . PHP_EOL;
            echo 'randDir:' . $this->_randDir . PHP_EOL;
            echo '=====' . PHP_EOL;

//            echo 'contLength:' . strlen($imgCont) . PHP_EOL;
//            $imgId = Wenku_Util_Image::upload($imgCont);
//            echo 'imgId:' . $imgId . PHP_EOL;
//            if (NULL === $imgId) {
//                $this->log('上传' . $filePath . '错误');
//                continue;
//            }
//            $picUrl = Wenku_Util_Image::picId2UrlBig($imgId);
//            echo 'picUrl:' . $picUrl . PHP_EOL;


            $imgId = '123456';
            $picUrl = 'http://fdjsaofjds';
            $ret = $this->insertData($imgId, $oriId, $fileMd5, $picUrl, $this->_codeDir, $this->_randDir);

            var_dump($ret);
            die();


        }
    }

    private function mysqlInit() {
//        $mysql_server_name_r="10.64.73.122";
        $mysql_username_r="doc_wr";
        $mysql_pwd_r="doc_wr";
        $mysql_database_r="ns_doc_wuxian";
        $mysql_port_r="3306";

        $mysql_server_name_r="127.0.0.1";


        $this->_conn=mysqli_connect($mysql_server_name_r,$mysql_username_r,$mysql_pwd_r,$mysql_database_r,$mysql_port_r);
        if (!$this->_conn)
        {
            die("连接错误: " . mysqli_connect_error());
        }
    }

    private function insertData($photoId, $oriId, $md5, $picUrl, $codeDir, $randDir) {
        $database_table = 'bigque_hi_photo';
        $ctime = time();
        $sql = "INSERT INTO `{$database_table}` ( `photo_id`, `ori_id` ,`md5`, `url`, `code_dir`,`rand_dir`,`ctime`) VALUES ('{$photoId}','{$oriId}','{$md5}','{$picUrl}','{$codeDir}','{$randDir}','{$ctime}');";

        try {
            $result=mysqli_query($this->_conn,$sql);
        }catch (Exception $e) {
            echo $e->getMessage();
            sleep(0.5);
            $result=mysqli_query($this->_conn,$sql);
        }
        return $result;
    }

    private function log($msg) {

    }
}