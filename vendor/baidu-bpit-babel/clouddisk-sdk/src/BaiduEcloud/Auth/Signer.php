<?php
/**
 * 接口签名
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Auth;

use BaiduEcloud\Exception\EcloudException;

class Signer {
	
	/**
	 * 获取接口头签名
	 * @param array $options
	 * @return boolean|string
	 */
	public static function getHeaderSign($options) {
		if (empty($options['username']) || !is_string($options['username'])) {
			throw new EcloudException("username empty or username not is string");
		}
		if (empty($options['token']) || !is_string($options['token'])) {
			throw new EcloudException("token empty or token not is string");
		}
		$enstr = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $options['token'], $options['username'], MCRYPT_MODE_CBC, $options['token']);
		$enstr = base64_encode($enstr);
		return $enstr;
	}
}