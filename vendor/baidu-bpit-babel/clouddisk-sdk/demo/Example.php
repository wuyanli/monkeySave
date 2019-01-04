<?php
/**
 * demo类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
include 'vendor/autoload.php';

use BaiduEcloud\Services\EcloudClient;
use BaiduEcloud\Conf\ConfParse;

class Example {
	
	private $_client = null;
	
	/**
	 * 构造函数
	 * @param void
	 * @return void
	 */
	public function __construct() {
		$options = ConfParse::load(dirname(__DIR__) . '/conf/ecloud.conf');
		$this->_client = new EcloudClient($options);
	}
	
	/**
	 * 上传公开文件(外网可访问文件)
	 * @param void
	 * @return void
	 */
	public function publicFileUpload() {
		$filePath = dirname(__DIR__) . "/data/c_1.pdf";
		$res = $this->_client->publicFileUpload($filePath);
		return $res;
	}
	
	/**
	 * 上传文件(未加密)
	 * @param void
	 * @return void
	 */
	public function privateFileUpload() {
		$filePath = dirname(__DIR__) . "/data/c_1.pdf";
		$res = $this->_client->privateFileUpload($filePath);
		return $res;
	}
	
	/**
	 * 上传文件(加密)
	 * @param void
	 * @return void
	 */
	public function encryptFileUpload() {
		$filePath = dirname(__DIR__) . "/data/c_1.pdf";
		$res = $this->_client->encryptFileUpload($filePath);
		return $res;
	}
	
	/**
	 * 下载内部未加密文件
	 * @param void
	 * @return void
	 */
	public function privateFileDownload() {
		$filePath = dirname(__DIR__) . "/tests";
		$res = $this->_client->privateFileDownload(2017753, $filePath);
		return $res;
	}
	
	/**
	 * 下载内部加密的文件
	 * @param void
	 * @return void
	 */
	public function encryptFileDownload() {
		$filePath = dirname(__DIR__) . "/tests";
		$res = $this->_client->encryptFileDownload(2017756, $filePath);
		return $res;
	}
	
	/**
	 * 获取文件列表
	 * @param void
	 * @return void
	 */
	public function getFileList() {
		$res = $this->_client->getFileList();
		return $res;
	}
	
	/**
	 * 获取文件列表(可以逐级获取)
	 * @param void
	 * @return void
	 */
	public function getFileListByFid() {
		$res = $this->_client->getFileList(2017745);
		return $res;
	}
}
$obj = new Example();
$res = $obj->publicFileUpload();
$res = $obj->privateFileUpload();
$res = $obj->encryptFileUpload();
$res = $obj->privateFileDownload();
$res = $obj->encryptFileDownload();
$res = $obj->getFileList();
$res = $obj->getFileListByFid();
var_dump($res);
