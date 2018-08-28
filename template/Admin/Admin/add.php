<? include __DIR__.'/../_/head.php'; ?>

	<div class="all_container clearfix">
		<div class="header">
			<h3>添加管理员</h3>
		</div>
		<div class="content">
			<form method="post">
				<table class="table1">
					<tr>
						<td>账号：</td>
						<td><input type="text" name="username" required=""></td>
					</tr>
					<tr>
						<td>密码：</td>
						<td><input type="password" name="password" required=""></td>
					</tr>
					<tr>
						<td>姓名：</td>
						<td><input type="text" name="realname" required=""></td>
					</tr>
					<tr>
						<td>权限组：</td>
						<td>
							<select name="group_id">
								<option value="1">总管理员</option>
								<? foreach($groups as $group): ?>
									<option value="<?= $group->id ?>"><?= $group->name ?></option>
								<? endforeach; ?>
							</select>
						</td>
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