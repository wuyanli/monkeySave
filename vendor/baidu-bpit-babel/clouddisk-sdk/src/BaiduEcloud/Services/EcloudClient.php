<?php
/**
 * 企业云盘client类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Services;

use BaiduEcloud\Exception\EcloudException;
use BaiduEcloud\Define\Constants;
class EcloudClient {
	
	private $_options = array();
	
	/**
	 * 构造函数
	 * @param array $options
	 * array('username' => 'chenhongwei01', 'from' => '', 'token' => '')
	 * @return void
	 */
	public function __construct($options = array()) {
		$this->_options = $options;
	}
	
	/**
	 * 公开性上传文件(内外网可访问)
	 * @param string $filePath
	 * @param string $fileName
	 * @param bool $isdwx
	 * @return boolean
	 */
	public function publicFileUpload($filePath, $fileName = '', $isdwx = true) {
		if (empty($filePath) || !is_file($filePath)) {
			throw new EcloudException("filePath is empty or %s not is file", $filePath);
		}
		if (!is_readable($filePath)) {
			throw new EcloudException("%s Unreadable", $filePath);
		}
		$fileSize = filesize($filePath);
		if (empty($fileSize)) {
			throw new EcloudException("%s filesize is 0", $filePath);
		}
		if (empty($fileName)) {
			$fileName = basename($filePath);
		}
		if (empty($fileName)) {
			throw new EcloudException("%s fileName is empty", $filePath);
		}
		$ecloudInterface = new EcloudInterface($this->_options);
		$res = $ecloudInterface->fileUpload($fileName, $fileSize, ($isdwx ? 0 : 1), $filePath);
		return $res;
	}
	
	/**
	 * 私密性文件上传(不加密)
	 * @param string $filePath
	 * @param string $fileName
	 * @return boolean
	 */
	public function privateFileUpload($filePath, $fileName = '') {
		if (empty($filePath) || !is_file($filePath)) {
			throw new EcloudException("filePath is empty or %s not is file", $filePath);
		}
		if (!is_readable($filePath)) {
			throw new EcloudException("%s Unreadable", $filePath);
		}
		$fileSize = filesize($filePath);
		if (empty($fileSize)) {
			throw new EcloudException("%s filesize is 0", $filePath);
		}
		if (empty($fileName)) {
			$fileName = basename($filePath);
		}
		if (empty($fileName)) {
			throw new EcloudException("%s fileName is empty", $filePath);
		}
		$ecloudInterface = new EcloudInterface($this->_options);
		$res = $ecloudInterface->preFileCreate();
		if (empty($res)) {
			throw new EcloudException("preFileCreate request error response false");
		}
		$bucket = $res['bucket'];
		$object = $res['object'];
		$uploadId = $res['upload_id'];
		$res = $ecloudInterface->preFileUpload($bucket, $object, $uploadId, $fileName, $fileSize, $filePath);
		if (empty($res)) {
			throw new EcloudException("preFileUpload request error response false");
		}
		$res = $ecloudInterface->preFileCommit($bucket, $object, $uploadId, $fileName, $fileSize);
		if (empty($res)) {
			throw new EcloudException("preFileCommit request error response false");
		}
		return $res;
	}
	
	/**
	 * 私密性文件上传(加密)
	 * @param string $filePath
	 * @param string $fileName
	 * @return boolean
	 */
	public function encryptFileUpload($filePath, $fileName = '') {
		if (empty($filePath) || !is_file($filePath)) {
			throw new EcloudException("filePath is empty or %s not is file", $filePath);
		}
		if (!is_readable($filePath)) {
			throw new EcloudException("%s Unreadable", $filePath);
		}
		$fileSize = filesize($filePath);
		if (empty($fileSize)) {
			throw new EcloudException("%s filesize is 0", $filePath);
		}
		if (empty($fileName)) {
			$fileName = basename($filePath);
		}
		if (empty($fileName)) {
			throw new EcloudException("%s fileName is empty", $filePath);
		}
		$ecloudInterface = new EcloudInterface($this->_options);
		$res = $ecloudInterface->preCreate();
		if (empty($res)) {
			throw new EcloudException("preCreate request error response false");
		}
		$bucket = $res['bucket'];
		$object = $res['object'];
		$uploadId = $res['upload_id'];
		$res = $ecloudInterface->preUpload($bucket, $object, $uploadId, $fileName, $fileSize, $filePath);
		if (empty($res)) {
			throw new EcloudException("preUpload request error response false");
		}
		$res = $ecloudInterface->preCommit($bucket, $object, $uploadId, $fileName, $fileSize);
		if (empty($res)) {
			throw new EcloudException("preCommit request error response false");
		}
		return $res;
	}
	
	/**
	 * 私密性文件下载(不加密的文件)
	 * @param bigint $fileId
	 * @param string $storageFilePath
	 * @return boolean
	 */
	public function privateFileDownload($fileId, $storageFilePath) {
		if (empty($fileId) || !is_numeric($fileId)) {
			throw new EcloudException("fileId is empty or fileId not is number current fileId is %s", $fileId);
		}
		if (!is_writable($storageFilePath)) {
			throw new EcloudException("%s can not write file", $storageFilePath);
		}
		$ecloudInterface = new EcloudInterface($this->_options);
		$res = $ecloudInterface->getDownLoadsFile($fileId);
		if (empty($res) || !isset($res['body']['errno']) || 0 != $res['body']['errno']) {
			throw new EcloudException("getDownLoadsFile response empty or errno not is 0 current errno is %s", $res['body']['errno']);
		}
		$fileSize = $res['body']['result']['files'][0]['fsize'];
		$fileName = $res['body']['result']['files'][0]['filename'];
		$downloadUrl = $res['body']['result']['files'][0]['downloadurl'];
		$storageFilePath = sprintf("%s/%s", $storageFilePath, $fileName);
		if (file_exists($storageFilePath) && !unlink($storageFilePath)) {
			throw new EcloudException("%s file can not delete", $storageFilePath);
		}
		$startSize = 0;
		$chunkSize = Constants::ECLOUD_FILEUPLOAD_CHUNKSIZE;
		do {
			$endSize = $startSize + $chunkSize;
			if ($endSize > $fileSize) {
				$endSize = $fileSize;
			}
			$res = BosClient::rangDownloadFile($downloadUrl, $startSize, $endSize);
			if (false === $res) {
				throw new EcloudException("chunk download file error startSize %s downloadUrl %s", $startSize, $downloadUrl);
			}
			@file_put_contents($storageFilePath, $res['body'], FILE_APPEND);
			$startSize = $endSize + 1;
		} while ($startSize < $fileSize);
		return true;
	}
	
	/**
	 * 私密性文件下载(加密的文件)
	 * @param bigint $fileId
	 * @param string $storageFilePath
	 * @return boolean
	 */
	public function encryptFileDownload($fileId, $storageFilePath) {
		if (empty($fileId) || !is_numeric($fileId)) {
			throw new EcloudException("fileId is empty or fileId not is number current fileId is %s", $fileId);
		}
		if (!is_writable($storageFilePath)) {
			throw new EcloudException("%s can not write file", $storageFilePath);
		}
		$ecloudInterface = new EcloudInterface($this->_options);
		$res = $ecloudInterface->getDownLoadsFile($fileId);
		if (empty($res) || !isset($res['body']['errno']) || 0 != $res['body']['errno']) {
			throw new EcloudException("getDownLoadsFile response empty or errno not is 0 current errno is %s", $res['body']['errno']);
		}
		$fileSize = $res['body']['result']['files'][0]['fsize'];
		$fileName = $res['body']['result']['files'][0]['filename'];
		$storageFilePath = sprintf("%s/%s", $storageFilePath, $fileName);
		if (file_exists($storageFilePath) && !unlink($storageFilePath)) {
			throw new EcloudException("%s file can not delete", $storageFilePath);
		}
		$startSize = 0;
		$chunkSize = Constants::ECLOUD_FILEUPLOAD_CHUNKSIZE;
		do {
			$endSize = $startSize + $chunkSize;
			if ($endSize > $fileSize) {
				$endSize = $fileSize;
			}
			$res = $ecloudInterface->rangeDownloadEncFile($fileId, $storageFilePath, $startSize, $endSize);
			if (false === $res) {
				throw new EcloudException("chunk download encfile error startSize %s fileId %s", $startSize, $fileId);
			}
			@file_put_contents($storageFilePath, $res['body'], FILE_APPEND);
			$startSize = $endSize + 1;
		} while ($startSize < $fileSize);
		return true;
	}
	
	/**
	 * 获取文件列表
	 * @param number $parentFileid
	 * @return array
	 */
	public function getFileList($parentFileid = 0, $page = 1, $pageSize = 50) {
		$ecloudInterface = new EcloudInterface($this->_options);
		$res = $ecloudInterface->getFileList($parentFileid, $page, $pageSize);
		return $res;
	}
}