<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>后台管理系统</title>
<script src="/static/_js/jquery.min.js"></script>
<script type="text/javascript" src="/static/bootstrap/js/bootstrap.min.js"></script>
<link href="/static/admin/css/login-bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="/static/admin/css/login.css" rel="stylesheet" type="text/css">
<style type="text/css">
body {
	background-color: #666666;
	background-image: url("/static/admin/img/bg_3.jpg");
	background-repeat: no-repeat;
	background-position: center top;
	background-attachment: fixed;
	background-clip: border-box;
	background-size: cover;
	background-origin: padding-box;
	width: 100%;
	padding: 0;
}
</style>
<script>
	if(top.location != self.location){top.location = self.location;}    //防止页面被框架包含
</script>
<?
$msg = get_session('msg');
del_session('msg');
?>
<? if ($msg): ?>
	<script>alert("<?= $msg ?>");</script>
<? endif; ?>
</head>
<body>
<div class="bg-dot"></div>
<div class="login-layout">
	<div class="top">
		<h5> XXX系统<em></em> </h5>
		<h2>平台管理中心</h2>
	</div>
	<div class="box">
		<form method="post">
			<span>
			<label>帐号</label>
			<input name="username" id="username" type="text" class="input-text"/>
			</span> <span>
			<label>密码</label>
			<input name="password" id="password" class="input-password" type="password" />
			</span> <span>
			<input name="nchash" type="hidden" value="476d52ed" />
			<button class="btn input-button" type="submit">登录</button>
			<!--<button class="btn input-button" type="button" data-toggle="modal" data-target="#myModal">找回密码</button>-->
			</span>
		</form>
	</div>
</div>
<div class="bottom">
	<h5>Powered by 通用软件公司</h5>
	<h6> © 2016-<?= date("Y") ?> <a href="" target="_blank">通用科技</a> </h6>
</div>
</body>
</html>
