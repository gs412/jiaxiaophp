<? include __DIR__.'/../_/head.php'; ?>

	<link rel="stylesheet" href="/static/admin/css/left.css"/>
	<script src="/static/admin/js/left.js"></script>

	<div class="menu clearfix">

		<? foreach($controllers as $controller): ?>
			<h4><?= $controller->menu_name ?></h4>
			<ul class="ul2">
				<? foreach($controller->methods as $method): ?>
					<li><a href="/Admin/<?= str_replace('Controller', '', $controller->name) ?>/<?= $method->name ?>" target="_right"><?= $method->menu_name ?></a></li>
				<? endforeach; ?>
			</ul>
		<? endforeach; ?>

	</div>

<? include __DIR__.'/../_/foot.php'; ?>