<?php
namespace sunnnnn\wechat\pay;

use sunnnnn\wechat\pay\lib\WxPayConfig;
use sunnnnn\wechat\pay\lib\WxPayApi;

class WxPay{
	
	public $config;
	
	/**
	 * 构造函数
	* @date: 2017-5-11 上午11:00:23
	* @author: sunnnnn [www.sunnnnn.com] [mrsunnnnn@qq.com]
	* @param unknown $config
	* 
	* 
	* 
	* 
	* 
	* 
	* 
	* 
	 */
	public function __construct($config = []){
		if(empty($config)){
			if(function_exists('config')){
				$this->config = config('wechat');
			}
		}else{
			$this->config = $config;
		}
	
		if(empty($this->config)){
			Error::showError('Please set the configuration file <param: wechat>!');
		}
	
		if(!$this->isWeChatBrowser()){
			Error::showError('please open this in wechat app !');
		}
	}
}