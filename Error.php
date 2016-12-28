<?php
namespace sunnnnn\wechat;
/**
* @use: 错误类
* @date: 2016-12-28 下午12:43:25
* @author: sunnnnn
*/
class Error{
	
	/**
	 * 显示错误信息
	* @date: 2016-12-28 下午12:42:48
	* @author: sunnnnn
	* @param unknown $msg
	 */
    public static function showError($msg){
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
<title>出错啦</title>
<link rel="stylesheet" href="//cdn.bootcss.com/weui/1.1.0/style/weui.min.css"/>
</head>
<body>
<div class="page">
    <div class="weui-msg">
        <div class="weui-msg__icon-area"><i class="weui-icon-warn weui-icon_msg"></i></div>
        <div class="weui-msg__text-area">
            <h2 class="weui-msg__title">操作失败</h2>
            <p class="weui-msg__desc"><?= $msg; ?></p>
        </div>
		<div class="weui-msg__opr-area">
            <p class="weui-btn-area">
                <a href="javascript:history.back();" class="weui-btn weui-btn_primary">确定</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
<?php
	exit();
    }
}
