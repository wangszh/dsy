<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html>
<html>
<head>
	<title>{:C('SITE_TITLE')} {:C('SITE_VERSION')}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>跳转提示</title>
	<style type="text/css">
		*{ padding: 0; margin: 0; }
		body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 12px; }
		.system-message{ width:80%;margin:0px auto; padding: 20px 5%; margin-top:10% !important; border:2px solid #ccc;border-radius:15px;color: #8a6d3b;
			background-color: #fcf8e3;
			border-color: #faebcc;}
		.system-message h1{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
		.system-message .jump{ padding-top: 10px;padding-left:70px;font-size:14px;}
		.system-message .jump a{ color: #333;font-size:14px;}
		.system-message .success,.system-message .error{height:70px; padding-left:70px; line-height: 70px; font-size: 24px; color:#}
		.system-message .detail{font-size: 12px; line-height: 20px; margin-top: 12px; display:none}
		.success{background:url('./Public/ok.png') 0 0 no-repeat;background-size: 50px;}
		.error{background:url('./Public/err.png') 0 0 no-repeat;background-size: 50px;}
	</style>
</head>
<body>
<div class="system-message">
	<?php if(isset($message)) {?>
	<p class="success"><?php echo($message); ?></p>
	<?php }else{?>
	<p class="error"><?php echo($error); ?></p>
	<?php }?>
	<p class="detail"></p>
	<p class="jump">
		页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间： <b id="wait"><?php echo($waitSecond); ?></b>
	</p>
</div>
<script type="text/javascript">
	(function(){
		var wait = document.getElementById('wait'),href = document.getElementById('href').href;
		var interval = setInterval(function(){
			var time = --wait.innerHTML;
			if(time <= 0) {
				location.href = href;
				clearInterval(interval);
			};
		}, 1000);
	})();
</script>
</body>
</html>
