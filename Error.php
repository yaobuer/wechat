<?php
namespace sunnnnn\wechat;

class Error{
    /**
     * showError
     * 显示错误信息
     */
    public static function showError($msg){
?>
		<html>
		<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
		<title>Error</title>
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/zui/1.5.0/css/zui.min.css">
		</head>
		<body>
			<div class="container">
				<div class="row" style="margin-top:50px;">
					<div class="col-md-12">
						<div class="alert alert-danger-inverse with-icon">
						  	<i class="icon-frown"></i>
						  	<div class="content">
						  		<h4>出错啦</h4>
								<p><?= $msg; ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</body>
		</html>
<?php
	exit();
    }
}
