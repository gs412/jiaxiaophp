$(document).ready(function () {
	$('input[required][type!="file"]').after('<span style="color:red; margin-left:3px; display:inline-block; vertical-align:bottom; margin-bottom:-2px;">*</span>');
	$('input[required][type="file"]').after('<span class="red_star2">*</span>');
	$('textarea[required]').after('<span style="color:red; margin-left:3px; display:inline-block; vertical-align:top; margin-bottom:-2px;">*</span>');
});