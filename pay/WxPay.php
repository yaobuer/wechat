<?php
namespace sunnnnn\wechat\pay;

use Yii;
use sunnnnn\wechat\Error;
use sunnnnn\wechat\pay\lib\WxPayConfig;
use sunnnnn\wechat\pay\lib\WxPayApi;
use sunnnnn\wechat\pay\lib\JsApiPay;
use sunnnnn\wechat\pay\lib\WxPayUnifiedOrder;
use sunnnnn\wechat\pay\lib\WxPayBizPayUrl;
use sunnnnn\wechat\pay\lib\WxPayNotifyReply;

class WxPay{
	
	public $config;
	
	private $url_qrcode = 'http://paysdk.weixin.qq.com/example/qrcode.php?data=';
	
	/**
	 * 构造函数
	* @date: 2017-5-11 上午11:00:23
	* @author: sunnnnn [www.sunnnnn.com] [mrsunnnnn@qq.com]
	* @param unknown $config
	* [
	* 	app_id      => APPID
	* 	app_secret  => APPSECRET
	* 	mch_id      => MCHID
	* 	mch_key     => KEY
	* 	cert_path   => SSLCERT_PATH
	* 	key_path    => SSLKEY_PATH
	* ]
	 */
	public function __construct($config = []){
		if(empty($config)){
			if(function_exists('config')){
				$this->config = config('wechat');
			}
			if(!empty(Yii::$app->wxpay->config)){
				$this->config = Yii::$app->wxpay->config;
			}
		}else{
			$this->config = $config;
		}
	
		if(empty($this->config)){
			Error::showError('Please set the configuration file <param: wechat>!');
		}
		
		WxPayConfig::setConfig($this->config);
	}

	/**
	 * 扫码支付：模式一（需要公众平台填写回调地址）
	 * 流程：
	 * 1、组装包含支付信息的url，生成二维码
	 * 2、用户扫描二维码，进行支付
	 * 3、确定支付之后，微信服务器会回调预先配置的回调地址，在【微信开放平台-微信支付-支付配置】中进行配置
	 * 4、在接到回调通知之后，用户进行统一下单支付，并返回支付信息以完成支付
	 * 5、支付完成之后，微信服务器会通知支付成功
	 * 6、在支付成功通知中需要查单确认是否真正支付成功
	* @date: 2017-5-11 下午1:01:58
	* @author: sunnnnn [www.sunnnnn.com] [mrsunnnnn@qq.com]
	 */
	public function nativeMod1($productId, $onlyData = false){
		$url = $this->GetPrePayUrl($productId);
		return empty($onlyData) ? $this->url_qrcode.urlencode($url) : $url;
	}

	/**
	 * 扫码支付：模式二
	 * 流程：
	 * 1、调用统一下单，取得code_url，生成二维码
	 * 2、用户扫描二维码，进行支付
	 * 3、支付完成之后，微信服务器会通知支付成功
	 * 4、在支付成功通知中需要查单确认是否真正支付成功
	* @date: 2017-5-11 下午1:08:16
	* @author: sunnnnn [www.sunnnnn.com] [mrsunnnnn@qq.com]
	* @param array $order
	* [
	* 	body=>商品描述, attach=>附加数据, outTradeOn=>商户订单号, fee=>费用, tag=>商品标记, notify=>回掉地址, productId=>商品ID
	* ]
	* @param string $onlyData true 直接返回二维码链接，false 返回数据需自己生成二维码
	* @return Ambigous <string, \sunnnnn\wechat\pay\lib\成功时返回，其他抛异常>
	 */
	public function native($order, $onlyData = false){
		$input = new WxPayUnifiedOrder();
		$input->SetBody($order['body']);
		$input->SetAttach($order['attach']);
		$input->SetOut_trade_no($order['outTradeOn']);
		$input->SetTotal_fee($order['fee']);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag($order['tag']);
		$input->SetNotify_url($order['notify']);
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id($order['productId']);
		$result = WxPayApi::unifiedOrder($input);
		
		return empty($onlyData) ? $this->url_qrcode.urlencode($result["code_url"]) : $result["code_url"];
	}
	
	/**
	 * jsApi支付，需微信浏览器支持
	* @date: 2017-5-12 下午12:06:42
	* @author: sunnnnn [www.sunnnnn.com] [mrsunnnnn@qq.com]
	* @param unknown $order
	* *[
	* 	body=>商品描述, attach=>附加数据, outTradeOn=>商户订单号, fee=>费用, tag=>商品标记, notify=>回掉地址, productId=>商品ID
	* ]
	* @param string $openId
	* @return Ambigous <\sunnnnn\wechat\pay\lib\json数据，可直接填入js函数作为参数, string>
	 */
	public function jsApi($order, $openId = ''){
		$tools = new JsApiPay();
		$openId = empty($openId) ? $tools->GetOpenid() : $openId;
		
		$input = new WxPayUnifiedOrder();
		$input->SetBody($order['body']);
		$input->SetAttach($order['attach']);
		$input->SetOut_trade_no($order['outTradeOn']);
		$input->SetTotal_fee($order['fee']);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag($order['tag']);
		$input->SetNotify_url($order['notify']);
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = WxPayApi::unifiedOrder($input);
		$jsApiParameters = $tools->GetJsApiParameters($order);
		
		return $jsApiParameters;
	}	
	
	/**
	 * 回掉函数
	* @date: 2017-5-11 下午2:55:33
	* @author: sunnnnn [www.sunnnnn.com] [mrsunnnnn@qq.com]
	* @param unknown $callback [class, function]业务回调函数，成功返回true，失败返回错误msg
	* @param string $needSign
	 */
	public function notify($callback, $needSign = true){
		$reply = new WxPayNotifyReply();
		$msg = "OK";
		$result = WxpayApi::notify($callback, $msg);
		if($result == true){
			$reply->SetReturn_code("SUCCESS");
			$reply->SetReturn_msg("OK");
			$this->ReplyNotify($reply, $needSign);
		} else {
			$reply->SetReturn_code("FAIL");
			$reply->SetReturn_msg($msg == 'OK' ? $result : $msg);
			$this->ReplyNotify($reply, false);
		}
	}
	
	
	private function ReplyNotify($reply, $needSign){
		if($needSign == true && $reply->GetReturn_code() == "SUCCESS"){
			$reply->SetSign();
		}
		WxpayApi::replyNotify($reply->ToXml());
	}
	
	/**
	 *
	 * 生成扫描支付URL,模式一
	 * @param BizPayUrlInput $bizUrlInfo
	 */
	private function GetPrePayUrl($productId)
	{
		$biz = new WxPayBizPayUrl();
		$biz->SetProduct_id($productId);
		$values = WxPayApi::bizpayurl($biz);
		$url = "weixin://wxpay/bizpayurl?" . $this->ToUrlParams($values);
		return $url;
	}
	
	/**
	 *
	 * 参数数组转换为url参数
	 * @param array $urlObj
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v){
			$buff .= $k . "=" . $v . "&";
		}
	
		$buff = trim($buff, "&");
		return $buff;
	}
	
}