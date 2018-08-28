<? include __DIR__.'/../_/head.php'; ?>

	<div class="all_container clearfix">
		<div class="header">
			<h3>添加菜单</h3>
		</div>
		<div class="content">
			<form method="post">
				<table class="table1">
					<tr>
						<td>上级分类：</td>
						<td>
							<select name="parent_id" id="parent_id">
								<option value="0">作为一级分类</option>
								<? foreach($parents as $parent): ?>
									<option value="<?= $parent->id ?>"><?= $parent->name ?></option>
								<? endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td>菜单名称：</td>
						<td><input type="text" name="name" required=""></td>
					</tr>
					<tr style="display:none;">
						<td>控制器：</td>
						<td>
							<select name="controller" id="controller">
								<? foreach($controller_list as $controller): ?>
									<option value="<?= $controller->name ?>"><?= $controller->name ?></option>
								<? endforeach; ?>
							</select>
						</td>
					</tr>
					<tr style="display:none;">
						<td>视图：</td>
						<td>
							<select name="action" id="action"></select>
						</td>
					</tr>
					<tr>
						<td>排序：</td>
						<td><input type="text" name="sort" style="width:50px;" value="0"></td>
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

	<script>
		$(document).ready(function () {
			$('#parent_id').change(function () {
				var $this = $(this);
				var $table1 = $('.table1');
				if ($this.val() == 0) {
					$table1.find('tr:nth-child(3)').hide();
					$table1.find('tr:nth-child(4)').hide();
				} else {
					$table1.find('tr').show();
				}
			});

			var controllers = <?= json_encode($controller_list, JSON_UNESCAPED_UNICODE) ?>;
			$("#controller").change(function () {
				var controller_name = $("#controller").val();
				var controller = controllers.find((controller) => controller.name==controller_name);
				if (controller) {
					var methods = controller.methods;
				} else {
					methods = [];
				}
				var html = methods.map((m) => '<option value="'+ m.name +'">'+ m.name +'</option>').join('');
				$('#action').html(html);
			}).change();

		});
	</script>

<? include __DIR__.'/../_/foot.php'; ?>