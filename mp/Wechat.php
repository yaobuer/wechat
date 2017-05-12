<?php
namespace sunnnnn\wechat\mp;

use yii\base\Component;
use sunnnnn\wechat\Error;
/**
* @use: 微信公众平台接口开发
* @date: 2017-5-11 上午10:34:26
* @author: sunnnnn [www.sunnnnn.com] [mrsunnnnn@qq.com]
 */
class Wechat extends Component{
	
	public $config;
	
	const STATE = 'wechat';
	const WX_URL_CODE = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=%s#wechat_redirect';
	const WX_URL_OPENID = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
	const WX_URL_USERINFO = 'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN';
	const WX_URL_ACCESSTOKEN = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
	const WX_URL_TEMPLATEMESSAGE = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s';
	const WX_URL_JSAPITICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=%s';
	const WX_URL_QRTICKET = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=%s';
	const WX_URL_QRCODE = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=%s';
	const WX_URL_MENU = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s';
	
	const PAY_URL_BONUS = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack'; //红包接口

	public function __construct($config = []){
		if(!empty($config)){
			$this->config = $config;
		}
		
		if(empty($this->config)){
			Error::showError('Please set the configuration file <param: wechat>!');
		}
		
		if(!$this->isWeChatBrowser()){
			Error::showError('please open this in wechat app !');
		}
	}
	
	public function setConfig($config){
		$this->config = $config;
	}
	
	/**
	 * 显示错误信息
	* @date: 2016-12-28 下午12:31:08
	* @author: sunnnnn
	* @param unknown $msg
	 */
	public function error($msg){
		Error::showError($msg);
	}
	
	/**
	 * 设置配置数据
	* @date: 2016-12-28 上午9:58:47
	* @author: sunnnnn
	* @param array $cfg
	 */
	public function setCfg($cfg = []){
		$this->config = empty($cfg) || !is_array($cfg) ? [] : $cfg;
	}
	
	/**
	 * 获取配置数据
	* @date: 2016-12-28 上午9:59:01
	* @author: sunnnnn
	* @return Ambigous <string, unknown, multitype:, array>
	 */
	public function getCfg(){
		return $this->config;
	}
	
