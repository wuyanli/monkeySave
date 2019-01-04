<?php
/**
 * 接口请求方式
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
namespace BaiduEcloud\Http;

class HttpMethod
{
	const GET = 'GET'; //--GET请求
	const PUT = 'PUT'; //--PUT请求
	const POST = 'POST'; //--POST请求
	const DELETE = 'DELETE'; //--DELETE请求
	const HEAD = 'HEAD'; //--HEAD请求
	const OPTIONS = 'OPTIONS'; //--OPTIONS请求
	const FORMDATA = 'multipart'; //--chunk上传文件请求
}