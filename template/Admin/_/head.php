<?php

if (in_array($this->path_info, ['Admin/Index#login', 'Admin/Index#left'])) {
	$height_100 = 'height:100%;';
}

?><!DOCTYPE html>
<html style="<?= $height_100 ?>">
<head>
	<title><?= $this->page_title ?></title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="/static/bootstrap/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="/static/admin/css/main.css"/>
	<script src="/static/_js/jquery.min.js"></script>
	<!--<script src="/static/_js/jquery.animate-colors-min.js"></script>-->
	<script src="/static/bootstrap/js/bootstrap.min.js"></script>
	<script src="/static/_js/main.js"></script>
	<script src="/static/admin/js/main.js"></script>
	<?
	$msg = get_session('msg');
	del_session('msg');
	?>
	<? if ($msg): ?>
		<script>alert("<?= $msg ?>");</script>
	<? endif; ?>
</head>
<body style="margin:0; padding:0; <?= $height_100 ?>">