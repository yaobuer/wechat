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