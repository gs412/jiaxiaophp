<? include __DIR__.'/../_/head.php'; ?>

	<? foreach($this->page_styles as $page_style): ?>
		<link rel="stylesheet" href="<?= $page_style ?>">
	<? endforeach ?>
	<? foreach($this->page_scripts as $page_script): ?>
		<script src="<?= $page_script ?>"></script>
	<? endforeach ?>

	<div class="all_container clearfix">
		<div class="header">
			<h3><?= $args['form_options']['index_title'] ?></h3>
		</div>
		<div class="content">
			<table class="table2">
				<tr>
					<? foreach($columns as $column): ?>
						<th><?= $form[$column]['label'] ?></th>
					<? endforeach ?>
					<th>操作</th>
				</tr>
				<? foreach($objects as $object): ?>
					<tr data-id="<?= $object->id ?>">
						<? foreach($columns as $i => $column): ?>
							<td><?= $object->$column ?></td>
						<? endforeach ?>
						<td>
							<a href="/<?= explode('#', $this->path_info)[0] ?>/edit/<?= $object->id ?>">编辑</a>
							<a href="/<?= explode('#', $this->path_info)[0] ?>/delete/<?= $object->id ?>" data-method="post" data-confirm="确定删除？">删除</a>
						</td>
					</tr>
				<? endforeach; ?>
			</table>
			<div class="pagestr"><?= $pagestr ?></div>
			<a href="/<?= explode('#', $this->path_info)[0] ?>/add" class="btn btn-small btn-success" style="margin-top:20px;">+ 添加</a>
		</div>
	</div>

<? include __DIR__.'/../_/foot.php'; ?>