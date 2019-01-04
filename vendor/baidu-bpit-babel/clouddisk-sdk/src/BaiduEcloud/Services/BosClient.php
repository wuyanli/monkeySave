<?php
/**
 * Bos client类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Services;

use BaiduEcloud\Http\HttpClient;
use BaiduEcloud\Http\HttpMethod;
use BaiduEcloud\Exception\EcloudException;

class BosClient {
	
	/**
	 * range下载文件
	 * @param string $url
	 * @param bigint $start
	 * @param bigint $end
	 * @return stream
	 */
	public static function rangDownloadFile($url, $startSize, $endSize) {
		if (0 > $startSize || 0 > $endSize || $startSize > $endSize) {
			throw new EcloudException("startSize less than 0 or endSize less than 0 or startSize greater than endSize");
		}
		$header = array(
			'Range' => sprintf("bytes=%s-%s", $startSize, $endSize),
		);
		$httpClient = new HttpClient();
		$res = $httpClient->sendRequest($url, HttpMethod::GET, array(), $header);
		return $res;
	}
}