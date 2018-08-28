<? include __DIR__.'/../_/head.php'; ?>

	<div class="all_container clearfix">
		<div class="header">
			<h3>菜单列表</h3>
		</div>
		<div class="content">
			<style scoped="scoped">
				.table2{width:700px !important;}
				.table2 tr td:first-child{width:25%;}
				.table2 tr td:nth-child(2){width:35%;}
				.table2 tr td:nth-child(3){width:60px;}
			</style>
			<table class="table2">
				<tr>
					<th>菜单</th>
					<th>视图</th>
					<th>排序</th>
					<th>操作</th>
				</tr>
				<? foreach($menus as $menu): ?>
					<tr>
						<td><?= $menu->name ?></td>
						<td></td>
						<td><?= $menu->sort ?></td>
						<td>
							<a href="/Admin/Menu/edit/<?= $menu->id ?>">编辑</a>
							<? if($menu->children): ?>
								<a href="javascript:alert('有子菜单，无法删除');" style="color:#ccc;">删除</a>
							<? else: ?>
								<a href="/Admin/Menu/delete/<?= $menu->id ?>" data-method="post" data-confirm="确定删除？">删除</a>
							<? endif; ?>
						</td>
					</tr>
					<? foreach($menu->children as $m): ?>
						<tr>
							<td><?= $m->name ?></td>
							<td>Admin/<?= $m->controller ?>#<?= $m->action ?></td>
							<td><?= $m->sort ?></td>
							<td>
								<a href="/Admin/Menu/edit/<?= $m->id ?>">编辑</a>
								<a href="/Admin/Menu/delete/<?= $m->id ?>" data-method="post" data-confirm="确定删除？">删除</a>
							</td>
						</tr>
					<? endforeach; ?>
				<? endforeach; ?>
			</table>
		</div>
	</div>

<? include __DIR__.'/../_/foot.php'; ?>