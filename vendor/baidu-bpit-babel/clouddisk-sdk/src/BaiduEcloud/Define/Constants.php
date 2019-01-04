<?php
/**
 * 常量
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Define;

class Constants {
	
	const ECLOUD_FILEUPLOAD_CHUNKSIZE = 5242880; //--企业云盘文件分段上传chunk 5*1024*1024大小
	
	/**
	 * 获取url地址
	 * @param string $method
	 * @return string
	 */
	public static function getEcloudUrl($method) {
		$urls = array(
			'fileUpload' => '/v1/openapi/fileupload', //--小文件上传
			'preFileCreate' => '/v1/openapi/prefilecreate', //--大文件开始上传
			'preFileUpload' => '/v1/openapi/prefileupload', //--大文件分段断点续传上传中
			'preFileCommit' => '/v1/openapi/prefilecommit', //--大文件上传完成
			'getDownLoadsFile' => '/v1/openapi/getdownloadsfile', //--获取下载文件地址
			'preCreate' => '/v1/openapi/precreate', //--文件加密开始上传
			'preUpload' => '/v1/openapi/preupload', //--文件加密分段断点续传上传中
			'preCommit' => '/v1/openapi/precommit', //--文件加密上传完成
			'rangeDownloadEncFile' => '/v1/openapi/rangedownloadencfile', //--range下载加密文件
			'getFileList' => '/v1/openapi/getfilelist', //--获取文件列表
		);
		return $urls[$method];
	}
}