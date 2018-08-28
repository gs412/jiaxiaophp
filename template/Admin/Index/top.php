<? include __DIR__.'/../_/head.php'; ?>

	<style>
		img{border:none;}
		.ul1{list-style:none; line-height:80px;}
		.ul1 li{display:inline-block; margin-left:15px;}
	</style>

	<script>
		$(document).ready(function () {
			$('#refresh_sys').click(function () {
				window.parent.frames['_right'].location = window.parent.frames['_right'].location.toString();
				return false;
			});
		});
	</script>

	<div style="height:83px; width:100%; overflow:hidden; background:url(/static/_img/ht/2head.jpg)">
		<div style="float:left;">
			<a href="/Admin/Index/" target="_parent"><img src="/static/_img/ht/logo2.png" alt=""></a>
		</div>
		<div style="float:right; width:480px;">
			<ul class="ul1">
				<li>
					<img src="/static/_img/ht/3user.png">
					<span style="color:#fff;">欢迎<a href="###" style="color:#fff;"><u><?= $this->admin->username ?></u></a>使用本系统！</span>
				</li>
				<li>
					<a href="/Admin/Index/right" title="系统首页" target="_right"><img src="/static/_img/ht/pencil_48.png"></a>
				</li>
				<li>
					<a href="password_reset.php" title="修改密码" target="_right"><img src="/static/_img/ht/paper_content_pencil_48.png"></a>
				</li>
				<li>
					<a href="###" id="refresh_sys" title="刷新系统"><img src="/static/_img/ht/clock_48.png"></a>
				</li>
				<li>
					<a href="/Admin/Index/logout" target="_parent" title="退出系统"><img src="/static/_img/ht/comment_48.png"></a>
				</li>
			</ul>
		</div>
	</div>


<? include __DIR__.'/../_/foot.php'; ?>