	/**
	 * 判断是否微信浏览器
	* @date: 2016-12-28 上午9:59:17
	* @author: sunnnnn
	* @return boolean
	 */
	public function isWeChatBrowser(){
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
			return true;
		}
		return false;
	}
	
	/**
	 * 获取url转为微信可获取code的url
	* @date: 2016-12-28 上午9:59:37
	* @author: sunnnnn
	* @param unknown $url
	* @param string $userinfo
	* @return string
	 */
	public function getCodeUrl($url, $userinfo = false){
		$scope = $userinfo ? 'snsapi_userinfo' : 'snsapi_base';
		return sprintf(self::WX_URL_CODE, $this->config['app_id'], urlencode($url), $scope, self::STATE);
	}
	
	/**
	 * 获取openid
	* @date: 2016-12-28 上午10:04:06
	* @author: sunnnnn
	* @param unknown $code 微信服务器传回的code参数(与getCodeUrl配合使用)
	* @param string $getAll
	* @return mixed
	 */
	public function getOpenid($code, $getAll = false){
		$url = sprintf(self::WX_URL_OPENID, $this->config['app_id'], $this->config['app_secret'], $code);
		$resultJson = file_get_contents($url);
		$result = json_decode($resultJson, true);
		if($getAll){
			return $result;
		}
		return $result['openid'];
	}
	
	/**
	 * 获取用户信息
	* @date: 2016-12-28 上午10:04:47
	* @author: sunnnnn
	* @param unknown $access_token
	* @param unknown $openid
	* @return mixed
	 */
	public function getUserinfo($access_token, $openid){
		$url = sprintf(self::WX_URL_USERINFO, $access_token, $openid);
		$resultJson = file_get_contents($url);
		$result = json_decode($resultJson, true);
		return $result;
	}
	
	/**
	 * 获取AccessToken（通用型）
	* @date: 2016-12-28 上午10:05:03
	* @author: sunnnnn
	* @param string $getAll false为只获取一个AccessToken，true获取整个返回的数组，包含过期时间等
	* @return Ambigous <unknown, mixed>
	 */
	public function getAccessToken($getAll = false){
		$url = sprintf(self::WX_URL_ACCESSTOKEN, $this->config['app_id'], $this->config['app_secret']);
		$result = $this->curlGet($url);
		return $getAll ? $result : $result['access_token'];
	}
	
	/**
	 * 发送模板消息
	* @date: 2016-12-28 上午10:05:25
	* @author: sunnnnn
	* @param unknown $data 模板消息数据
	* @param string $accessToken 可以使用已保存的accessToken， 为空则自动重新获取
	* @return mixed
	 */
	public function sendTemplateMessage($data, $accessToken = ''){
		$access_token = !empty($accessToken) ? $accessToken : $this->getAccessToken();
		$url = sprintf(self::WX_URL_TEMPLATEMESSAGE, $access_token);
		$result = $this->curlPost($url, json_encode($data));
		return json_decode($result,true);
	}
	
	/**
	 * 获取JsSdk配置数据
	* @date: 2016-12-28 上午10:05:49
	* @author: sunnnnn
	* @param string $ticket
	* @param string $url
	* @param string $noncestr
	* @return Ambigous <string, \sunnnnn\wechat\Ambigous, unknown, mixed>
	 */
	public function getJsSdkConfig($ticket = '', $url = '', $noncestr = 'wechat'){
		$data['jsapi_ticket'] = empty($ticket) ? $this->getJsapiTicket() : $ticket;
		$data['noncestr'] = $noncestr;
		$data['timestamp'] = strval(time());
		$data['url'] = empty($url) ? 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] : $url;
		$data['sign'] = $this->getSign($data);
		$data['appid'] = $this->config['app_id'];
		return $data;
	}
	
	/**
	 * 获取JsSdk Ticket
	* @date: 2016-12-28 上午10:06:35
	* @author: sunnnnn
	* @param string $accessToken 可以使用已保存的accessToken， 为空则自动重新获取
	* @param string $getAll false为只获取一个Ticket，true获取整个返回的数组，包含过期时间等
	* @return Ambigous <unknown, mixed>
	 */
	public function getJsapiTicket($accessToken = '', $getAll = false){
		$access_token = !empty($accessToken) ? $accessToken : $this->getAccessToken();
		$url = sprintf(self::WX_URL_JSAPITICKET, $access_token);
		$ticket = $this->curlGet($url);
		return $getAll ? $ticket : $ticket['ticket'];
	}
	
	/**
	 * 创建临时二维码ticket
	* @date: 2016-12-28 上午10:09:31
	* @author: sunnnnn
	* @param unknown $scene_id
	* @param string $accessToken
	* @param number $expire_seconds
	* @return mixed
	 */
	public function getQRLimitTicket($scene_id, $accessToken = '', $expire_seconds = 604800){
		$access_token = !empty($accessToken) ? $accessToken : $this->getAccessToken();
		$url = sprintf(self::WX_URL_QRTICKET, $access_token);
		$data = [
			'expire_seconds' => $expire_seconds,
			'action_name'    => 'QR_SCENE',
			'action_info'    => [
				'scene' => ['scene_id' => $scene_id]
			],
		];
		$result = $this->curlPost($url, json_encode($data));
		return json_decode($result,true);
	}
	
	/**
	 * 创建永久二维码ticket
	* @date: 2016-12-28 上午10:11:10
	* @author: sunnnnn
	* @param unknown $scene_str
	* @param string $accessToken
	* @return mixed
	 */
	public function getQRTicket($scene_str, $accessToken = ''){
		$access_token = !empty($accessToken) ? $accessToken : $this->getAccessToken();
		$url = sprintf(self::WX_URL_QRTICKET, $access_token);
		$data = [
				'action_name'    => 'QR_LIMIT_SCENE',
				'action_info'    => [
						'scene' => ['scene_str' => $scene_str]
				],
		];
		$result = $this->curlPost($url, json_encode($data));
		return json_decode($result,true);
	}
	
	/**
	 * 通过ticket换取二维码
	* @date: 2016-12-28 上午10:12:05
	* @author: sunnnnn
	* @param unknown $ticket
	* @return mixed
	 */
	public function getQRCode($ticket){
		$url = sprintf(self::WX_URL_QRCODE, urlencode($ticket));
		return $this->curlGet($url);
	}
	
	/**
	 * 通过ticket换取二维码URL
	* @date: 2016-12-28 上午10:12:25
	* @author: sunnnnn
	* @param unknown $ticket
	* @return string
	 */
	public function getQRCodeUrl($ticket){
		return sprintf(self::WX_URL_QRCODE, urlencode($ticket));
	}
	
	/**
	 * 自定义菜单
	* @date: 2016-12-28 上午10:12:42
	* @author: sunnnnn
	* @param unknown $menuData
	* @param string $accessToken
	* @return boolean
	 */
	public function setMenu($menuData, $accessToken = ''){
		$access_token = !empty($accessToken) ? $accessToken : $this->getAccessToken();
		$url = sprintf(self::WX_URL_QRTICKET, $access_token);
		$json_menu = json_encode($menuData);
		$json_menu = preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $menuData);
		$result = $this->curlPost($url, $json_menu);
		if($result->errcode === 0){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 生成签名
	* @date: 2016-12-28 上午10:13:11
	* @author: sunnnnn
	* @param unknown $data
	* @return string
	 */
	private function getSign($data){
		if(empty($data) || !is_array($data)){
			return '';
		}
		ksort($data);
		$str = '';
		foreach($data as $key => $val){
			if(empty($str)){
				$str = strtolower($key).'='.$val;
			}else{
				$str .= '&'.strtolower($key).'='.$val;
			}
		}
		$sign = sha1($str);
		return $sign;
	}
	
	/**
	 * curl get 
	* @date: 2016-12-28 上午10:13:50
	* @author: sunnnnn
	* @param unknown $url
	* @return mixed
	 */
	private function curlGet($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
		$jsoninfo = json_decode($output, true);
		return $jsoninfo;
	}
	
	/**
	 * curl post
	* @date: 2016-12-28 上午10:14:02
	* @author: sunnnnn
	* @param unknown $url
	* @param unknown $data
	* @return string|mixed
	 */
	private function curlPost($url, $data){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$tmpInfo = curl_exec($curl);
		if (curl_errno($curl)) {
			return '';
		}
		curl_close($curl);
		return $tmpInfo;
	}
	
	
/**
 * --------------------------------------------------------------------------------------------------
 * --------------------------------------------------------------------------------------------------
 */
	/**
	 * 设置证书路径
	* @date: 2016-12-28 上午10:14:27
	* @author: sunnnnn
	* @param unknown $path
	 */
	public function setCertPath($path){
		$this->config['cert_path'] = $path;
	}
	
	/**
	 * 发放红包
	* @date: 2016-12-28 上午10:14:38
	* @author: sunnnnn
	* @param unknown $openid openid
	* @param unknown $money 红包金额
	* @param unknown $number 红包人数
	* @param string $send_name 发送方名称
	* @param string $wishing 祝福语
	* @param string $act_name 活动名称
	* @param string $remark 备注
	* @return boolean
	 */
	public function sendBonus($openid, $money, $number, $send_name = '', $wishing = '', $act_name = '', $remark = ''){
		$data['nonce_str'] = strval(time());
		$data['mch_billno'] = $this->config['mch_id'].date('YmdHis').rand(1000, 9999);
		$data['mch_id'] = $this->config['mch_id'];
		$data['wxappid'] = $this->config['app_id'];
		$data['send_name'] = $send_name;
		$data['re_openid'] = $openid;
		$data['total_amount'] = $money;
		$data['total_num'] = $number;
		$data['wishing'] = $wishing;
		$data['client_ip'] = $this->ip();
		$data['act_name'] = $act_name;
		$data['remark'] = $remark;
		
		$data['sign'] = $this->getPaySign($data);
		
		$xml = simplexml_load_string('<xml/>');
		$this->createXml($data, $xml);
		$vars = $xml->saveXML();
		
		$result = $this->curlPostSsl(self::PAY_URL_BONUS, $vars);
		$result = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
		if($result->return_code == 'SUCCESS'){
			return true;
		}else{
			return $result->return_msg;
		}
	}
	
	/**
	 * 数组转XML
	* @date: 2016-12-28 上午10:15:53
	* @author: sunnnnn
	* @param unknown $data
	* @param unknown $xml
	 */
	private function createXml($data, $xml){
		foreach($data as $k => $v) {
			if(is_array($v)) {
				$x = $xml->addChild($k);
				$this->createXml($v, $x);
			}else $xml->addChild($k, $v);
		}
	}
	
	/**
	 * 生成支付签名
	* @date: 2016-12-28 上午10:16:36
	* @author: sunnnnn
	* @param unknown $data
	* @return string
	 */
	private function getPaySign($data){
		if(empty($data) || !is_array($data)){
			return '';
		}
		ksort($data);
		$str = '';
		foreach($data as $key => $val){
			if(!empty($val)){
				if(empty($str)){
					$str = strtolower($key).'='.$val;
				}else{
					$str .= '&'.strtolower($key).'='.$val;
				}
			}
		}
		$str .= '&key='.$this->config['mch_key'];
		$sign = strtoupper(md5($str));
		return $sign;
	}
	
	/**
	 * 带证书请求数据
	* @date: 2016-12-28 上午10:17:09
	* @author: sunnnnn
	* @param unknown $url
	* @param unknown $vars
	* @param number $second
	* @param unknown $aHeader
	* @return mixed|boolean
	 */
	private function curlPostSsl($url, $vars, $second = 30, $aHeader = []){
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
	
		//以下两种方式需选择一种
	
		//第一种方法，cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
// 		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT, $this->config['cert_path'].'/apiclient_cert.pem');
		//默认格式为PEM，可以注释
// 		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY, $this->config['cert_path'].'/apiclient_key.pem');
	
		//第二种方式，两个文件合成一个.pem文件
		// 	curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
	
		if( count($aHeader) >= 1 ){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}
	
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
		$data = curl_exec($ch);
		if($data){
			curl_close($ch);
			return $data;
		}else {
			$error = curl_errno($ch);
			echo "call faild, errorCode:$error\n";
			curl_close($ch);
			return false;
		}
	}
	
	/**
	 * 获取真实IP
	* @date: 2016-12-28 上午10:18:19
	* @author: sunnnnn
	* @return NULL|boolean|string|unknown
	 */
	public function ip(){
		static $ip = null;
		if (!is_null($ip)) {
			return $ip;
		}
	
		// 定义函数: 过滤掉空串、127.0.0.1、和非法字符串
		$filter_func = function($_ip) {
			$_ip = trim($_ip);
			return !empty($_ip) && $_ip != '127.0.0.1' && preg_match('/^[\d.]{7,15}$/', $_ip);
		};
	
		// 定义函数: 取第一个非内网IP
		$get_func = function($_ip_list) {
			if (count($_ip_list) > 0) {
				foreach ($_ip_list as $_ip) {
					if (!preg_match('/^(10\.0\.10\.|192\.168\.)[\d.]+$/', $_ip)) {
						return $_ip;
					}
				}
	
				return $_ip_list[0];
			}
			return '0.0.0.0';
		};
	
		$ip_list = [$_SERVER['REMOTE_ADDR']];
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$proxy_ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$proxy_ip_list = array_reverse($proxy_ip_list);
			$ip_list = array_merge($ip_list, $proxy_ip_list);
		}
		$ip_list = array_values(array_filter($ip_list, $filter_func));
		$ip = $get_func($ip_list);
		return $ip;
	}
	
}