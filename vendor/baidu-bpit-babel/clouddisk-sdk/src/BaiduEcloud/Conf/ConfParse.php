<?php
/**
 * Ecloud conf解析类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Conf;

class ConfParse {
	
	/**
	 * 解析conf配置文件(配置文件兼容odp)
	 * @param string $confPath
	 * @return array
	 */
	public static function load($confPath) {
		$config = array();
		$alllines = file($confPath);
		foreach ($alllines as $line) {
			preg_match("/^([^#]*?):(.*?)$/is", $line, $matchs);
			if (!empty($matchs[1])) {
				$key = trim($matchs[1]);
				$config[$key] = trim($matchs[2]);
			}
		}
		if (!defined('ECLOUD_LOG_PATH')) {
		    isset($config['logpath']) ? : $config['logpath'] = dirname(dirname(dirname(__DIR__))) . '/log';
		    echo 'logpath:' . $config['logpath'] . PHP_EOL;
			define('ECLOUD_LOG_PATH', $config['logpath']);
			echo 'log:' . ECLOUD_LOG_PATH . PHP_EOL;
		}
		return $config;
	}
}