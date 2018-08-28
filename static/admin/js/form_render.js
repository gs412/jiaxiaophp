$(document).ready(function () {
	$('select.field_type').change(function () {
		var $this = $(this);
		if ($this.val() == 'VARCHAR(X)') {
			$this.after('<input class="addition" type="text">');
			$this.next('input.addition').focus();
		} else {
			$this.next('input.addition').remove();
		}
	});

	$('.db_field input[value^="同步表结构"]').click(function () {
		var $fields = {};
		$('.db_field select').each(function () {
			var $this = $(this);
			var $key = $this.attr('id').replace(/^field_/gi, '');
			if ($this.val() == 'VARCHAR(X)') {
				$fields[$key] = 'VARCHAR(' + $this.next('input.addition').val() + ')';
			} else {
				$fields[$key] = $this.val();
			}
		});
		var $data = {'table_name':window.db_table_name, 'fields':$fields};
		$.post('/Admin/Index/syncdb', $data, function ($result) {
			if ($result == 'ok') {
				window.location.reload();
			}
		});
	});

	//删除多余的表结构
	$('.db_table ul li a').click(function () {
		var $a = $(this);
		var $field = $a.data('field');
		var $data = {'table_name':window.db_table_name, 'field':$field};
		$.post('/Admin/Index/dropfield', $data, function ($result) {
			if ($result == 'ok') {
				window.location.reload();
			}
		});
	});

	//上传附件
	var $input_type_file = $('input[type="file"]');
	$input_type_file.change(function(){
		var $this = $(this);
		var $type = $this.data('type');
		var $owner_type = $this.data('owner_type');
		if (['images', 'image'].indexOf($type) > -1) {
			if (!$this.val().match(/\.(jpg|jpeg|gif|png|bmp)$/gi)) {
				alert("只允许jpg、jpeg、gif、png、bmp后缀的图片");
				$this.val('');
				return false;
			}
		}
		var data = new FormData();
		data.append('owner_type', $owner_type);
		data.append('owner_id', $id);
		data.append('type', $type);
		data.append('file2', $this[0].files[0]);
		$.ajax({
			url: '/Admin/Index/upload_file',
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			type: 'POST',
			success: function(data){
				//alert('上传成功');
				$this.val("");
				$this.removeAttr('required');
				$this.next('span.red_star2').hide();
				apply_file_list($this);
				//$this.prev('.file_list').find('.file:last-child').css({opacity:0}).animate({opacity:1});
			}
		});
	});

	//显示附件列表
	$input_type_file.each(function(){
		apply_file_list($(this));
	});
	function apply_file_list($this){
		var $type = $this.data('type');
		var $owner_type = $this.data('owner_type');
		$.ajax({
			url: '/Admin/Index/get_file_list',
			type: 'POST',
			dataType: 'json',
			data: {form_action:$action, owner_type:$owner_type, owner_id:$id},
			success: function (files) {
				var html = '';
				for(var i=0;i<files.length;i++) {
					var file = files[i];
					var url = file.url;
					var name = file.name;
					var id = file.id;
					html += "<div class='img_div'>";
					if (['images', 'image'].indexOf($type) > -1) {
						html += "<img class='img' data-id='"+id+"' src='"+url+"'>";
					} else {
						html += "<div class='file_name' data-id='"+id+"'>"+name+"</div>";
					}
					html += "<div class='del_div'><i class='icon-remove'></i></div>" +
					"</div>";
				}
				$this.prev('.file_list').html(html);
				check_if_show_or_required($this);
				$this.prev('.file_list').find('.img_div .del_div').click(function(){
					var $del_div = $(this);
					var $img_div = $del_div.parent();
					var $next_div = $img_div.next();
					layer.close($img_div.data('layer_index'));
					$img_div.remove();
					img_div_hover($next_div);
					check_if_show_or_required($this);
					var file_id = $img_div.find('img.img, div.file_name').data('id');
					$.ajax({
						url: '/Admin/Index/delete_file',
						type: 'POST',
						data: {file_id: file_id}
					});
				});
				$this.prev('.file_list').find('.img_div').each(function () {
					var $img_div = $(this);
					$img_div.unbind();
					$img_div.hover(function(){
						img_div_hover($img_div);
					}, function(){
						if ($img_div.find('img.img').length) {
							layer.close($img_div.data('layer_index'));
						}
						$img_div.find('.del_div').hide();
					});
				});
			}
		});
	}
	function img_div_hover($img_div) {
		if ($img_div.find('img.img').length) {
			var layer_index = layer.tips(
				"<img src='"+$img_div.find('img.img').attr('src')+"' style='margin:4px 0;'>",
				$img_div,
				{tips:[1,'#333'], time:2000000}
			);
			$img_div.data('layer_index', layer_index);
		}
		//if (['images', 'files'].indexOf($img_div.parent().next('input').data('type')) > -1) {
			$img_div.find('.del_div').show();
		//}
	}
	function check_if_show_or_required($input) {
		if (['images', 'files'].indexOf($input.data('type')) > -1) {
			var $max = $input.data('max');
			if ($max && $input.prev('.file_list').find('div.img_div').length >= $max) {
				$input.hide();
			} else {
				$input.show();
			}
		}
		if ($input.data('required') == 'yes' && $input.prev('.file_list').find('div.img_div').length == 0) {
			$input.attr('required', 'required');
			$input.css({'box-shadow':'unset'});
			if ($input.next('span.red_star2').length == 0) {
				$input.after('<span class="red_star2">*</span>');
			} else {
				$input.next('span.red_star2').show();
			}
		}
	}
});