jQuery.cookie = function(name, value, options) {
	if (typeof value != 'undefined') { // name and value given, set cookie
		options = options || {};
		if (value === null) {
			value = '';
			options.expires = -1;
		}
		var expires = '';
		if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
			var date;
			if (typeof options.expires == 'number') {
				date = new Date();
				date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
			} else {
				date = options.expires;
			}
			expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
		}
		var path = options.path ? '; path=' + options.path : '';
		var domain = options.domain ? '; domain=' + options.domain : '';
		var secure = options.secure ? '; secure' : '';
		document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
	} else { // only name given, get cookie
		var cookieValue = null;
		if (document.cookie && document.cookie != '') {
			var cookies = document.cookie.split(';');
			for (var i = 0; i < cookies.length; i++) {
				var cookie = jQuery.trim(cookies[i]);
				// Does this cookie string begin with the name we want?
				if (cookie.substring(0, name.length + 1) == (name + '=')) {
					cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
					break;
				}
			}
		}
		if(name=="csrftoken" && cookieValue==null){
			return $('meta[name=csrftoken]').attr("content");
		}
		return cookieValue;
	}
};
// 调用方式： $.cookie('csrftoken')

jQuery(function ($) {
    $.extend({
        form: function (url, data, method) {
            if (method == null) method = 'POST';
            if (data == null) data = {};

            var form = $('<form>').attr({
                method: method,
                action: url
            }).css({
                display: 'none'
            });

            var addData = function (name, data) {
                if ($.isArray(data)) {
                    for (var i = 0; i < data.length; i++) {
                        var value = data[i];
                        addData(name + '[]', value);
                    }
                } else if (typeof data === 'object') {
                    for (var key in data) {
                        if (data.hasOwnProperty(key)) {
                            addData(name + '[' + key + ']', data[key]);
                        }
                    }
                } else if (data != null) {
                    form.append($('<input>').attr({
                        type: 'hidden',
                        name: String(name),
                        value: String(data)
                    }));
                }
            };

            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    addData(key, data[key]);
                }
            }

            return form.appendTo('body');
        }
    });
});
// 调用方式： $.form('xxxurl', $json, 'POST').submit();

jQuery.urlParam = function (name) {
	var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	if (results == null) {
		return null;
	} else {
		return results[1] || 0;
	}
};
//调用方式： $.urlParam('keyword')

$.fn.hasAttr = function(name) {
	return this.attr(name) !== undefined;
};
//调用方式： if($('.edit').hasAttr('id'))

//select初始化：option自动加seleted
jQuery.init_select = function ($select) {
	var $value = $select.data('value');
	if ($value !== '') {
		$select.find('option[value="'+$value+'"]').attr('selected', 'selected');
	}
};
//checkbox初始化：自动选中
jQuery.init_checkbox = function ($checkbox_wrapper) {
	var $value = $checkbox_wrapper.data('value');
	if ($value) {
		$value = $value.split(',');
		$checkbox_wrapper.find('input[type="checkbox"]').each(function () {
			var $checkbox = $(this);
			if ($.inArray($checkbox.val(), $value) != -1) {
				$checkbox.prop("checked", true);
			}
		});
	}
};

$(document).ready(link_post_with_confirm);
$(document).ready(alert_slideUp);

function link_post_with_confirm (selector) {
	// 超链接被点击发送post请求
	if (typeof(obj) === 'undefined') {
		selector = $('a')
	}
	selector.click(function () {
		var $this = $(this);
		var $confirm_str = $this.data('confirm');
		if ($confirm_str) {
			if (!confirm($confirm_str)) {
				return false;
			}
		}
		var $method = $this.data('method');
		if ($method && $method.toLowerCase() == 'post') {
			var $json = $this.data('json');
			if ($json) {
				if (typeof $json == 'object') {
					$json.csrfmiddlewaretoken = $.cookie('csrftoken');
				} else {
					alert("非json数据:\n\n"+$json);
					return false;
				}
			} else {
				$json = {csrfmiddlewaretoken:$.cookie('csrftoken')};
			}
			$.form($this.attr('href'), $json, 'POST').submit();
			return false;
		}
	});

	//option自动加seleted
	$('select').each(function () {
		var $select = $(this);
		$.init_select($select);
	});
	//checkbox自动选中
	$('.checkbox-wrapper').each(function () {
		var $checkbox_wrapper = $(this);
		$.init_checkbox($checkbox_wrapper);
	});
}

function alert_slideUp () {
	setTimeout(function () {
		$('.alert-error, .alert-success, .alert-info').slideUp(800);
	}, 1800);
}