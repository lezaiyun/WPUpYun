<?php
require_once 'sdk/vendor/autoload.php';

use Upyun\Upyun;
use Upyun\Config;


class UpYunApi
{
	// 用于签名的公钥和私钥
	private $client;

	public function __construct($options) {
		// 初始化签权对象
		$serviceConfig = new Config($options['serviceName'], $options['operatorName'], $options['operatorPwd']);
		// $serviceConfig->setUploadType('BLOCK_PARALLEL');  // (实测有问题)使用并行式断点续传上传文件
		$this->client = new Upyun($serviceConfig);
	}

	public function Upload($key, $file) {
		$this->client->write($key, $file);  // 支持异步，但因为涉及异步回调地址，暂不使用异步。
	}

	public function Delete($keys) {
		$this->client->delete($keys, True);  // 删除成功返回 true，否则 false
	}

	public function hasExist($key) {
	    // 和七牛插件一样，这里引用的地方路径会以/开头，但至少七牛那边路径是把开头的/去掉的。这里待定。
		return $this->client->has( $key );
	}
}
