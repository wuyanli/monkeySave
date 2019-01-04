<?php
/**
 * Created by PhpStorm.
 * User: luanjinyang
 * Date: 2018/12/18
 * Time: 下午7:33
 */

include 'vendor/autoload.php';

use BaiduEcloud\Services\EcloudClient;
use BaiduEcloud\Conf\ConfParse;

$dayFile = isset($argv[1]) ? $argv[1] : '-1 day';
echo $dayFile . PHP_EOL;
echo strtotime($dayFile) . PHP_EOL;
echo empty(strtotime(strval($dayFile))) . PHP_EOL;

if (empty(strtotime($dayFile))) {
    echo '时间格式错误' . PHP_EOL;
    echo <<<EOL
        usage :
        php    checkEcloudfm.php   2018-12-18

EOL;
    die();
}

$check = new CheckFm($dayFile);
$check->checkFmPng();

class CheckFm {

    /**
     * @var EcloudClient|null
     */
    private $_client = null;

    /**
     * @var null
     */
    private $_dayFile = null;

    /**
     * @var string
     */
    private $_fmOutFile = '';

    /**
     * 构造函数
     * @param void
     * @return void
     */
    public function __construct($dayFile) {
        $this->_dayFile = $dayFile;
        $options = ConfParse::load(dirname(__FILE__) . '/conf/ecloud.conf');
        $this->_client = new EcloudClient($options);
    }

    /**
     * 上传公开文件(外网可访问文件)
     * @param $filePath
     * @return bool
     * @throws \BaiduEcloud\Exception\EcloudException
     */
    public function publicFileUpload($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        $res = $this->_client->publicFileUpload($filePath);
        return $res;
    }

    /**
     * 上传文件(未加密)
     * @param $filePath
     * @return bool
     * @throws \BaiduEcloud\Exception\EcloudException
     */
    public function privateFileUpload($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        $res = $this->_client->privateFileUpload($filePath);
        return $res;
    }

    /**
     * 上传文件(加密)
     * @param $filePath
     * @return bool
     * @throws \BaiduEcloud\Exception\EcloudException
     */
    public function encryptFileUpload($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        $res = $this->_client->encryptFileUpload($filePath);
        return $res;
    }

    /**
     * 下载内部未加密文件
     * @param void
     * @return void
     */
    public function privateFileDownload($fileId, $path = null) {
        if ($path == null) {
            $path = dirname(__DIR__) . '/download';
        }
        $res = $this->_client->privateFileDownload($fileId, $path);
        return $res;
    }

    /**
     * 下载内部加密的文件
     * @param void
     * @return void
     */
    public function encryptFileDownload($fileId, $path=null) {
        if ($path == null) {
            $path = dirname(__DIR__) . '/download';
        }
        $res = $this->_client->encryptFileDownload($fileId, $path);
        return $res;
    }

    /**
     * 获取文件列表
     * @param int $parentFileid
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getFileList($parentFileid = 0, $page = 1, $pageSize = 50) {
        $res = $this->_client->getFileList($parentFileid, $page, $pageSize);
        return $res;
    }

    /**
     * 获取文件列表(可以逐级获取)
     * @param void
     * @return void
     */
    public function getFileListByFid($fileId) {
        $res = $this->_client->getFileList($fileId);
        return $res;
    }

    public function checkFmPng() {
        $files = $this->getFileList();
        var_dump($files);
    }
}
