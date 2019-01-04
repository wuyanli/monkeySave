<?php
/**
 * 异常基类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Exception;

class BaseException extends \Exception {
	
	/**
	 * 构造函数
	 * @param string $message
	 * @return void
	 */
	public function __construct($message) {
		parent::__construct($message);
	}
}