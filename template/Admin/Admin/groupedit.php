<? include __DIR__.'/../_/head.php'; ?>

	<div class="all_container clearfix">
		<div class="header">
			<h3>编辑权限组</h3>
		</div>
		<div class="content">
			<form method="post">
				<table class="table1">
					<tr>
						<td>权限组名称：</td>
						<td><input type="text" name="name" required="" value="<?= $group->name ?>"></td>
					</tr>
					<tr>
						<td>排序：</td>
						<td><input type="text" name="sort" style="width:50px;" value="<?= $group->sort ?>"></td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="submit" value="提 交">
							<input type="button" value="返 回" onclick="history.back();">
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>

<? include __DIR__.'/../_/foot.php'; ?>