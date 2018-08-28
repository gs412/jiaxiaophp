<? include __DIR__.'/../_/head.php'; ?>

	<div class="all_container clearfix">
		<div class="header">
			<h3>权限组列表</h3>
		</div>
		<div class="content">
			<style scoped="scoped">
				.table2{width:500px !important;}
				.table2 tr td:first-child{width:43%;}
				.table2 tr td:nth-child(2){width:18%;}
			</style>
			<table class="table2">
				<tr>
					<th>组名</th>
					<th>排序</th>
					<th>操作</th>
				</tr>
				<tr>
					<td>总管理员</td>
					<td>1</td>
					<td><span style="color:#999; cursor:help;" title="系统内置权限组，拥有所有权限，禁止编辑禁止删除">系统内置</span></td>
				</tr>
				<? foreach($groups as $group): ?>
					<tr>
						<td><?= $group->name ?></td>
						<td><?= $group->sort ?></td>
						<td>
							<a href="/Admin/Admin/group_setperm/<?= $group->id ?>">分配权限</a>
							<a href="/Admin/Admin/groupedit/<?= $group->id ?>">编辑</a>
							<a href="/Admin/Admin/groupdelete/<?= $group->id ?>" data-method="post" data-confirm="确定删除？">删除</a>
						</td>
					</tr>
				<? endforeach; ?>
			</table>
			<a href="/Admin/Admin/groupadd" class="btn btn-small btn-success" style="margin-top:20px;">+ 添加</a>
		</div>
	</div>

<? include __DIR__.'/../_/foot.php'; ?>