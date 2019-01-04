<?php
/**
 * 接口请求content-type
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Http;

class HttpContentType
{
	const JSON = 'application/json; charset=utf-8'; //--请求响应json格式
	const FORMDATA = 'multipart/form-data'; //--POST分段上传文件
	const FORMURLENCODED = 'application/x-www-form-urlencoded'; //--POST表单请求
}