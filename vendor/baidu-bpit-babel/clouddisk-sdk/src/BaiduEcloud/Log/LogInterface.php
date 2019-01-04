<?php
/**
 * log 接口类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Log;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LogInterface {
	
	private $_log = null;
	
	/**
	 * 构造函数
	 * @param void
	 * @return void
	 */
	public function __construct($logPath) {
		$this->_log = new Logger('ecloud');
		$this->_log->pushHandler(new StreamHandler($logPath, Logger::DEBUG));
	}
	
	/**
	 * __call魔术方法
	 * @param string $method
	 * @param array $parameter
	 * @return boolean
	 */
	public function __call($method, $parameter) {
		call_user_func(array($this->_log, $method), $parameter[0]);
		return true;
	}
}