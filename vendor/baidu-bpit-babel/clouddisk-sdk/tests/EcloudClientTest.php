<?php
/**
 * ecloud单元测试类
 *
 * @author chenhongwei01 <chenhongwei01@baidu.com>
 *
 */
declare(strict_types=1);

include 'vendor/autoload.php';

use BaiduEcloud\Services\EcloudClient;
use BaiduEcloud\Conf\ConfParse;
use PHPUnit\Framework\TestCase;

final class EcloudClientTest extends TestCase {
	
	/**
	 * publicFileUpload单测
	 * @param void
	 * @return void
	 */
	public function testPublicFileUpload()
	{
		$options = ConfParse::load(dirname(__DIR__) . '/conf/ecloud.conf');
		$ecloudClient = new EcloudClient($options);
		$filePath = dirname(__DIR__) . "/data/c_1.pdf";
		$res = $ecloudClient->publicFileUpload($filePath);
		$this->assertArrayHasKey('fid', $res);
	}
	
	/**
	 * privateFileUpload单测
	 * @param void
	 * @return void
	 */
	public function testPrivateFileUpload()
	{
		$options = ConfParse::load(dirname(__DIR__) . '/conf/ecloud.conf');
		$ecloudClient = new EcloudClient($options);
		$filePath = dirname(__DIR__) . "/data/c_1.pdf";
		$res = $ecloudClient->privateFileUpload($filePath);
		$this->assertArrayHasKey('fid', $res);
	}
	
	/**
	 * encryptFileUpload单测
	 * @param void
	 * @return void
	 */
	public function testEncryptFileUpload()
	{
		$options = ConfParse::load(dirname(__DIR__) . '/conf/ecloud.conf');
		$ecloudClient = new EcloudClient($options);
		$filePath = dirname(__DIR__) . "/data/c_1.pdf";
		$res = $ecloudClient->encryptFileUpload($filePath);
		$this->assertArrayHasKey('fid', $res);
	}
	
	/**
	 * privateFileDownload单测
	 * @param void
	 * @return void
	 */
	public function testPrivateFileDownload()
	{
		$options = ConfParse::load(dirname(__DIR__) . '/conf/ecloud.conf');
		$ecloudClient = new EcloudClient($options);
		$filePath = dirname(__DIR__) . "/tests";
		$res = $ecloudClient->privateFileDownload(2017753, $filePath);
		$this->assertEquals(true, $res);
	}
	
	/**
	 * encryptFileDownload单测
	 * @param void
	 * @return void
	 */
	public function testEncryptFileDownload()
	{
		$options = ConfParse::load(dirname(__DIR__) . '/conf/ecloud.conf');
		$ecloudClient = new EcloudClient($options);
		$filePath = dirname(__DIR__) . "/tests";
		$res = $ecloudClient->encryptFileDownload(2017756, $filePath);
		$this->assertEquals(true, $res);
	}
	
	/**
	 * getFileList单测
	 * @param void
	 * @return void
	 */
	public function testGetFileList()
	{
		$options = ConfParse::load(dirname(__DIR__) . '/conf/ecloud.conf');
		$ecloudClient = new EcloudClient($options);
		$res = $ecloudClient->getFileList();
		$this->assertGreaterThan(0, $res['body']['result']['total']);
	}
}