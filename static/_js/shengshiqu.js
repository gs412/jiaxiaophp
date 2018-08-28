$(document).ready(function () {
	var $select_sheng = $('select#sheng_id');
	var $select_shi = $('select#shi_id');
	var $select_qu = $('select#qu_id');

	$select_sheng.change(function () {
		var $this = $(this);
		fill_shi($this.val(), 0, function (shis) {
			fill_qu(shis[0].id, 0);
		});
	});
	$select_shi.change(function () {
		var $this = $(this);
		fill_qu($this.val(), 0);
	});

	var $init_sheng_id = $select_sheng.data('init_id');
	var $init_shi_id = $select_shi.data('init_id');
	var $init_qu_id = $select_qu.data('init_id');

	fill_sheng($init_sheng_id);
	fill_shi($init_sheng_id, $init_shi_id, null);
	fill_qu($init_shi_id, $init_qu_id);

	function fill_sheng(init_sheng_id) {
		$.ajax({
			url: '/api/get_shengs.php',
			type: 'POST',
			data: {},
			dataType: 'json',
			success: function (shengs) {
				var html = '';
				$.each(shengs, function (i, sheng) {
					html += '<option value="'+sheng.id+'"'+(sheng.id==init_sheng_id ? 'selected' : '')+'>'+sheng.name+'</option>';
				});
				$select_sheng.html(html);
			}
		});
	}

	function fill_shi(sheng_id, init_shi_id, callback) {
		$.ajax({
			url: '/api/get_shis.php',
			type: 'POST',
			data: {sheng_id:sheng_id},
			dataType: 'json',
			success: function (shis) {
				var html = '';
				$.each(shis, function (i, shi) {
					html += '<option value="'+shi.id+'"'+(shi.id==init_shi_id ? 'selected' : '')+'>'+shi.name+'</option>';
				});
				$select_shi.html(html);
				if (callback) {
					callback(shis);
				}
			}
		});
	}

	function fill_qu(shi_id, init_qu_id) {
		$.ajax({
			url: '/api/get_qus.php',
			type: 'POST',
			data: {shi_id:shi_id},
			dataType: 'json',
			success: function (qus) {
				var html = '';
				$.each(qus, function (i, qu) {
					html += '<option value="'+qu.id+'"'+(qu.id==init_qu_id ? 'selected' : '')+'>'+qu.name+'</option>';
				});
				$select_qu.html(html);
			}
		});
	}
});