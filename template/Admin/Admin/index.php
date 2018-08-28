<? include __DIR__.'/../_/head.php'; ?>

	<div class="all_container clearfix">
		<div class="header">
			<h3>管理员列表</h3>
		</div>
		<div class="content">
			<style scoped="scoped">
				.table2{width:700px !important;}
				.table2 tr td:first-child{width:23%;}
				.table2 tr td:nth-child(2){width:18%;}
			</style>
			<table class="table2">
				<tr>
					<th>账号</th>
					<th>姓名</th>
					<th>权限组</th>
					<th>加入时间</th>
					<th>操作</th>
				</tr>
				<? foreach($admins as $admin): ?>
					<tr>
						<td><?= $admin->username ?></td>
						<td><?= $admin->realname ?></td>
						<td><?= $admin->gname ?></td>
						<td><?= $admin->created_at ?></td>
						<td>
							<a href="/Admin/Admin/edit/<?= $admin->id ?>">编辑</a>
							<a href="/Admin/Admin/delete/<?= $admin->id ?>" data-method="post" data-confirm="确定删除？">删除</a>
						</td>
					</tr>
				<? endforeach; ?>
			</table>
			<a href="/Admin/Admin/add" class="btn btn-small btn-success" style="margin-top:20px;">+ 添加</a>
		</div>
	</div>

<? include __DIR__.'/../_/foot.php'; ?>