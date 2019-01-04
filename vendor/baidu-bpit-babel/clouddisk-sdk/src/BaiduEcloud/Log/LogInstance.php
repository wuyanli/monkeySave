<?php
/**
 * log 实例类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Log;

use BaiduEcloud\Exception\EcloudException;

class LogInstance {
	
	private static $_instance = null;
	
	/**
	 * 单例模式
	 * @param void
	 * @return \BaiduEcloud\Log\LogInterface
	 */
	public static function getInstance() {
		if (self::$_instance instanceof LogInterface) {
			return self::$_instance;
		}
		$logPath = '';
		if (defined('ECLOUD_LOG_PATH')) {
			$logPath = ECLOUD_LOG_PATH;
		}
		if (empty($logPath)) {
			throw new EcloudException("logpath value empty");
		}
		if (!is_dir($logPath)) {
			throw new EcloudException("%s not is dir", $logPath);
		}
		$logPath = sprintf("%s/%s", $logPath, 'ecloud');
		if (!is_dir($logPath)) {
			mkdir($logPath);
		}
		$logPath = sprintf("%s/%s", $logPath, "info_" . date('Ymd') . '.log');
		self::$_instance = new LogInterface($logPath);
		return self::$_instance;
	}
	
	/**
	 * __call魔术方法
	 * @param string $method
	 * @param array $parameter
	 * @return boolean
	 */
	public function __call($method, $parameter) {
		if (empty(self::$_instance)) {
			self::getInstance();
		}
		if (method_exists(self::$_instance, $method)) {
			call_user_func(array(self::$_instance, $method), $parameter[0]);
		}
		return true;
	}
}