微信借口开发，集成主流框架（thinkphp5、yii2）
=================
#配置
tp5 中config.php中放入配置信息：
'wechat' => [
	'app_id' => '',  //app id
	'app_secret' => '', //app secret
	'mch_id' => '', //商户号
	'mch_key' => '', //商户 key
	'cert_path' => '', //证书路径
],
'wxpay' => [
	'app_id' => '',  //app id
	'app_secret' => '', //app secret
	'mch_id' => '', //商户号
	'mch_key' => '', //商户 key
	'cert_path' => '', //证书路径
	'key_path' => '', //证书路径
],

controller：
$wechat = new WechatTp();

#扫码支付
$wxPay = new WxPayTp();
$src = $wxPay->native([
	body=>商品描述, 
	attach=>附加数据, 
	outTradeOn=>商户订单号, 
	fee=>费用, 
	tag=>商品标记, 
	notify=>回掉地址, 
	productId=>商品ID
]);

echo "<img src='{$src}'>";

#js支付
$jsApiParameters = $wxPay->jsApi([
	body=>商品描述, 
	attach=>附加数据, 
	outTradeOn=>商户订单号, 
	fee=>费用, 
	tag=>商品标记, 
	notify=>回掉地址, 
	productId=>商品ID
]);

jsApiParameters为生成的用于调用微信支付接口的配置数据，用法查阅微信支付文档


```

yii2配置：
'components' => [
	'wechat' => [
		'class' => 'sunnnnn\wechat\mp\Wechat',
		'config' => [
			'app_id' => '',  //app id
			'app_secret' => '', //app secret
			'mch_id' => '', //商户号
			'mch_key' => '', //商户 key
			'cert_path' => '', //证书路径
		]
	],
	'wxpay' => [
		'class' => 'sunnnnn\wechat\pay\WxPay',
		'config' => [
			'app_id' => '',  //app id
			'app_secret' => '', //app secret
			'mch_id' => '', //商户号
			'mch_key' => '', //商户 key
			'cert_path' => '', //证书路径
			'key_path' => '', //证书路径
		]
	],
],

controller：

#扫码支付
$src = Yii::$app->wxpay->native([
	body=>商品描述, 
	attach=>附加数据, 
	outTradeOn=>商户订单号, 
	fee=>费用, 
	tag=>商品标记, 
	notify=>回掉地址, 
	productId=>商品ID
]);

echo "<img src='{$src}'>";

#js支付
$jsApiParameters = Yii::$app->wxpay->jsApi([
	body=>商品描述, 
	attach=>附加数据, 
	outTradeOn=>商户订单号, 
	fee=>费用, 
	tag=>商品标记, 
	notify=>回掉地址, 
	productId=>商品ID
]);

jsApiParameters为生成的用于调用微信支付接口的配置数据，用法查阅微信支付文档