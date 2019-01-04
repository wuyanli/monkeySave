<?php
/**
 * 接口请求封装类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Http;

use BaiduEcloud\Auth\Signer;
use GuzzleHttp\json_decode;
use BaiduEcloud\Util\EcloudUtils;
use BaiduEcloud\Define\Constants;
use BaiduEcloud\Log\LogInstance;

class HttpClient {
	
	private $_options = array();
	private $_logInstance = null;
	
	/**
	 * 构造函数
	 * @param string $createUserName
	 * @return void
	 */
	public function __construct($options = array()) {
		$this->_options = $options;
		$this->_logInstance = LogInstance::getInstance();
	}
	
	/**
	 * 发送Ecloud请求
	 * @param string $method
	 * @param HttpMethod $httpMethod
	 * @param array $params
	 * @param array $header
	 * @return boolean|array
	 */
	public function sendEcloudRequest($method, $httpMethod = HttpMethod::GET, $params = array(), $header = array()) {
		$guzzleClient = new \GuzzleHttp\Client();
		$encUserName = Signer::getHeaderSign($this->_options);
		$url = $this->_getUrl($method);
		if (!isset($params['from'])) {
			$params['from'] = $this->_options['from'];
		}
		$header[HttpHeaders::ECLOUD_OPENAPI_CUNAME] = $encUserName;
		$options = $this->_buildOptions($httpMethod, $params, $header);
		//--文件上传处理请求
		if ($httpMethod == HttpMethod::FORMDATA) {
			$httpMethod = HttpMethod::POST;
		}
		$num = 0;
		$retry = 1;
		$sucess = false;
		if (isset($this->_options['retry'])) {
			$retry = $retry + intval($this->_options['retry']);
		}
		do {
			++$num;
			$res = $guzzleClient->request($httpMethod, $url, $options);
			$httpCode = $res->getStatusCode();
			$contentType = $res->getHeaders()['Content-Type'][0];
			if ($httpCode == HttpCode::CODE_200 || $httpCode == HttpCode::CODE_206) {
				$sucess = true;
				break;
			}
			usleep(300000);
		} while ($num < $retry);
		if (!$sucess) {
			$this->_logInstance->debug("{$url} httpcode {$httpCode}");
			return false;
		}
		$body = $res->getBody()->getContents();
		if ($contentType == HttpContentType::JSON) {
			$body = json_decode($body, true);
		}
		return array(
			'headers' => $res->getHeaders(),
			'body' => $body,
		);
	}
	
	/**
	 * 发送请求
	 * @param string $url
	 * @param HttpMethod $httpMethod
	 * @param array $params
	 * @param array $header
	 * @return boolean|array
	 */
	public function sendRequest($url, $httpMethod = HttpMethod::GET, $params = array(), $header = array()) {
		$guzzleClient = new \GuzzleHttp\Client();
		$url = $this->_getUrl($url);
		$options = $this->_buildOptions($httpMethod, $params, $header);
		$num = 0;
		$retry = 1;
		$sucess = false;
		if (isset($this->_options['retry'])) {
			$retry = $retry + intval($this->_options['retry']);
		}
		do {
			++$num;
			$res = $guzzleClient->request($httpMethod, $url, $options);
			$httpCode = $res->getStatusCode();
			$contentType = $res->getHeaders()['Content-Type'][0];
			if ($httpCode == HttpCode::CODE_200 || $httpCode == HttpCode::CODE_206) {
				$sucess = true;
				break;
			}
		} while ($num < $retry);
		if (!$sucess) {
			$this->_logInstance->debug("{$url} httpcode {$httpCode}");
			return false;
		}
		$body = $res->getBody()->getContents();
		if ($contentType == HttpContentType::JSON) {
			$body = json_decode($body, true);
		}
		return array(
			'headers' => $res->getHeaders(),
			'body' => $body,
		);
	}
	
	/**
	 * 组合guzzle options参数
	 * @param string $httpMethod
	 * @param array $params
	 * @param array $header
	 * @return array
	 */
	private function _buildOptions($httpMethod, $params, $header) {
		$options = array();
		switch ($httpMethod) {
			case HttpMethod::POST:
				$options = array(
					'form_params' => $params,
					'headers' => $header,
				);
				break;
			case HttpMethod::FORMDATA:
				$tmpArr = array();
				foreach ($params as $k => $v) {
					$tmpArr[] = array('name' => $k, 'contents' => $v);
				}
				$options = array(
					'multipart' => $tmpArr,
					'headers' => $header,
				);
				break;
			default:
				$options = array(
					'query' => $params,
					'headers' => $header,
				);
				break;
		}
		//--https不认证
		$options['verify'] = false;
		//--请求超时时间配置
		$options['connect_timeout'] = 3;
		$options['read_timeout'] = 3;
		$options['timeout'] = 15;
		if (isset($this->_options['connect_timeout'])) {
			//--请求连接超时时间
			$options['connect_timeout'] = EcloudUtils::milli2second($this->_options['connect_timeout']);
		}
		if (isset($this->_options['read_timeout'])) {
			//--请求读取超时时间
			$options['read_timeout'] = EcloudUtils::milli2second($this->_options['read_timeout']);
		}
		if (isset($this->_options['write_timeout'])) {
			//--请求总超时时间(即写)
			$options['timeout'] = EcloudUtils::milli2second($this->_options['write_timeout']);
		}
		return $options;
	}
	
	/**
	 * 获取url地址
	 * @param string $url or $method
	 * @return string
	 */
	private function _getUrl($method) {
		if (!filter_var($method, FILTER_VALIDATE_URL)) {
			return sprintf("%s%s", $this->_options['endpoint'], Constants::getEcloudUrl($method));
		} else {
			return $method;
		}
	}
}