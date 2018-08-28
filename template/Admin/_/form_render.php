<? include __DIR__.'/../_/head.php'; ?>

	<link rel="stylesheet" href="/static/admin/css/form_render.css">
	<script src="/static/admin/js/form_render.js"></script>
	<script src="/static/kindeditor/kindeditor-all-min.js"></script>
	<script src="/static/layui/layer/layer.js"></script>
	<script>
		window.db_table_name = '<?= $args['form_options']['db_table'] ?>';
		var edit_match = window.location.toString().match(/(\w+)\/(\d+)#*$/);
		if (edit_match) {
			var $action = 'edit';
			var $id = edit_match[2];
		} else {
			$action = 'add';
			$id = 0;
		}

		$(document).ready(function () {
			$('.kindeditor').each(function () {
				var $kindeditor = $(this);
				var owner_type = window.db_table_name + '_' + $kindeditor.attr('id');
				var args = {
					width : '700px',
					height: '400px',
					uploadJson : '/Admin/Index/editor_upload_file?owner_type=' + owner_type + '&owner_id=' + $id
				};
				if (typeof(window.KindEditor_args) != "undefined") {
					$.each(args, function (k, v) {
						if (!window.KindEditor_args.hasOwnProperty(k)) {
							window.KindEditor_args[k] = v;
						}
					});
					args = window.KindEditor_args;
				}
				KindEditor.ready(function(K) {
					var editor = K.create('#'+$kindeditor.attr('id'), args);

					if ($kindeditor.hasAttr("required")) {
						$kindeditor.parent().find('.ke-container').css({'display':'inline-block'});
						if ($kindeditor.val() == '') {
							$kindeditor.val(' ');  //防止提交出现javascript错误
						}
						$('#form_main').submit(function () {
							if (editor.html() == '') {
								alert($kindeditor.parent().prev().html().replace('：','') + '不能为空');
								$kindeditor.val(' ');  //防止下次点击提交时出现javascript错误
								editor.focus();
								return false;
							}
						});
					}
					$('span[data-name="multiimage"]').hide();   //这一行代码即可隐藏批量上传功能，有些山寨
				});
			});
		});
	</script>

	<? foreach($this->page_styles as $page_style): ?>
		<link rel="stylesheet" href="<?= $page_style ?>">
	<? endforeach ?>
	<? foreach($this->page_scripts as $page_script): ?>
		<script src="<?= $page_script ?>"></script>
	<? endforeach ?>

	<div class="all_container clearfix">
		<div class="header">
			<h3><?= $form_options['form_title'] ?></h3>
		</div>
		<div class="content">
			<form method="post" id="form_main">
				<table class="table1">
					<input type="hidden" name="referer" value="<?= REFERER ?>">
					<? foreach($form_fields as $name => $field): ?>
						<? if($field['type'] == 'hidden'): ?>
							<input type="hidden" name="<?= $name ?>" value="<?= $field['value'] ?>">
						<? else: ?>
							<tr>
								<td><?= $field['label'] ?>：</td>
								<td>
									<? if($field['type'] == 'select'): ?>
										<select name="<?= $name ?>" <? add_attributes($field) ?>>
											<? foreach($field['options'] as $key=>$val): ?>
												<? if(is_array($val)): ?>
													<option value="<?= $val[0] ?>"><?= $val[1] ?></option>
												<? else: ?>
													<option value="<?= $key ?>"><?= $val ?></option>
												<? endif ?>
											<? endforeach ?>
										</select><? add_description($field); ?>
									<? elseif($field['type'] == 'radio'): ?>
										<? foreach($field['options'] as $key=>$val): ?>
											<?
											if(is_array($val)) {
												$value1 = $val[0];
												$text = $val[1];
											} else {
												$value1 = $key;
												$text = $val;
											}
											if (isset($field['value']) and $field['value']==$value1) {
												$checked = 'checked="checked"';
											} else {
												$checked = '';
											}
											?>
											<label class="for_radio"><input type="radio" name="<?= $name ?>" value="<?= $value1 ?>" <?= $checked ?>><?= $text ?></label>
										<? endforeach ?><? add_description($field); ?>
									<? elseif($field['type'] == 'checkbox'): ?>
										<? foreach($field['options'] as $key=>$val): ?>
											<?
											if(is_array($val)) {
												$value1 = $val[0];
												$text = $val[1];
											} else {
												$value1 = $key;
												$text = $val;
											}
											if (isset($field['value']) and $field['value'] and in_array($value1, explode(',',$field['value']))) {
												$checked = 'checked="checked"';
											} else {
												$checked = '';
											}
											?>
											<label class="for_checkbox"><input type="checkbox" name="<?= $name ?>[]" value="<?= $value1 ?>" <?= $checked ?>><?= $text ?></label>
										<? endforeach ?><? add_description($field); ?>
									<? elseif($field['type'] == 'textarea'): ?>
										<textarea name="<?= $name ?>" <? add_attributes($field) ?>><? if($field['value']): ?><?= $field['value'] ?><? endif ?></textarea><? add_description($field); ?>
									<? elseif($field['type'] == 'editor'): ?>
										<textarea name="<?= $name ?>" id="<?= $name ?>" class="kindeditor" <? add_attributes($field) ?>><? if($field['value']): ?><?= $field['value'] ?><? endif ?></textarea><? add_description($field); ?>
									<? elseif(in_array($field['type'], ['images','files','image','file'])): ?>
										<div class="file_list"></div>
										<input name="<?= $name ?>" type="file" data-type="<?= $field['type'] ?>" data-owner_type="<?= $args['form_options']['db_table'].'_'.$name ?>" <? if(isset($field['max'])): ?>data-max=<?= $field['max'] ?><? endif ?> <? if($field['required']): ?>data-required="yes"<? endif ?> style="font-size:12px;height:auto;line-height:normal;"><? add_description($field); ?>
									<? else: ?>
										<input name="<?= $name ?>" <? add_attributes($field) ?>><? add_description($field); ?>
									<? endif; ?>
									<? if(isset($lack_db_fields[$name])): ?>
										<div class="db_field">
											<span class="name"><?= $name ?></span>
											<select id="field_<?= $name ?>" class="field_type" data-value="<?= $field['db_type'] ?>">
												<option value="0">不创建</option>
												<? foreach($db_fields_options as $option): ?>
													<option value="<?= $option ?>"><?= $option ?></option>
												<? endforeach ?>
											</select>
										</div>
									<? endif ?>
								</td>
							</tr>
						<? endif; ?>
					<? endforeach; ?>
					<tr>
						<td></td>
						<td>
							<input type="submit" value="提 交">
							<input type="button" value="返 回" onclick="history.back();">
							<? if($lack_db_fields): ?>
								<div class="db_field">
									<input type="button" value="同步表结构">
								</div>
							<? endif ?>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>

	<? if($more_db_fields): ?>
		<div class="db_table">
			<div class="db_right">
				表 <u><?= $args['form_options']['db_table'] ?></u> 多余的表结构
				<ul>
					<? foreach($more_db_fields as $name => $type): ?>
						<li><?= $name ?>(<?= $type ?>) <a href="javascript:void(0);" data-field="<?= $name ?>">x</a></li>
					<? endforeach ?>
				</ul>
			</div>
		</div>
	<? endif ?>

<? include __DIR__.'/../_/foot.php'; ?>



<?

function add_attributes($field)
{
	if ($field['type'] == 'select') {
		echo ' data-value="'.$field['value'].'" ';
		if ($field['style']) {
			echo ' style="'.$field['style'].'" ';
		}
	} elseif ($field['type'] == 'textarea') {
		if ($field['required']) {
			echo ' required ';
		}
		if ($field['style']) {
			echo ' style="'.$field['style'].'" ';
		}
	} elseif ($field['type'] == 'editor') {
		if ($field['required']) {
			echo ' required ';
		}
	} else {
		foreach($field as $k=>$v) {
			if ($v and !in_array($k, ['addition', 'description', 'db_type'])) {
				echo ' '.$k.'="'.$v.'" ';
			}
		}
	}
	echo ' '.$field['addition'].' ';
}

function add_description($field)
{
	if ($field['description']) {
		if (isset($field['description']['style'])) {
			if (has_str($field['description']['style'], 'block')) {
				$style_add = 'line-height:normal;'.$field['description']['style'];
			} else {
				$style_add = 'margin-left:15px;'.$field['description']['style'];
			}
		} else {
			$style_add = 'margin-left:15px;';
		}
		echo '<span class="field_description" style="'.$style_add.'">';
		echo $field['description']['text'];
		echo "</span>";
	}
}

?>
