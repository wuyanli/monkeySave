<?php
/**
 * Ecloud 异常类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Exception;

class EcloudException extends BaseException {
	
	/**
	 * 构造函数
	 * @param string $message
	 * @return void
	 */
	public function __construct() {
		$numargs = func_num_args();
		$args = func_get_args();
		if (1 == $numargs) {
			$message = $args[0];
		} else {
			$format = array_shift($args);
			$message = vsprintf($format, $args);
		}
		parent::__construct($message);
	}
}