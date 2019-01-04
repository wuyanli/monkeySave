<?php
/**
 * 企业云盘公共类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Util;

use BaiduEcloud\Exception\EcloudException;

class EcloudUtils {
	
	/**
	 * 获取当然时间戳
	 * @param void
	 * @return number
	 */
	public static function unixstamp() {
		return time();
	}
	
	/**
	 * hash256计算
	 * @param array $params
	 * @param string $accessToken
	 * @return string
	 */
	public static function hash256($params, $accessToken) {
		if (empty($accessToken)) {
			throw new EcloudException("hash256 token empty");
		}
		ksort($params);
		return hash('sha256', http_build_query($params) . $accessToken);
	}
	
	/**
	 * hash256计算(单值)
	 * @param string $value
	 * @return string
	 */
	public static function hash256Value($value) {
		if (empty($value)) {
			throw new EcloudException("hash256Value value empty");
		}
		return hash('sha256', $value);
	}
	
	/**
	 * 毫秒转秒
	 * @param bigint $millisecond
	 * @return number
	 */
	public static function milli2second($millisecond) {
		$second = ceil($millisecond / 1000);
		return (empty($second) ? 1 : $second);
	}
}