<?php
/**
    把第一次生产出来的bdjson中的url进行替换图片的hiphoto地址
 */

//$argv[1]是挑选出的图片的根目录， $arvg[2]是第一次生产出的bdjson

$rootPath = isset($argv[1]) ? $argv[1] : NULL;
if ($rootPath === NULL) {
    die('请输入要替换的bdjson根目录');
}

if (!is_dir($rootPath)) {
    die($rootPath . '不是文件夹');
}

class BdJsonRep{

    private $_rootPath = NULL;

    private $_dateDir = NULL;

    private $_0Or1 = NULL;

    private $_randDir = NULL;

    private $_outDir = '/home/test/bdjson/';

    private $_conn = NULL;

    public function __construct($rootPath){
        if (substr($rootPath, strlen($rootPath)-1, 1) != '/') {
            $rootPath .= '/';
        }
        $this->_rootPath = $rootPath;
        $this->mysqlInit();
    }

    public function main() {
        $this->parseRoot();
    }


    private function parseRoot() {
        $dateDirs = scandir($this->_rootPath);
        foreach ($dateDirs as $dateDir) {
            if (substr($dateDir, 0, 1) == '.') {
                continue;
            }
            $this->_dateDir = $dateDir;
            $datePath = $this->_rootPath . $dateDir;
            if (!is_dir($datePath)) {
                continue;
            }
            $dir0 = $datePath . '/0/';
            $dir1 = $datePath . '/1/';

            if (is_dir($dir0)) {
                $this->_0Or1 = '0';
                $this->parseFile($dir0);
            }
            if (is_dir($dir1)) {
                $this->_0Or1 = '1';
                $this->parseFile($dir1);
            }

        }
    }

    private function parseFile($numDir) {
        $randDirs = scandir($numDir);
        foreach ($randDirs as $randDir) {
            if (substr($randDir, 0, 1) == '.') {
                continue;
            }
            $this->_randDir = $randDir;
            $randPath = $numDir . $randDir . '/';
            if (!is_dir($randPath)) {
                continue;
            }
            //获取一个文件夹里面所有图片的信息
            $ret = $this->getRandDirPicInfo($randDir);
            //格式化数据库中的url信息

die();
            $pics = scandir($randPath);
            $jsonPath = $randPath . $randDir . '.json';
            $jsonCont = file_get_contents($jsonPath);
            $urlPattern = '/\.\/([a-z0-9A-Z]+)\.(png|jpg)/';
            foreach ($pics as $pic) {
                if (substr($randDir, 0, 1) == '.') {
                    continue;
                }
                $ext = substr($pic, strlen($pic)-3, 3);
                if ($ext != 'png' && $ext != 'jpg') {
                    continue;
                }

                $imgUrl = '';
                $jsonCont = preg_replace($urlPattern, $imgUrl, $jsonCont);
                $outPath = $this->_outDir . $this->_dateDir . '/' . $this->_0Or1 . '/' . $this->_randDir . '/' . $this->_randDir . '.json';
            }
            if (!is_dir(dirname($outPath))) {
                mkdir(dirname($outPath), 0777, true);
            }
            file_put_contents($outPath, $jsonCont);
        }
    }

    private function getRandDirPicInfo($randDir) {
        $database_table = 'bigque_hi_photo';

        $sql = "SELECT `ori_id`, `rand_id`,`url` FROM {$database_table} WHERE `rand_dir` = '{$randDir}';";

        try {
            $result=mysqli_query($this->_conn,$sql);
        }catch (Exception $e) {
            Bd_Log::warning('read db fail, gonna sleep 1');
            echo $e->getMessage();
            sleep(0.5);
            $result=mysqli_query($this->_conn,$sql);
        }

        var_dump($result);

        return $result;
    }

    private function mysqlInit() {
        $mysql_server_name_r="10.64.73.122";
        $mysql_username_r="doc_wr";
        $mysql_pwd_r="doc_wr";
        $mysql_database_r="ns_doc_wuxian";
        $mysql_port_r="3306";

        $this->_conn=mysqli_connect($mysql_server_name_r,$mysql_username_r,$mysql_pwd_r,$mysql_database_r,$mysql_port_r);
        if (!$this->_conn)
        {
            die("连接错误: " . mysqli_connect_error());
        }
    }
}