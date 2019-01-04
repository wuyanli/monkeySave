<?php
/**
 * 企业云盘接口封装类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Services;

use BaiduEcloud\Define\Constants;

use GuzzleHttp\Psr7;
use BaiduEcloud\Http\HttpClient;
use BaiduEcloud\Http\HttpMethod;
use BaiduEcloud\Util\EcloudUtils;
use BaiduEcloud\Log\LogInstance;

class EcloudInterface {
	
	private $_options = array();
	private $_logInstance = null;
	
	/**
	 * 构造函数
	 * @param array $options
	 * array('username' => 'chenhongwei01', 'from' => '', 'token' => '')
	 * @return void
	 */
	public function __construct($options = array()) {
		$this->_options = $options;
		$this->_logInstance = LogInstance::getInstance();
	}
	
	/**
	 * 公开性上传文件(内外网可访问)
	 * @param string $fileName
	 * @param bigint $fileSize
	 * @param boolean $isdwx
	 * @param string $filePath
	 * @return array
	 */
	public function fileUpload($fileName, $fileSize, $isdwx, $filePath) {
		$httpClient = new HttpClient($this->_options);
		$params = array(
			'filename' => $fileName,
			'size' => $fileSize,
			'append_filename' => $isdwx,
			'file' => fopen($filePath, "r"),
		);
		$res = $httpClient->sendEcloudRequest(__FUNCTION__, HttpMethod::FORMDATA, $params);
		if (!empty($res) && 0 == $res['body']['errno']) {
			return $res['body']['result'];
		}
		$this->_logInstance->debug(__FUNCTION__ . " response " . var_export($res));
		return false;
	}
	
	/**
	 * 开始上传文件(不加密)
	 * @param void
	 * @return array
	 */
	public function preFileCreate() {
		$params = array();
		$httpClient = new HttpClient($this->_options);
		$res = $httpClient->sendEcloudRequest(__FUNCTION__, HttpMethod::POST, $params);
		if (!empty($res) && 0 == $res['body']['errno']) {
			return $res['body']['result'];
		}
		$this->_logInstance->debug(__FUNCTION__ . " response " . var_export($res));
		return $res;
	}
	
	/**
	 * 上传文件中(不加密)
	 * @param string $bucket
	 * @param string $object
	 * @param string $uploadId
	 * @param string $fileName
	 * @param int $fileSize
	 * @param string $filePath
	 * @return boolean
	 */
	public function preFileUpload($bucket, $object, $uploadId, $fileName, $fileSize, $filePath) {
		$params = array(
			'bucket' => $bucket,
			'object' => $object,
			'upload_id' => $uploadId,
			'filename' => $fileName,
			'filesize' => $fileSize,
		);
		$chunk = 0;
		$startSize = 0;
		$original = Psr7\stream_for(fopen($filePath, "r"));
		do {
			$stream = new Psr7\LimitStream($original, Constants::ECLOUD_FILEUPLOAD_CHUNKSIZE, $startSize);
			$params['chunk'] = $chunk;
			$params['file'] = $stream;
			$httpClient = new HttpClient($this->_options);
			$res = $httpClient->sendEcloudRequest(__FUNCTION__, HttpMethod::FORMDATA, $params);
			if (empty($res) || 0 != $res['body']['errno']) {
				$this->_logInstance->debug(__FUNCTION__ . " response " . var_export($res));
				return false;
			}
			++$chunk;
			$startSize += Constants::ECLOUD_FILEUPLOAD_CHUNKSIZE;
			$stream = null;
		} while ($fileSize > $startSize);
		return true;
	}
	
	/**
	 * 上传文件完成(不加密)
	 * @param string $bucket
	 * @param string $object
	 * @param string $uploadId
	 * @param string $fileName
	 * @param int $fileSize
	 * @return boolean|array
	 */
	public function preFileCommit($bucket, $object, $uploadId, $fileName, $fileSize) {
		$params = array(
			'bucket' => $bucket,
			'object' => $object,
			'upload_id' => $uploadId,
			'filename' => $fileName,
			'filesize' => $fileSize,
		);
		$httpClient = new HttpClient($this->_options);
		$res = $httpClient->sendEcloudRequest(__FUNCTION__, HttpMethod::POST, $params);
		if (!empty($res) && 0 == $res['body']['errno']) {
			return $res['body']['result'];
		}
		$this->_logInstance->debug(__FUNCTION__ . " response " . var_export($res));
		return false;
	}
	
	/**
	 * 开始上传文件(加密)
	 * @param void
	 * @return array
	 */
	public function preCreate() {
		$params = array();
		$httpClient = new HttpClient($this->_options);
		$res = $httpClient->sendEcloudRequest(__FUNCTION__, HttpMethod::POST, $params);
		if (!empty($res) && 0 == $res['body']['errno']) {
			return $res['body']['result'];
		}
		$this->_logInstance->debug(__FUNCTION__ . " response " . var_export($res));
		return $res;
	}
	
	/**
	 * 上传文件中(加密)
	 * @param string $bucket
	 * @param string $object
	 * @param string $uploadId
	 * @param string $fileName
	 * @param int $fileSize
	 * @param string $filePath
	 * @return boolean
	 */
	public function preUpload($bucket, $object, $uploadId, $fileName, $fileSize, $filePath) {
		$params = array(
			'bucket' => $bucket,
			'object' => $object,
			'upload_id' => $uploadId,
			'filename' => $fileName,
			'filesize' => $fileSize,
		);
		$chunk = 0;
		$startSize = 0;
		$original = Psr7\stream_for(fopen($filePath, "r"));
		do {
			$stream = new Psr7\LimitStream($original, Constants::ECLOUD_FILEUPLOAD_CHUNKSIZE, $startSize);
			$params['chunk'] = $chunk;
			$params['file'] = $stream;
			$httpClient = new HttpClient($this->_options);
			$res = $httpClient->sendEcloudRequest(__FUNCTION__, HttpMethod::FORMDATA, $params);
			if (empty($res) || 0 != $res['body']['errno']) {
				$this->_logInstance->debug(__FUNCTION__ . " response " . var_export($res));
				return false;
			}
			++$chunk;
			$startSize += Constants::ECLOUD_FILEUPLOAD_CHUNKSIZE;
			$stream = null;
		} while ($fileSize > $startSize);
		return true;
	}
	
	/**
	 * 上传文件完成(不加密)
	 * @param string $bucket
	 * @param string $object
	 * @param string $uploadId
	 * @param string $fileName
	 * @param int $fileSize
	 * @return boolean|array
	 */
	public function preCommit($bucket, $object, $uploadId, $fileName, $fileSize) {
		$params = array(
			'bucket' => $bucket,
			'object' => $object,
			'upload_id' => $uploadId,
			'filename' => $fileName,
			'filesize' => $fileSize,
		);
		$httpClient = new HttpClient($this->_options);
		$res = $httpClient->sendEcloudRequest(__FUNCTION__, HttpMethod::POST, $params);
		if (!empty($res) && 0 == $res['body']['errno']) {
			return $res['body']['result'];
		}
		$this->_logInstance->debug(__FUNCTION__ . " response " . var_export($res));
		return false;
	}
	
	/**
	 * 获取下载文件地址(不加密文件)
	 * @param bigint $fileId
	 * @return boolean
	 */
	public function getDownLoadsFile($fileId) {
		$params = array(
			'fileid' => $fileId,
			'from' => $this->_options['from'],
			'_t' => EcloudUtils::unixstamp(),
			'_u' => EcloudUtils::hash256Value($this->_options['username']),
		);
		$params['_s'] = EcloudUtils::hash256($params, $this->_options['token']);
		$httpClient = new HttpClient($this->_options);
		$res = $httpClient->sendEcloudRequest(__FUNCTION__, HttpMethod::GET, $params);
		if (empty($res)) {
			$this->_logInstance->debug(__FUNCTION__ . " response " . var_export($res));
			return false;
		}
		return $res;
	}
	
	/**
	 * 下载文件(加密文件)
	 * @param bigint $fileId
	 * @param string $storageFilePath
	 * @param bigint $startSize
	 * @param bigint $endSize
	 * @return boolean
	 */
	public function rangeDownloadEncFile($fileId, $storageFilePath, $startSize, $endSize) {
		$params = array(
			'fid' => $fileId,
			'_t' => time(),
		);
		$header = array(
			'Range' => sprintf("bytes=%s-%s", $startSize, $endSize),
		);
		ksort($params);
		$params['_s'] = EcloudUtils::hash256($params, $this->_options['token']);
		$httpClient = new HttpClient($this->_options);
		$res = $httpClient->sendEcloudRequest(__FUNCTION__, HttpMethod::GET, $params, $header);
		if (empty($res)) {
			$this->_logInstance->debug(__FUNCTION__ . " response " . var_export($res));
			return false;
		}
		return $res;
	}
	
	/**
	 * 获取文件列表
	 * @param bigint $parentFileid
	 * @return boolean
	 */
	public function getFileList($parentFileid = 0, $page = 1, $pageSize = 50) {
		$params = array(
			'page' => $page,
			'pageSize' => $pageSize,
		);
		if (!empty($parentFileid)) {
			$params['pfid'] = $parentFileid;
		}
		$params['_s'] = EcloudUtils::hash256($params, $this->_options['token']);
		$httpClient = new HttpClient($this->_options);
		$res = $httpClient->sendEcloudRequest(__FUNCTION__, HttpMethod::GET, $params);
		if (empty($res)) {
			$this->_logInstance->debug(__FUNCTION__ . " response " . var_export($res));
			return false;
		}
		return $res;
	}
}