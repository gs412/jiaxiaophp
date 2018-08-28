<? include __DIR__.'/../_/head.php'; ?>

	<div class="all_container clearfix">
		<div class="header">
			<h3>为“<?= $group->name ?>”组设置权限</h3>
		</div>
		<div class="content">
			<style scoped="scoped">
				.table2{width:600px !important;}
				.table2 tr td:first-child{width:30%;}
				.table2 tr td:nth-child(2){width:50%;}
			</style>
			<form method="post">
				<table class="table2 checkbox-wrapper" data-value="<?= $group->perm ?>">
					<tr>
						<th>权限</th>
						<th>视图</th>
						<th>授权（<label style="display:inline;"><input type="checkbox" id="check_all"><small>全选</small></label>）</th>
					</tr>
					<? foreach($controllers as $controller): ?>
						<tr>
							<td colspan="3" style="font-weight:bold;"><?= $controller->menu_name ? $controller->menu_name : $controller->perm_name ?></td>
						</tr>
						<? foreach($controller->methods as $method): ?>
							<tr>
								<td>　 <?= $method==end($controller->methods) ? '└' : '├'; ?>─ <?= $method->perm_name ?></td>
								<?
									$view = 'Admin/'.$controller->name.'#'.$method->name;
								?>
								<td><?= $view ?></td>
								<td>
									<input type="checkbox" name="perm[]" value="<?= $view ?>">
								</td>
							</tr>
						<? endforeach; ?>
					<? endforeach; ?>
					<tr>
						<td colspan="3" style="padding-top:10px; text-align:center; background:none; border:none;">
							<input type="submit" value="配 置">
							<input type="button" value="返 回" onclick="window.location.href='/Admin/Admin/grouplist';">
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>

	<script>
		$(document).ready(function () {
			$('input[type="checkbox"]#check_all').change(function () {
				var $this = $(this);
				if ($this.prop('checked')) {
					$('input[type="checkbox"][name="perm[]"]').prop('checked', true);
				} else {
					$('input[type="checkbox"][name="perm[]"]').prop('checked', false);
				}
			});
		});
	</script>

<? include __DIR__.'/../_/foot.php'; ?>